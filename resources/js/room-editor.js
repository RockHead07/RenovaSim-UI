import * as THREE from 'three';
import { createScene, buildRoom } from './editor/scene.js';
import { createFurniture, handleClick, startDrag, doDrag, endDrag, getDragging, getSelected, setSelected, deleteSelected, serializeObjects, clearObjects, getObjects, setRoomDimensions, constrainObjectPosition } from './editor/objects.js';
import { initExplore, setupExploreEvents, lockPointer, isLocked, updateExplore, enterExploreMode, exitExploreMode, applyGravityToObjects } from './editor/explore.js';
import { TransformControls } from 'three/addons/controls/TransformControls.js';
import * as API from './editor/api.js';

let engine, roomGroup, catalog = {}, currentRoom = null, mode = 'build';
let savedCamPos = null, savedCamTarget = null;
let savedWallCamPos = null, savedWallCamTarget = null;
let heldObject = null, heldDistance = 2.0;
let transformControl = null;
const timer = new THREE.Timer();

// Wall Drawing State
let drawingWall = false;
let wallStartPoint = null;
let wallPreview = null;
const wallHeight = 3.2;
const wallThickness = 0.15;

// ── Init ──
window.RenovaEditor = { init, uploadAndGenerate, switchMode, addFurniture, deleteObj, applyTemplate, paintWall, saveProject, spawnFurniture, startWallDraw, cancelWallDraw, getCatalog: () => catalog };

async function init() {
    const container = document.getElementById('editor-canvas');
    if (!container) return;
    engine = createScene(container);

    // Check Python server
    const online = await API.checkStatus();
    updateStatus(online);

    // Load catalog
    try { catalog = await API.getCatalog(); } catch(e) { catalog = {}; }

    // Load templates into UI
    try {
        const tpls = await API.getTemplates();
        renderTemplates(tpls);
    } catch(e) {}

    // Load paint colors
    try {
        const colors = await API.getPaintColors();
        renderPaintColors(colors);
    } catch(e) {}

    // Render catalog to UI
    renderCatalog(catalog);

    // TransformControls for Build mode
    transformControl = new TransformControls(engine.camera, engine.renderer.domElement);
    transformControl.addEventListener('dragging-changed', function (event) {
        engine.controls.enabled = !event.value;
    });
    // Set to translate mode by default
    transformControl.setMode('translate');
    engine.scene.add(transformControl.getHelper());

    let isConstraining = false;
    transformControl.addEventListener('change', () => {
        if (isConstraining) return;
        const obj = transformControl.object;
        if (obj && transformControl.dragging) {
            isConstraining = true;
            constrainObjectPosition(obj, currentRoom ? currentRoom.width : 8, currentRoom ? currentRoom.length : 10, getObjects());
            onObjSelected(obj);
            isConstraining = false;
        }
    });

    // Setup explore mode events
    setupExploreEvents(engine.renderer.domElement);

    // Mouse events for Build mode
    const cv = engine.renderer.domElement;
    cv.addEventListener('mousedown', onMouseDown);
    cv.addEventListener('mousemove', onMouseMove);
    cv.addEventListener('mouseup', onMouseUp);

    // Keyboard shortcuts
    document.addEventListener('keydown', onKeyDown);

    // Animation loop
    animate();

    // Check if loading an existing project
    const projectId = document.body.dataset.projectId;
    if (projectId && online) {
        try {
            const room = await API.getRoom(projectId);
            if (room && room.id) {
                currentRoom = room;
                loadRoomIntoScene(currentRoom);
                toast(`Loaded: ${room.name || 'Room'}`, 'success');
                return; // Skip upload overlay
            }
        } catch(e) {
            console.warn('Could not load project:', projectId, e);
        }
    }

    // Show upload overlay for new projects
    showUpload();
}

function animate() {
    requestAnimationFrame(animate);
    timer.update();
    const dt = timer.getDelta();
    if (mode === 'build') {
        if (!transformControl || !transformControl.dragging) {
            engine.controls.enabled = true;
        }
        engine.controls.update();
    } else {
        engine.controls.enabled = false;
        updateExplore(dt);
        
        // Apply gravity and stacking to all placement objects except the held one
        applyGravityToObjects(getObjects(), heldObject, dt);
        
        if (heldObject) {
            const ft = heldObject.userData.furnitureType || '';
            const isWallMounted = ['mirror', 'painting', 'clock', 'curtain'].some(t => ft.includes(t));
            
            if (isWallMounted) {
                // Snapping wall-mounted items to room walls and partition walls
                const wallMeshes = [];
                engine.scene.traverse(child => {
                    if (child.isMesh && child.userData && child.userData.type === 'wall') {
                        wallMeshes.push(child);
                    }
                });
                
                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(new THREE.Vector2(0, 0), engine.camera);
                const intersects = raycaster.intersectObjects(wallMeshes, true);
                
                if (intersects.length > 0 && intersects[0].distance < 6.0) {
                    const hit = intersects[0];
                    // Get world normal of hit face
                    const normal = hit.face.normal.clone();
                    normal.transformDirection(hit.object.matrixWorld);
                    normal.normalize();
                    
                    const s = heldObject.userData.scale || [1, 1, 1];
                    const offset = s[2] / 2; // Offset by half thickness
                    heldObject.position.copy(hit.point).add(normal.clone().multiplyScalar(offset));
                    
                    // Rotate to face outward
                    const angle = Math.atan2(normal.x, normal.z);
                    heldObject.rotation.y = angle;
                } else {
                    // Fallback in front of player
                    const dir = new THREE.Vector3();
                    engine.camera.getWorldDirection(dir);
                    heldObject.position.copy(engine.camera.position).add(dir.multiplyScalar(heldDistance));
                }
            } else {
                // Snapping floor items to ground or other furniture surfaces
                const targetMeshes = [];
                const floorMesh = engine.scene.getObjectByName('floor') || engine.scene.children.find(c => c.name === 'floor');
                if (floorMesh) targetMeshes.push(floorMesh);
                
                getObjects().forEach(obj => {
                    if (obj !== heldObject) {
                        const oft = obj.userData.furnitureType || '';
                        // Do not stack on rugs, carpets, or wall-mounted items
                        if (!oft.includes('rug') && !oft.includes('carpet') && !['mirror', 'painting', 'clock', 'curtain'].some(t => oft.includes(t))) {
                            obj.traverse(child => {
                                if (child.isMesh && child.material && child.material.visible !== false) {
                                    targetMeshes.push(child);
                                }
                            });
                        }
                    }
                });
                
                const raycaster = new THREE.Raycaster();
                raycaster.setFromCamera(new THREE.Vector2(0, 0), engine.camera);
                const intersects = raycaster.intersectObjects(targetMeshes, true);
                
                if (intersects.length > 0 && intersects[0].distance < 6.0) {
                    const hit = intersects[0];
                    heldObject.position.copy(hit.point);
                    if (heldObject.position.y < 0) heldObject.position.y = 0;
                } else {
                    // Fallback to floor baseline
                    const dir = new THREE.Vector3();
                    engine.camera.getWorldDirection(dir);
                    const dest = new THREE.Vector3().copy(engine.camera.position).add(dir.multiplyScalar(heldDistance));
                    heldObject.position.x = dest.x;
                    heldObject.position.z = dest.z;
                    heldObject.position.y = 0;
                }
                
                // Constrain position to prevent wall penetration
                constrainObjectPosition(heldObject, currentRoom ? currentRoom.width : 8, currentRoom ? currentRoom.length : 10, getObjects());
            }
        }
    }
    engine.renderer.render(engine.scene, engine.camera);
}

// ── Upload & Generate ──
function showUpload() {
    document.getElementById('upload-overlay').style.display = 'flex';
}

async function uploadAndGenerate() {
    const input = document.getElementById('file-input');
    if (!input.files.length) { toast('Please select room photos', 'warning'); return; }

    document.getElementById('upload-overlay').style.display = 'none';
    document.getElementById('generating-overlay').style.display = 'flex';

    try {
        const result = await API.uploadImages(Array.from(input.files));
        if (result.status === 'success') {
            currentRoom = result.room;
            loadRoomIntoScene(currentRoom);
            toast(`Generated! ${currentRoom.objects.length} objects detected`, 'success');
            // Show template recommendations
            if (currentRoom.recommended_templates && currentRoom.recommended_templates.length) {
                const recEl = document.getElementById('recommendations');
                if (recEl) {
                    recEl.innerHTML = `<div class="property-label">Recommended for: ${currentRoom.recommended_type}</div>`;
                    currentRoom.recommended_templates.forEach(t => {
                        recEl.innerHTML += `<div class="template-card" onclick="RenovaEditor.applyTemplate('${t.id}')">
                            <div class="template-icon">${t.thumbnail}</div>
                            <div class="template-name">${t.name}</div>
                            <div class="template-desc">${t.description}</div>
                        </div>`;
                    });
                }
            }
        } else {
            toast('Generation failed', 'error');
            showUpload();
        }
    } catch (e) {
        console.error(e);
        toast('Server error - is Python server running?', 'error');
        // Fallback: create demo room
        createDemoRoom();
    }
    document.getElementById('generating-overlay').style.display = 'none';
}

function createDemoRoom() {
    currentRoom = {
        id: 'demo-' + Date.now(), name: 'Demo Room', width: 8, length: 10, height: 3.2,
        wall_color: '#f5f0eb', floor_color: '#c4a882', objects: [], detected_assets: [],
        recommended_type: 'living', status: 'demo'
    };
    loadRoomIntoScene(currentRoom);
    toast('Demo room created (Python server offline)', 'warning');
}

function loadRoomIntoScene(room) {
    // Clear existing
    const old = engine.scene.getObjectByName('room');
    if (old) engine.scene.remove(old);
    clearObjects(engine.scene);

    // Build room geometry
    roomGroup = buildRoom(engine.scene, room.width, room.length, room.height, room.wall_color, room.floor_color);
    setRoomDimensions(room.width, room.length);
    
    // Hide ceiling in build mode
    const ceiling = engine.scene.getObjectByName('ceiling');
    if (ceiling) ceiling.visible = false;

    // Place objects
    if (room.objects) {
        room.objects.forEach(obj => {
            const mesh = createFurniture(obj.type, catalog, obj.position, obj.rotation, obj.id, obj.scale);
            if (mesh) engine.scene.add(mesh);
        });
    }

    // Update asset list UI
    updateAssetList();

    // Reset camera
    engine.camera.position.set(room.width * 0.8, room.height * 2, room.length * 0.8);
    engine.controls.target.set(0, 0, 0);
    engine.controls.update();

    // Update room info
    const infoEl = document.getElementById('room-info');
    if (infoEl) infoEl.textContent = `${room.width}m × ${room.length}m × ${room.height}m`;
}

// ── Mode Switching ──
function switchMode(newMode) {
    if (newMode === mode) return;
    const buildBtn = document.getElementById('mode-build');
    const exploreBtn = document.getElementById('mode-explore');
    const panel = document.getElementById('side-panel');
    const hud = document.getElementById('explore-hud');
    const crosshair = document.getElementById('crosshair');
    const hint = document.getElementById('transform-hint');

    if (newMode === 'explore') {
        // Cancel active wall drawing before entering explore mode
        cancelWallDraw();
        
        if (transformControl) transformControl.detach();
        const ceiling = engine.scene.getObjectByName('ceiling');
        if (ceiling) ceiling.visible = true;
        savedCamPos = engine.camera.position.clone();
        savedCamTarget = engine.controls.target.clone();
        setSelected(null);
        enterExploreMode(engine.camera);
        initExplore(engine.camera);
        if (buildBtn) buildBtn.classList.remove('active');
        if (exploreBtn) exploreBtn.classList.add('active');
        if (panel) panel.classList.add('collapsed');
        if (hud) hud.style.display = 'flex';
        if (crosshair) crosshair.classList.add('visible');
        if (hint) hint.style.display = 'none';
        lockPointer(engine.renderer.domElement);
    } else {
        // Hide explore catalog overlay if visible
        const expCat = document.getElementById('explore-catalog');
        if (expCat) expCat.style.display = 'none';
        
        const ceiling = engine.scene.getObjectByName('ceiling');
        if (ceiling) ceiling.visible = false;
        exitExploreMode();
        if (savedCamPos) engine.camera.position.copy(savedCamPos);
        if (savedCamTarget) engine.controls.target.copy(savedCamTarget);
        engine.controls.update();
        if (buildBtn) buildBtn.classList.add('active');
        if (exploreBtn) exploreBtn.classList.remove('active');
        if (panel) panel.classList.remove('collapsed');
        if (hud) hud.style.display = 'none';
        if (crosshair) crosshair.classList.remove('visible');
        if (hint) hint.style.display = '';
    }
    mode = newMode;
}

// ── Interactions ──
function onMouseDown(e) {
    if (mode === 'explore' && isLocked()) {
        if (e.button === 0) { // Left click
            if (heldObject) {
                heldObject = null;
            } else {
                const hit = handleClick(e, engine.camera, engine.scene, engine.renderer.domElement, mode, null);
                if (hit) {
                    heldObject = hit;
                    heldDistance = Math.min(Math.max(engine.camera.position.distanceTo(hit.position), 1.5), 4.0);
                }
            }
        }
        return;
    }
    
    // Build mode - Wall Drawing Tool click handling
    if (mode === 'build' && drawingWall) {
        if (e.button === 0) { // Left click
            const floorMesh = engine.scene.getObjectByName('floor') || engine.scene.children.find(c => c.name === 'floor');
            if (!floorMesh) return;
            
            const rect = engine.renderer.domElement.getBoundingClientRect();
            const mouse = new THREE.Vector2(
                ((e.clientX - rect.left) / rect.width) * 2 - 1,
                -((e.clientY - rect.top) / rect.height) * 2 + 1
            );
            
            const raycaster = new THREE.Raycaster();
            raycaster.setFromCamera(mouse, engine.camera);
            const intersects = raycaster.intersectObject(floorMesh, true);
            
            if (intersects.length > 0) {
                const pt = intersects[0].point;
                // Snap to 0.1m grid for precision architectural drawing
                pt.x = Math.round(pt.x * 10) / 10;
                pt.z = Math.round(pt.z * 10) / 10;
                
                if (!wallStartPoint) {
                    wallStartPoint = pt.clone();
                    const txt = document.getElementById('draw-wall-text');
                    if (txt) txt.textContent = "Wall Drawing: Click floor again to set Wall End Point";
                    toast('Start point set. Click to draw the wall.', 'success');
                } else {
                    const p1 = wallStartPoint;
                    const p2 = pt;
                    const dir = new THREE.Vector3().subVectors(p2, p1);
                    const L = dir.length();
                    
                    if (L < 0.2) {
                        toast('Wall is too short! Minimum length is 0.2m.', 'warning');
                        return;
                    }
                    
                    const C = new THREE.Vector3().copy(p1).add(dir.clone().multiplyScalar(0.5));
                    const angle = -Math.atan2(dir.z, dir.x);
                    
                    // Temporarily store dynamic wall details in catalog to instantiate custom partition wall
                    catalog['partition_wall'] = {
                        name: "Partition Wall",
                        category: "build",
                        color: "#f5f0eb",
                        scale: [L, wallHeight, wallThickness]
                    };
                    
                    const mesh = createFurniture('partition_wall', catalog, [C.x, wallHeight / 2, C.z], [0, angle, 0]);
                    if (mesh) {
                        engine.scene.add(mesh);
                        updateAssetList();
                        toast('Partition wall added!', 'success');
                    }
                    
                    // Reset start point to support multi-wall chaining/continuation
                    wallStartPoint = null;
                    const txt = document.getElementById('draw-wall-text');
                    if (txt) txt.textContent = "Wall Drawing: Click floor to set Wall Start Point";
                }
            }
        }
        return;
    }

    if (mode !== 'build') return;
    if (transformControl && transformControl.axis !== null) return; // Prevent selection when clicking transform arrows
    handleClick(e, engine.camera, engine.scene, engine.renderer.domElement, mode, onObjSelected);
}

function onMouseMove(e) {
    if (mode !== 'build') return;
    
    // Live preview for Wall Drawing Tool
    if (drawingWall && wallStartPoint) {
        const floorMesh = engine.scene.getObjectByName('floor') || engine.scene.children.find(c => c.name === 'floor');
        if (!floorMesh) return;
        
        const rect = engine.renderer.domElement.getBoundingClientRect();
        const mouse = new THREE.Vector2(
            ((e.clientX - rect.left) / rect.width) * 2 - 1,
            -((e.clientY - rect.top) / rect.height) * 2 + 1
        );
        
        const raycaster = new THREE.Raycaster();
        raycaster.setFromCamera(mouse, engine.camera);
        const intersects = raycaster.intersectObject(floorMesh, true);
        
        if (intersects.length > 0) {
            const pt = intersects[0].point;
            pt.x = Math.round(pt.x * 10) / 10;
            pt.z = Math.round(pt.z * 10) / 10;
            
            const p1 = wallStartPoint;
            const p2 = pt;
            const dir = new THREE.Vector3().subVectors(p2, p1);
            const L = dir.length();
            const C = new THREE.Vector3().copy(p1).add(dir.clone().multiplyScalar(0.5));
            const angle = -Math.atan2(dir.z, dir.x);
            
            if (!wallPreview) {
                const wallMat = new THREE.MeshStandardMaterial({
                    color: 0x7cb342,
                    transparent: true,
                    opacity: 0.45,
                    roughness: 0.5
                });
                const geo = new THREE.BoxGeometry(1, 1, 1);
                wallPreview = new THREE.Mesh(geo, wallMat);
                engine.scene.add(wallPreview);
            }
            
            wallPreview.scale.set(L, wallHeight, wallThickness);
            wallPreview.position.set(C.x, wallHeight / 2, C.z);
            wallPreview.rotation.set(0, angle, 0);
        }
    }
}

function onMouseUp() {
    if (mode !== 'build') return;
}

function onObjSelected(obj) {
    if (transformControl) {
        if (obj) transformControl.attach(obj);
        else transformControl.detach();
    }
    const propPanel = document.getElementById('props-content');
    if (!propPanel) return;
    if (!obj) { propPanel.innerHTML = '<p style="color:var(--editor-text-muted);font-size:13px;text-align:center;padding:20px;">Click an object to select it</p>'; return; }
    const u = obj.userData;
    propPanel.innerHTML = `
        <div class="property-group">
            <div class="property-label">Object</div>
            <div style="font-size:14px;font-weight:600;color:var(--editor-text);margin-bottom:8px;">${u.name || u.furnitureType}</div>
        </div>
        <div class="property-group">
            <div class="property-label">Position</div>
            <div class="property-row"><label>X</label><input class="property-input" type="number" step="0.1" value="${obj.position.x.toFixed(2)}" onchange="RenovaEditor._updatePos(this,'x')"></div>
            <div class="property-row"><label>Y</label><input class="property-input" type="number" step="0.1" value="${obj.position.y.toFixed(2)}" onchange="RenovaEditor._updatePos(this,'y')"></div>
            <div class="property-row"><label>Z</label><input class="property-input" type="number" step="0.1" value="${obj.position.z.toFixed(2)}" onchange="RenovaEditor._updatePos(this,'z')"></div>
        </div>
        <div class="property-group">
            <div class="property-label">Rotation Y</div>
            <div class="property-row"><label>°</label><input class="property-input" type="number" step="5" value="${THREE.MathUtils.radToDeg(obj.rotation.y).toFixed(0)}" onchange="RenovaEditor._updateRot(this)"></div>
        </div>
        <div class="property-group">
            <div class="property-label">Scale</div>
            <div class="property-row"><input class="property-input" type="range" min="0.5" max="3" step="0.1" value="1" onchange="RenovaEditor._updateScale(this)"></div>
        </div>
        <button class="toolbar-btn" style="width:100%;justify-content:center;margin-top:8px;color:var(--editor-danger);border-color:var(--editor-danger);" onclick="RenovaEditor.deleteObj()">🗑 Delete Object</button>
    `;
}

window.RenovaEditor._updatePos = (input, axis) => {
    const obj = getSelected(); if (!obj) return;
    obj.position[axis] = parseFloat(input.value);
    constrainObjectPosition(obj, currentRoom ? currentRoom.width : 8, currentRoom ? currentRoom.length : 10, getObjects());
    onObjSelected(obj);
};
window.RenovaEditor._updateRot = (input) => {
    const obj = getSelected(); if (!obj) return;
    obj.rotation.y = THREE.MathUtils.degToRad(parseFloat(input.value));
};
window.RenovaEditor._updateScale = (input) => {
    const obj = getSelected(); if (!obj) return;
    const s = parseFloat(input.value);
    obj.scale.set(s, s, s);
};

function onKeyDown(e) {
    if (e.key === 'Delete' || e.key === 'Backspace') deleteObj();
    if (e.key === 'Escape') { 
        if (drawingWall) {
            cancelWallDraw();
        } else if (mode === 'explore') { 
            switchMode('build'); 
            heldObject = null; 
        } else {
            setSelected(null); 
        }
    }
    if (e.key === 'r' || e.key === 'R') {
        if (mode === 'build' && getSelected()) getSelected().rotation.y += Math.PI / 8;
        if (mode === 'explore' && heldObject) heldObject.rotation.y += Math.PI / 8;
    }
    if ((e.key === 'c' || e.key === 'C') && mode === 'explore') {
        if (typeof window.toggleExploreCatalog === 'function') {
            window.toggleExploreCatalog();
        }
    }
}

// ── Furniture Actions ──
function addFurniture(type) {
    if (!catalog[type] && !currentRoom) return;
    const info = catalog[type] || { scale: [1,1,1], color: '#888888', name: type };
    const pos = [Math.random()*2-1, info.scale[1]/2, Math.random()*2-1];
    const mesh = createFurniture(type, catalog, pos, [0,0,0]);
    if (mesh) {
        engine.scene.add(mesh);
        setSelected(mesh);
        onObjSelected(mesh);
        updateAssetList();
        toast(`Added ${info.name}`, 'success');
    }
}

function deleteObj() {
    const sel = getSelected();
    if (!sel) return;
    const name = sel.userData.name || 'Object';
    deleteSelected(engine.scene);
    onObjSelected(null);
    updateAssetList();
    toast(`Deleted ${name}`, 'warning');
}

// ── Templates ──
async function applyTemplate(templateId) {
    if (!currentRoom) return;
    try {
        const result = await API.applyTemplate(currentRoom.id, templateId);
        if (result.status === 'success') {
            currentRoom = result.room;
            loadRoomIntoScene(currentRoom);
            toast('Template applied!', 'success');
        }
    } catch(e) {
        toast('Failed to apply template', 'error');
    }
}

// ── Paint Wall ──
function paintWall(color) {
    if (!roomGroup) return;
    roomGroup.children.forEach(child => {
        if (child.userData && child.userData.type === 'wall') {
            child.material.color.set(color);
        }
    });
    if (currentRoom) currentRoom.wall_color = color;
    toast(`Walls painted!`, 'success');
}

// ── Save ──
async function saveProject() {
    if (!currentRoom) { toast('No room to save', 'warning'); return; }
    currentRoom.objects = serializeObjects();
    try {
        // Capture thumbnail from canvas
        try {
            engine.renderer.render(engine.scene, engine.camera);
            const thumbCanvas = document.createElement('canvas');
            thumbCanvas.width = 400;
            thumbCanvas.height = 250;
            const tctx = thumbCanvas.getContext('2d');
            tctx.drawImage(engine.renderer.domElement, 0, 0, 400, 250);
            const thumbnail = thumbCanvas.toDataURL('image/jpeg', 0.7);
            currentRoom.thumbnail = thumbnail;
            // Also save thumbnail separately
            await API.saveThumbnail(currentRoom.id, thumbnail);
        } catch(e) { console.warn('Thumbnail capture failed:', e); }

        await API.saveRoom(currentRoom.id, currentRoom);
        toast('Project saved!', 'success');
    } catch(e) {
        toast('Save failed - server offline?', 'error');
    }
}

// ── UI Helpers ──
function renderCatalog(cat) {
    const el = document.getElementById('catalog-grid');
    if (!el) return;
    const categories = {};
    Object.entries(cat).forEach(([key, item]) => {
        if (!categories[item.category]) categories[item.category] = [];
        categories[item.category].push({ key, ...item });
    });
    let html = '';
    Object.entries(categories).forEach(([cat, items]) => {
        items.forEach(item => {
            html += `<div class="asset-card" onclick="RenovaEditor.addFurniture('${item.key}')" title="${item.name}">
                <span class="asset-icon">${item.icon || '📦'}</span>
                <span class="asset-name">${item.name}</span>
            </div>`;
        });
    });
    el.innerHTML = html;
}

function renderTemplates(tpls) {
    const el = document.getElementById('templates-list');
    if (!el) return;
    let html = '';
    Object.entries(tpls).forEach(([id, t]) => {
        html += `<div class="template-card" onclick="RenovaEditor.applyTemplate('${id}')">
            <div class="template-icon">${t.thumbnail}</div>
            <div class="template-name">${t.name}</div>
            <div class="template-desc">${t.description}</div>
            <div class="template-items">${t.objects.length} items included</div>
        </div>`;
    });
    el.innerHTML = html;
}

function renderPaintColors(colors) {
    const el = document.getElementById('paint-grid');
    if (!el) return;
    let html = '';
    colors.forEach(c => {
        html += `<div class="color-swatch" style="background:${c.hex}" title="${c.name}" onclick="RenovaEditor.paintWall('${c.hex}')"></div>`;
    });
    el.innerHTML = html;
}

function updateAssetList() {
    const el = document.getElementById('scene-objects');
    if (!el) return;
    const objs = getObjects();
    if (!objs.length) { el.innerHTML = '<p style="color:var(--editor-text-muted);font-size:12px;padding:8px;">No objects in scene</p>'; return; }
    let html = '';
    objs.forEach(o => {
        const u = o.userData;
        html += `<div class="asset-card" style="grid-column:span 2;display:flex;align-items:center;gap:8px;text-align:left;padding:8px 12px;" onclick="RenovaEditor._selectById('${u.id}')">
            <span style="font-size:18px;">${catalog[u.furnitureType]?.icon || '📦'}</span>
            <span class="asset-name" style="font-size:12px;">${u.name}</span>
        </div>`;
    });
    el.innerHTML = html;
}

window.RenovaEditor._selectById = (id) => {
    const obj = getObjects().find(o => o.userData.id === id);
    if (obj) { setSelected(obj); onObjSelected(obj); }
};

function updateStatus(online) {
    const dot = document.getElementById('status-dot');
    const txt = document.getElementById('status-text');
    if (dot) dot.className = online ? 'status-dot' : 'status-dot offline';
    if (txt) txt.textContent = online ? 'Python Server Online' : 'Python Server Offline';
}

function toast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.textContent = msg;
    container.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// ── New Features: Catalog Spawning & Wall Drawing ──
function spawnFurniture(type) {
    if (!catalog[type]) return;
    const info = catalog[type];
    
    // Calculate a position in front of the camera
    const dir = new THREE.Vector3();
    engine.camera.getWorldDirection(dir);
    const pos = new THREE.Vector3().copy(engine.camera.position).add(dir.multiplyScalar(heldDistance));
    
    const mesh = createFurniture(type, catalog, [pos.x, pos.y, pos.z], [0, 0, 0]);
    if (mesh) {
        engine.scene.add(mesh);
        heldObject = mesh;
        updateAssetList();
        
        // Close explore catalog overlay
        const expCat = document.getElementById('explore-catalog');
        if (expCat) expCat.style.display = 'none';
        
        // Re-lock pointer to return to explore movement/controls
        lockPointer(engine.renderer.domElement);
        toast(`Spawned ${info.name}`, 'success');
    }
}

function startWallDraw() {
    drawingWall = true;
    wallStartPoint = null;
    if (wallPreview) {
        engine.scene.remove(wallPreview);
        wallPreview = null;
    }
    
    // Hide transform controls if attached
    if (transformControl) transformControl.detach();
    setSelected(null);
    onObjSelected(null);
    
    // Save current camera state and position it straight down
    savedWallCamPos = engine.camera.position.clone();
    savedWallCamTarget = engine.controls.target.clone();
    
    engine.controls.enableRotate = false;
    
    const size = Math.max(currentRoom ? currentRoom.width : 8, currentRoom ? currentRoom.length : 10);
    const height = size * 1.3;
    
    engine.controls.target.set(0, 0, 0);
    engine.camera.position.set(0, height, 0.001); // 0.001 offset prevents gimbal lock/glitches in OrbitControls
    engine.controls.update();
    
    // Show banner UI
    const banner = document.getElementById('draw-wall-banner');
    if (banner) banner.style.display = 'flex';
    
    const txt = document.getElementById('draw-wall-text');
    if (txt) txt.textContent = "Wall Drawing: Click floor to set Wall Start Point";
    
    toast('Wall drawing tool active. Click floor to start.', 'info');
}

function cancelWallDraw() {
    drawingWall = false;
    wallStartPoint = null;
    if (wallPreview) {
        engine.scene.remove(wallPreview);
        wallPreview = null;
    }
    
    // Restore camera controls and position
    engine.controls.enableRotate = true;
    if (savedWallCamPos) {
        engine.camera.position.copy(savedWallCamPos);
        savedWallCamPos = null;
    }
    if (savedWallCamTarget) {
        engine.controls.target.copy(savedWallCamTarget);
        savedWallCamTarget = null;
    }
    engine.controls.update();
    
    // Hide banner UI
    const banner = document.getElementById('draw-wall-banner');
    if (banner) banner.style.display = 'none';
    
    toast('Wall drawing cancelled.', 'info');
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('editor-canvas')) init();
});
