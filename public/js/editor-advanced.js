/**
 * Advanced 3D Room Editor v5 - Fixed & Enhanced
 * ✨ Features:
 * - Explore Mode: Free camera + WASD + mouse look (right-click drag)
 * - Build Mode: Orbit camera + click-to-place furniture + transform controls
 * - Room templates with preset furniture
 * - Professional dark UI design
 *
 * FIXES APPLIED:
 * 1. THREE.PCFShadowShadowMap → THREE.PCFSoftShadowMap  (typo causing shadow error)
 * 2. CapsuleGeometry → CylinderGeometry+SphereGeometry  (CapsuleGeometry not in r128)
 * 3. setupLoaders() was OUTSIDE the class body — moved inside
 * 4. Infinite recursion: setupUI()→updateUI()→setupUI() — split into renderUI()/updateUI()/_refreshUIStats()
 * 5. updateUI() was called on EVERY keydown — now only called on mode switch
 * 6. selectObject() crashed when material.emissive was undefined — added safe pre-create
 * 7. setupRoom() leaked old wall/floor meshes each call — now removes old meshes first
 * 8. loadCharacterModel() was empty stub — restored with GLTFLoader + graceful fallback
 * 9. Mouse raycasting used window dimensions instead of canvas rect — fixed
 * 10. Render loop used Date.now() float — switched to performance.now() + delta cap
 */

class AdvancedRoom3DEditor {
    constructor() {
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js is not loaded');
        }
        this.canvas = document.getElementById('canvas');
        if (!this.canvas) {
            throw new Error('Canvas element with id="canvas" not found');
        }

        // Scene
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        // Character
        this.character = null;
        this.isMoving = false;
        this.characterSpeed = 5;
        this.mixer = null;
        this.clock = new THREE.Clock();

        // Mode
        this.mode = 'explore'; // 'explore' | 'build'
        this.selectedObject = null;
        this.currentFurnitureType = null;
        this.objects = [];
        this.walls = {};
        this.roomMeshes = []; // track all room geometry for cleanup on resize
        this.floor = null;
        this.ceiling = null;

        // Input
        this.keys = {};
        this.mouseButtons = { left: false, right: false, middle: false };
        this._tcDragging = false;

        // Explore camera
        this.cameraRotation = { x: 0, y: 0 };
        this.cameraSpeed = 8;
        this.cameraSensitivity = 0.003;

        // Build camera (orbit)
        this.buildCameraDistance = 6;
        this.buildCameraHeight = 3;
        this.buildCameraRotation = { x: 0.3, y: 0 };
        this.orbitCenter = new THREE.Vector3(0, 1, 0);

        // Transform
        this.transformControls = null;
        this.transformMode = 'translate';

        // Room
        this.roomWidth = 5;
        this.roomLength = 5;
        this.roomHeight = 3;
        this.roomColors = {
            floor: 0xc8a882,
            walls: 0xeeeade,
            ceiling: 0xffffff
        };

        // Ground
        this.groundHeight = 0;

        // Loaders
        this.gltfLoader = null;

        // Internal UI handler ref for proper removeEventListener
        this._uiClickHandler = null;

        console.log('✅ AdvancedRoom3DEditor v5 initialized');
    }

    init() {
        console.log('🚀 Initializing Advanced 3D Editor v5...');
        this.setupScene();
        this.setupRenderer();
        this.setupCamera();
        this.setupLights();
        this.setupRoom();
        this.setupCharacter();
        this.setupLoaders();       // FIX: now inside class
        this.loadCharacterModel(); // FIX: now functional with fallback
        this.setupTransformControls();
        this.setupEventListeners();
        this.renderUI();           // FIX: no more infinite recursion
        this.startRenderLoop();
        console.log('✅ Editor v5 ready!');
    }

    // ============================================================
    // SCENE
    // ============================================================
    setupScene() {
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x87ceeb);
        this.scene.fog = new THREE.Fog(0x87ceeb, 80, 200);
    }

    setupRenderer() {
        this.renderer = new THREE.WebGLRenderer({
            canvas: this.canvas,
            antialias: true,
        });
        this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap; // FIX #1: was PCFShadowShadowMap (typo)
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1;
        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupCamera() {
        const w = this.canvas.clientWidth;
        const h = this.canvas.clientHeight;
        this.camera = new THREE.PerspectiveCamera(70, w / h, 0.05, 500);
        this.camera.position.set(0, 1.6, 3.5);
        this.camera.lookAt(0, 1, 0);
    }

    setupLights() {
        this.scene.add(new THREE.AmbientLight(0xfff8f0, 0.55));

        const sun = new THREE.DirectionalLight(0xfffaf0, 0.85);
        sun.position.set(8, 14, 8);
        sun.castShadow = true;
        sun.shadow.mapSize.set(2048, 2048);
        sun.shadow.camera.left   = -20;
        sun.shadow.camera.right  =  20;
        sun.shadow.camera.top    =  20;
        sun.shadow.camera.bottom = -20;
        sun.shadow.camera.near   = 0.5;
        sun.shadow.camera.far    = 200;
        sun.shadow.bias = -0.0005;
        this.scene.add(sun);

        const fill = new THREE.PointLight(0xffeedd, 0.4, 25);
        fill.position.set(0, 2.5, 0);
        this.scene.add(fill);
    }

    // ============================================================
    // ROOM — FIX #7: removes old meshes before re-creating
    // ============================================================
    setupRoom() {
        this.roomMeshes.forEach(m => this.scene.remove(m));
        this.roomMeshes = [];
        this.walls = {};

        this._addFloor();
        this._addWalls();
        this._addCeiling();
    }

    _addFloor() {
        const geo = new THREE.PlaneGeometry(this.roomWidth, this.roomLength);
        const mat = new THREE.MeshStandardMaterial({
            color: this.roomColors.floor,
            roughness: 0.85,
            metalness: 0.05
        });
        this.floor = new THREE.Mesh(geo, mat);
        this.floor.rotation.x = -Math.PI / 2;
        this.floor.receiveShadow = true;
        this.scene.add(this.floor);
        this.roomMeshes.push(this.floor);
    }

    _addWalls() {
        const mat = new THREE.MeshStandardMaterial({
            color: this.roomColors.walls,
            roughness: 0.75,
            metalness: 0.02
        });
        const W = this.roomWidth, L = this.roomLength, H = this.roomHeight;
        const hw = W / 2, hl = L / 2;

        const defs = [
            { name: 'back',  pos: [0, H/2, -hl], ry: Math.PI,    gw: W, gh: H },
            { name: 'front', pos: [0, H/2,  hl], ry: 0,           gw: W, gh: H },
            { name: 'left',  pos: [-hw, H/2, 0], ry:  Math.PI/2, gw: L, gh: H },
            { name: 'right', pos: [ hw, H/2, 0], ry: -Math.PI/2, gw: L, gh: H },
        ];

        defs.forEach(({ name, pos, ry, gw, gh }) => {
            const m = new THREE.Mesh(new THREE.PlaneGeometry(gw, gh), mat.clone());
            m.position.set(...pos);
            m.rotation.y = ry;
            m.castShadow = true;
            m.receiveShadow = true;
            this.scene.add(m);
            this.walls[name] = m;
            this.roomMeshes.push(m);
        });
    }

    _addCeiling() {
        const geo = new THREE.PlaneGeometry(this.roomWidth, this.roomLength);
        const mat = new THREE.MeshStandardMaterial({
            color: this.roomColors.ceiling,
            roughness: 0.5,
            side: THREE.BackSide
        });
        this.ceiling = new THREE.Mesh(geo, mat);
        this.ceiling.rotation.x = Math.PI / 2;
        this.ceiling.position.y = this.roomHeight;
        this.ceiling.receiveShadow = true;
        this.scene.add(this.ceiling);
        this.roomMeshes.push(this.ceiling);
    }

    // ============================================================
    // CHARACTER — FIX #2: CapsuleGeometry not in r128
    // ============================================================
    setupCharacter() {
        if (this.character) this.scene.remove(this.character);

        const g = new THREE.Group();

        // Torso (CylinderGeometry works in r128; CapsuleGeometry added in r142)
        const torso = new THREE.Mesh(
            new THREE.CylinderGeometry(0.26, 0.26, 1.05, 12),
            new THREE.MeshStandardMaterial({ color: 0x4f8ef7, roughness: 0.6 })
        );
        torso.position.y = 0.58;
        torso.castShadow = true;
        g.add(torso);

        // Head
        const head = new THREE.Mesh(
            new THREE.SphereGeometry(0.23, 16, 12),
            new THREE.MeshStandardMaterial({ color: 0xfdbcb4, roughness: 0.5 })
        );
        head.position.y = 1.32;
        head.castShadow = true;
        g.add(head);

        // Legs
        [-0.12, 0.12].forEach(xOff => {
            const leg = new THREE.Mesh(
                new THREE.CylinderGeometry(0.09, 0.08, 0.52, 8),
                new THREE.MeshStandardMaterial({ color: 0x2b4fa0, roughness: 0.7 })
            );
            leg.position.set(xOff, 0.04, 0);
            leg.castShadow = true;
            g.add(leg);
        });

        g.position.y = this.groundHeight;
        g.userData.isCharacter = true;
        this.scene.add(g);
        this.character = g;
    }

    // ============================================================
    // LOADERS — FIX #3: was placed OUTSIDE class body in original
    // ============================================================
    setupLoaders() {
        if (typeof GLTFLoader !== 'undefined') {
            this.gltfLoader = new GLTFLoader();
            console.log('✅ GLTFLoader ready');
        } else {
            console.warn('⚠️ GLTFLoader not available – using procedural character');
        }
    }

    // FIX #8: restored functional implementation with graceful fallback
    loadCharacterModel() {
        if (!this.gltfLoader) return;
        this.gltfLoader.load(
            '/images/Hoodie Character.glb',
            (gltf) => {
                while (this.character.children.length) {
                    this.character.remove(this.character.children[0]);
                }
                const model = gltf.scene;
                model.scale.set(1.4, 1.4, 1.4);
                model.position.y = -0.1;
                model.traverse(n => {
                    if (n.isMesh) { n.castShadow = true; n.receiveShadow = true; }
                });
                this.character.add(model);
                if (gltf.animations && gltf.animations.length) {
                    this.mixer = new THREE.AnimationMixer(model);
                    gltf.animations.forEach(clip => this.mixer.clipAction(clip).play());
                }
                console.log('✅ Character model loaded');
            },
            undefined,
            (err) => console.warn('⚠️ Character model not found, using procedural:', err.message)
        );
    }

    // ============================================================
    // TRANSFORM CONTROLS
    // ============================================================
    setupTransformControls() {
        if (typeof TransformControls !== 'undefined') {
            this.transformControls = new TransformControls(this.camera, this.renderer.domElement);
            this.transformControls.setMode('translate');
            this.transformControls.addEventListener('dragging-changed', e => {
                this._tcDragging = e.value;
            });
            this.scene.add(this.transformControls);
            console.log('✅ TransformControls ready');
        }
    }

    onTransformModeChange(mode) {
        this.transformMode = mode;
        if (this.transformControls) this.transformControls.setMode(mode);
    }

    // ============================================================
    // CAMERA UPDATE
    // ============================================================
    updateCameraExplore(delta) {
        const moveDir = new THREE.Vector3();
        if (this.keys['w']) moveDir.z -= 1;
        if (this.keys['s']) moveDir.z += 1;
        if (this.keys['a']) moveDir.x -= 1;
        if (this.keys['d']) moveDir.x += 1;
        moveDir.normalize();

        const yAxis   = new THREE.Vector3(0, 1, 0);
        const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(yAxis, this.cameraRotation.y);
        const right   = new THREE.Vector3(1, 0,  0).applyAxisAngle(yAxis, this.cameraRotation.y);

        const move = new THREE.Vector3()
            .addScaledVector(forward, -moveDir.z)
            .addScaledVector(right,   moveDir.x)
            .normalize()
            .multiplyScalar(this.cameraSpeed * delta);

        const pad = 0.4;
        const nx = this.camera.position.x + move.x;
        const nz = this.camera.position.z + move.z;
        if (Math.abs(nx) < this.roomWidth  / 2 - pad) this.camera.position.x = nx;
        if (Math.abs(nz) < this.roomLength / 2 - pad) this.camera.position.z = nz;
        this.camera.position.y = 1.6; // fixed eye height

        this.character.position.set(this.camera.position.x, this.groundHeight, this.camera.position.z);
        if (moveDir.lengthSq() > 0) {
            this.character.rotation.y = Math.atan2(moveDir.x, -moveDir.z) + this.cameraRotation.y;
            this.isMoving = true;
        } else {
            this.isMoving = false;
        }

        if (this.mixer) this.mixer.update(delta);

        const lookTarget = new THREE.Vector3(0, 0, -1)
            .applyAxisAngle(new THREE.Vector3(1, 0, 0), this.cameraRotation.x)
            .applyAxisAngle(new THREE.Vector3(0, 1, 0), this.cameraRotation.y)
            .multiplyScalar(10)
            .add(this.camera.position);
        this.camera.lookAt(lookTarget);
    }

    updateCameraBuild() {
        if (this.keys['w']) this.orbitCenter.z -= 0.08;
        if (this.keys['s']) this.orbitCenter.z += 0.08;
        if (this.keys['a']) this.orbitCenter.x -= 0.08;
        if (this.keys['d']) this.orbitCenter.x += 0.08;

        this.orbitCenter.x = Math.max(-this.roomWidth  / 2 + 0.5, Math.min(this.roomWidth  / 2 - 0.5, this.orbitCenter.x));
        this.orbitCenter.z = Math.max(-this.roomLength / 2 + 0.5, Math.min(this.roomLength / 2 - 0.5, this.orbitCenter.z));
        this.orbitCenter.y = 1.0;

        const angle = this.buildCameraRotation.y;
        const dist  = this.buildCameraDistance;
        this.camera.position.set(
            this.orbitCenter.x + Math.sin(angle) * dist,
            this.orbitCenter.y + this.buildCameraHeight,
            this.orbitCenter.z + Math.cos(angle) * dist
        );
        this.camera.lookAt(this.orbitCenter);
    }

    // ============================================================
    // FURNITURE
    // ============================================================
    getFurnitureTemplate(type) {
        const T = {
            bed:   { geometry: new THREE.BoxGeometry(1.5, 0.5, 2.2),            color: 0x7b6b5d, yOff: 0.25 },
            chair: { geometry: new THREE.BoxGeometry(0.5, 0.9, 0.5),            color: 0xa07850, yOff: 0.45 },
            table: { geometry: new THREE.BoxGeometry(1.4, 0.05, 0.9),           color: 0xcd9a60, yOff: 0.75 },
            sofa:  { geometry: new THREE.BoxGeometry(2.0, 0.7, 0.9),            color: 0x556080, yOff: 0.35 },
            desk:  { geometry: new THREE.BoxGeometry(1.2, 0.05, 0.6),           color: 0x7a5230, yOff: 0.75 },
            shelf: { geometry: new THREE.BoxGeometry(0.9, 1.5, 0.3),            color: 0x9e8050, yOff: 0.75 },
            lamp:  { geometry: new THREE.CylinderGeometry(0.04, 0.12, 1.6, 8), color: 0xe8d080, yOff: 0.8  },
            plant: { geometry: new THREE.ConeGeometry(0.32, 0.9, 8),            color: 0x3a9040, yOff: 0.45 },
        };
        return T[type] || T.table;
    }

    addFurniture(type, position) {
        const tmpl = this.getFurnitureTemplate(type);
        // FIX #6: pre-create emissive so selectObject can safely set it
        const mat = new THREE.MeshStandardMaterial({
            color: tmpl.color,
            roughness: 0.65,
            metalness: 0.15,
            emissive: new THREE.Color(0x000000),
        });
        const mesh = new THREE.Mesh(tmpl.geometry, mat);

        const pos = position ? position.clone() : new THREE.Vector3(0, 0, 0);
        pos.y = tmpl.yOff;
        mesh.position.copy(pos);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        mesh.userData.furnitureType = type;
        mesh.userData.originalColor = tmpl.color;

        this.scene.add(mesh);
        this.objects.push(mesh);
        return mesh;
    }

    // FIX #6: safe emissive access
    selectObject(obj) {
        if (this.selectedObject) {
            if (this.selectedObject.material && this.selectedObject.material.emissive) {
                this.selectedObject.material.emissive.setHex(0x000000);
            }
        }
        this.selectedObject = obj;
        if (obj && obj.material) {
            if (!obj.material.emissive) obj.material.emissive = new THREE.Color(0x000000);
            obj.material.emissive.setHex(0x2244aa);
        }
        if (this.transformControls) {
            obj ? this.transformControls.attach(obj) : this.transformControls.detach();
        }
        this._refreshUIStats();
    }

    // ============================================================
    // ROOM TEMPLATES
    // ============================================================
    loadRoomTemplate(name) {
        this.clearScene();
        const T = {
            bedroom:    [['bed',0,0],['chair',1.5,1],['lamp',-1.8,1.2],['desk',-1.8,-1.5],['plant',1.8,-1.8]],
            livingroom: [['sofa',0,0.5],['table',0,1.8],['shelf',-2,-1.8],['lamp',1.8,-1.2],['plant',-2,1.5]],
            office:     [['desk',0,0],['chair',0,-0.9],['shelf',-2,0],['lamp',1,0.5],['plant',1.8,1.8]],
            kitchen:    [['table',0,0],['chair',0.9,0],['chair',-0.9,0],['shelf',2,-1.5],['lamp',0,-2]],
        };
        (T[name] || T.bedroom).forEach(([type, x, z]) => {
            this.addFurniture(type, new THREE.Vector3(x, 0, z));
        });
    }

    clearScene() {
        this.objects.forEach(o => this.scene.remove(o));
        this.objects = [];
        if (this.transformControls) this.transformControls.detach();
        this.selectedObject = null;
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    setupEventListeners() {
        document.addEventListener('keydown',   e => this.onKeyDown(e));
        document.addEventListener('keyup',     e => { this.keys[e.key.toLowerCase()] = false; });
        document.addEventListener('mousemove', e => this.onMouseMove(e));
        document.addEventListener('mousedown', e => this.onMouseDown(e));
        document.addEventListener('mouseup',   e => this.onMouseUp(e));
        this.canvas.addEventListener('click',  e => this.onCanvasClick(e));
        this.canvas.addEventListener('contextmenu', e => e.preventDefault());

        // Pointer lock for smooth explore mode
        this.canvas.addEventListener('dblclick', () => {
            if (this.mode === 'explore') {
                this.canvas.requestPointerLock().catch(() => {});
            }
        });

        // Scroll to zoom in build mode
        this.canvas.addEventListener('wheel', e => {
            if (this.mode === 'build') {
                this.buildCameraDistance = Math.max(2, Math.min(14, this.buildCameraDistance + e.deltaY * 0.01));
            }
        }, { passive: true });
    }

    onKeyDown(e) {
        const k = e.key.toLowerCase();
        this.keys[k] = true;

        if (k === 's' && e.ctrlKey) { e.preventDefault(); this.saveRoom(); return; }
        if (k === 'e') { this.toggleMode(); return; }

        if (this.mode === 'build') {
            if (k === 'q') this.onTransformModeChange('translate');
            if (k === 'w') this.onTransformModeChange('rotate');
            if (k === 'r') this.onTransformModeChange('scale');

            const types = ['bed','chair','table','sofa','desk','shelf','lamp','plant'];
            const idx = parseInt(k) - 1;
            if (idx >= 0 && idx < types.length) {
                this.currentFurnitureType = types[idx];
                this._refreshUIStats(); // lightweight update only
            }

            if ((k === 'delete' || k === 'backspace') && this.selectedObject) {
                this.scene.remove(this.selectedObject);
                this.objects = this.objects.filter(o => o !== this.selectedObject);
                if (this.transformControls) this.transformControls.detach();
                this.selectedObject = null;
                this._refreshUIStats();
            }
        }
        // FIX #5: NO updateUI() here — avoids full DOM rebuild every keydown
    }

    onMouseMove(e) {
        // FIX #9: use canvas rect for accurate raycasting coords
        const rect = this.canvas.getBoundingClientRect();
        this.mouse.x =  ((e.clientX - rect.left) / rect.width)  * 2 - 1;
        this.mouse.y = -((e.clientY - rect.top)  / rect.height) * 2 + 1;

        if (this.mode === 'explore') {
            if (document.pointerLockElement === this.canvas) {
                this.cameraRotation.y -= e.movementX * this.cameraSensitivity;
                this.cameraRotation.x -= e.movementY * this.cameraSensitivity;
                this.cameraRotation.x = Math.max(-Math.PI / 2.2, Math.min(Math.PI / 2.2, this.cameraRotation.x));
            } else if (this.mouseButtons.right) {
                // Fallback: right-drag look without pointer lock
                this.cameraRotation.y -= e.movementX * this.cameraSensitivity;
                this.cameraRotation.x -= e.movementY * this.cameraSensitivity;
                this.cameraRotation.x = Math.max(-Math.PI / 2.2, Math.min(Math.PI / 2.2, this.cameraRotation.x));
            }
        } else if (this.mode === 'build') {
            if ((this.mouseButtons.middle || this.mouseButtons.right) && !this._tcDragging) {
                this.buildCameraRotation.y += e.movementX * 0.012;
            }
        }
    }

    onMouseDown(e) {
        if (e.button === 0) this.mouseButtons.left   = true;
        if (e.button === 1) this.mouseButtons.middle = true;
        if (e.button === 2) this.mouseButtons.right  = true;

        if (this.mode === 'build' && e.button === 0) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const hits = this.raycaster.intersectObjects(this.objects);
            this.selectObject(hits.length ? hits[0].object : null);
        }
    }

    onMouseUp(e) {
        if (e.button === 0) this.mouseButtons.left   = false;
        if (e.button === 1) this.mouseButtons.middle = false;
        if (e.button === 2) this.mouseButtons.right  = false;
    }

    onCanvasClick(e) {
        if (this.mode === 'build' && this.currentFurnitureType && e.button === 0) {
            this.raycaster.setFromCamera(this.mouse, this.camera);
            const hits = this.raycaster.intersectObject(this.floor);
            if (hits.length) {
                this.addFurniture(this.currentFurnitureType, hits[0].point);
                this._refreshUIStats();
            }
        }
    }

    // ============================================================
    // UI — FIX #4: separated into three concerns:
    //   renderUI()          — first-time full HTML injection
    //   updateUI()          — full rebuild, only on mode switch
    //   _refreshUIStats()   — fast span-only updates, no DOM rebuild
    // ============================================================
    renderUI() {
        let panel = document.getElementById('editor-ui');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'editor-ui';
            document.body.appendChild(panel);
        }
        panel.innerHTML = this._buildUIHTML();
        this.bindUIActions();
    }

    updateUI() {
        const panel = document.getElementById('editor-ui');
        if (panel) {
            panel.innerHTML = this._buildUIHTML();
            this.bindUIActions();
        }
    }

    _refreshUIStats() {
        const $  = id => document.getElementById(id);
        if ($('stat-objects'))   $('stat-objects').textContent   = this.objects.length;
        if ($('stat-furniture')) $('stat-furniture').textContent = this.currentFurnitureType || 'none';
        if ($('stat-selected'))  $('stat-selected').textContent  = this.selectedObject ? this.selectedObject.userData.furnitureType : '—';
        document.querySelectorAll('.furniture-item').forEach(item => {
            item.classList.toggle('active', item.dataset.type === this.currentFurnitureType);
        });
        document.querySelectorAll('[data-transform]').forEach(btn => {
            btn.classList.toggle('active-btn', btn.dataset.transform === this.transformMode);
        });
    }

    bindUIActions() {
        const panel = document.getElementById('editor-ui');
        if (!panel) return;
        if (this._uiClickHandler) panel.removeEventListener('click', this._uiClickHandler);
        this._uiClickHandler = e => this.onUIClick(e);
        panel.addEventListener('click', this._uiClickHandler);
    }

    onUIClick(e) {
        const t = e.target;
        if (t.id === 'toggle-mode-btn')   { this.toggleMode(); return; }
        if (t.id === 'save-btn')          { this.saveRoom(); return; }
        if (t.id === 'clear-scene-btn' && confirm('Clear all objects?')) {
            this.clearScene(); this._refreshUIStats(); return;
        }
        if (t.dataset.type)      { this.currentFurnitureType = t.dataset.type; this._refreshUIStats(); return; }
        if (t.dataset.template)  { this.loadRoomTemplate(t.dataset.template); this._refreshUIStats(); return; }
        if (t.dataset.transform) { this.onTransformModeChange(t.dataset.transform); this._refreshUIStats(); return; }

        let resized = false;
        if (t.id === 'expand-width')  { this.roomWidth  += 0.5; resized = true; }
        if (t.id === 'shrink-width')  { this.roomWidth   = Math.max(2, this.roomWidth  - 0.5); resized = true; }
        if (t.id === 'expand-length') { this.roomLength += 0.5; resized = true; }
        if (t.id === 'shrink-length') { this.roomLength  = Math.max(2, this.roomLength - 0.5); resized = true; }
        if (t.id === 'expand-height') { this.roomHeight += 0.5; resized = true; }
        if (t.id === 'shrink-height') { this.roomHeight  = Math.max(2, this.roomHeight - 0.5); resized = true; }
        if (resized) {
            this.setupRoom();
            const $ = id => document.getElementById(id);
            if ($('room-width-label'))  $('room-width-label').textContent  = `W: ${this.roomWidth.toFixed(1)}m`;
            if ($('room-length-label')) $('room-length-label').textContent = `L: ${this.roomLength.toFixed(1)}m`;
            if ($('room-height-label')) $('room-height-label').textContent = `H: ${this.roomHeight.toFixed(1)}m`;
        }
    }

    _buildUIHTML() {
        const isExplore = this.mode === 'explore';
        return `
<style>
#editor-ui {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    pointer-events: none;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    z-index: 1000;
    user-select: none;
}
.ui-panel {
    position: absolute;
    pointer-events: auto;
    background: linear-gradient(145deg, rgba(10,15,30,0.93), rgba(14,20,40,0.89));
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 1px solid rgba(100,140,255,0.18);
    border-radius: 14px;
    padding: 16px 18px;
    color: #dde4ff;
    box-shadow: 0 8px 40px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.06);
    min-width: 230px;
}
.ui-panel h3 {
    margin: 0 0 10px 0;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #7aa2ff;
}
.top-left   { top: 18px; left: 18px; }
.top-right  { top: 18px; right: 18px; min-width: 180px; }
.bottom-left { bottom: 18px; left: 18px; max-height: 64vh; overflow-y: auto; }

/* Scrollbar */
.bottom-left::-webkit-scrollbar { width: 4px; }
.bottom-left::-webkit-scrollbar-track { background: transparent; }
.bottom-left::-webkit-scrollbar-thumb { background: rgba(100,140,255,0.3); border-radius: 4px; }

.info-row {
    font-size: 12px; margin: 5px 0;
    color: #8898cc;
    display: flex; justify-content: space-between; align-items: center;
}
.info-row span { color: #e2eaff; font-weight: 600; }

.mode-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: ${isExplore ? 'rgba(160,100,255,0.15)' : 'rgba(60,140,255,0.15)'};
    border: 1px solid ${isExplore ? 'rgba(160,100,255,0.4)' : 'rgba(60,140,255,0.4)'};
    color: ${isExplore ? '#c090ff' : '#6eb0ff'};
    border-radius: 20px; padding: 6px 14px;
    font-size: 12px; font-weight: 600; margin-bottom: 12px; width: 100%; box-sizing: border-box;
}
.mode-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    background: ${isExplore ? '#c090ff' : '#6eb0ff'};
    box-shadow: 0 0 7px ${isExplore ? '#c090ff' : '#6eb0ff'};
    animation: blink 1.8s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.35} }

button {
    display: block; width: 100%; padding: 9px 14px;
    background: rgba(255,255,255,0.05);
    color: #ccd6f6; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 500;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    margin: 4px 0; text-align: left; box-sizing: border-box;
}
button:hover {
    background: rgba(100,150,255,0.18);
    border-color: rgba(100,150,255,0.5);
    color: #fff;
}
button.danger { border-color: rgba(255,80,80,0.3); color: #ff9090; }
button.danger:hover { background: rgba(255,80,80,0.15); border-color: rgba(255,100,100,0.5); }

.furniture-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin: 10px 0; }
.furniture-item {
    padding: 10px 2px; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
    cursor: pointer; text-align: center; font-size: 18px;
    transition: background 0.15s, border-color 0.15s; line-height: 1;
}
.furniture-item:hover { background: rgba(100,150,255,0.18); border-color: rgba(100,150,255,0.4); }
.furniture-item.active {
    background: rgba(80,130,255,0.28); border-color: #6090ff;
    box-shadow: 0 0 10px rgba(80,130,255,0.25);
}

.template-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; margin: 10px 0; }
.template-item {
    padding: 9px 6px; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
    cursor: pointer; font-size: 11px; text-align: center;
    color: #b0bcd8; transition: all 0.15s;
}
.template-item:hover { background: rgba(80,200,170,0.15); border-color: rgba(80,200,170,0.4); color: #fff; }

.transform-buttons { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 8px 0; }
.transform-buttons button { margin: 0; text-align: center; font-size: 11px; padding: 8px 4px; }
button.active-btn {
    background: rgba(80,130,255,0.32) !important;
    border-color: #6090ff !important;
    color: #fff !important;
}

.section-sep {
    border: none; border-top: 1px solid rgba(255,255,255,0.07); margin: 12px 0;
}

.size-label { font-size: 10px; color: #6878aa; margin: 8px 0 3px; letter-spacing: 0.3px; }
.size-row { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
.size-row button { margin: 0; padding: 7px 6px; font-size: 11px; text-align: center; }

.help-box {
    margin-top: 10px; padding: 10px 12px;
    background: rgba(0,0,0,0.2); border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.05);
    font-size: 11px; line-height: 1.7; color: #7888b8;
}
.help-box strong { color: #9aaad8; display: block; margin-bottom: 3px; }
</style>

<!-- TOP LEFT -->
<div class="ui-panel top-left">
    <div class="mode-badge">
        <span class="mode-dot"></span>
        ${isExplore ? '🎮 Explore Mode' : '🛠 Build Mode'}
    </div>
    <button id="toggle-mode-btn">⇄ Switch to ${isExplore ? 'Build' : 'Explore'} &nbsp;<kbd style="opacity:.5;font-size:10px">[E]</kbd></button>
    <button id="save-btn">💾 Save Room</button>
    <button class="danger" id="clear-scene-btn">🗑 Clear All</button>
    <div class="help-box">
        ${isExplore ? `
            <strong>Explore Controls</strong>
            WASD — Walk around<br>
            Double-click — Lock mouse<br>
            Mouse — Look around<br>
            Right-drag — Look (no lock)<br>
            E — Build mode
        ` : `
            <strong>Build Controls</strong>
            Click floor — Place furniture<br>
            Click object — Select it<br>
            Q / W / R — Move · Rotate · Scale<br>
            1–8 — Pick furniture<br>
            Right/Middle drag — Orbit<br>
            Scroll — Zoom in/out<br>
            Delete — Remove selected<br>
            Ctrl+S — Save · E — Explore
        `}
    </div>
</div>

<!-- TOP RIGHT -->
<div class="ui-panel top-right">
    <h3>📊 Scene</h3>
    <div class="info-row">Objects <span id="stat-objects">${this.objects.length}</span></div>
    <div class="info-row">Room <span>${this.roomWidth.toFixed(1)}×${this.roomLength.toFixed(1)}×${this.roomHeight.toFixed(1)}</span></div>
    <div class="info-row">Tool <span id="stat-furniture">${this.currentFurnitureType || 'none'}</span></div>
    <div class="info-row">Selected <span id="stat-selected">${this.selectedObject ? this.selectedObject.userData.furnitureType : '—'}</span></div>
    <div class="info-row">FPS <span id="fps-counter">—</span></div>
</div>

<!-- BOTTOM LEFT (build only) -->
${!isExplore ? `
<div class="ui-panel bottom-left">
    <h3>🪑 Furniture &nbsp;<small style="opacity:.4;font-weight:400">[1–8]</small></h3>
    <div class="furniture-grid">
        ${[['bed','🛏️'],['chair','🪑'],['table','📦'],['sofa','🛋️'],['desk','🖥️'],['shelf','📚'],['lamp','💡'],['plant','🪴']]
            .map(([type, icon]) => `<div class="furniture-item${this.currentFurnitureType===type?' active':''}" data-type="${type}" title="${type}">${icon}</div>`)
            .join('')}
    </div>

    ${this.selectedObject ? `
    <hr class="section-sep">
    <h3>✨ Transform</h3>
    <div class="transform-buttons">
        <button data-transform="translate" class="${this.transformMode==='translate'?'active-btn':''}">Move<br><small style="opacity:.5">Q</small></button>
        <button data-transform="rotate"    class="${this.transformMode==='rotate'   ?'active-btn':''}">Rotate<br><small style="opacity:.5">W</small></button>
        <button data-transform="scale"     class="${this.transformMode==='scale'    ?'active-btn':''}">Scale<br><small style="opacity:.5">R</small></button>
    </div>` : ''}

    <hr class="section-sep">
    <h3>🏠 Templates</h3>
    <div class="template-grid">
        <div class="template-item" data-template="bedroom">🛏️ Bedroom</div>
        <div class="template-item" data-template="livingroom">🛋️ Living</div>
        <div class="template-item" data-template="office">🖥️ Office</div>
        <div class="template-item" data-template="kitchen">🍳 Kitchen</div>
    </div>

    <hr class="section-sep">
    <h3>📐 Room Size</h3>
    <div class="size-label" id="room-width-label">W: ${this.roomWidth.toFixed(1)}m</div>
    <div class="size-row">
        <button id="expand-width">+ Width</button>
        <button id="shrink-width">− Width</button>
    </div>
    <div class="size-label" id="room-length-label">L: ${this.roomLength.toFixed(1)}m</div>
    <div class="size-row">
        <button id="expand-length">+ Length</button>
        <button id="shrink-length">− Length</button>
    </div>
    <div class="size-label" id="room-height-label">H: ${this.roomHeight.toFixed(1)}m</div>
    <div class="size-row">
        <button id="expand-height">+ Height</button>
        <button id="shrink-height">− Height</button>
    </div>
</div>
` : ''}
`;
    }

    // ============================================================
    // GAME LOGIC
    // ============================================================
    toggleMode() {
        this.mode = this.mode === 'explore' ? 'build' : 'explore';
        if (this.mode === 'explore') {
            this.canvas.requestPointerLock().catch(() => {});
        } else {
            document.exitPointerLock();
            this.orbitCenter.copy(this.character.position);
            this.orbitCenter.y = 1.0;
        }
        if (this.transformControls) this.transformControls.detach();
        this.selectedObject = null;
        this.updateUI(); // full rebuild only on mode switch
    }

    saveRoom() {
        const data = {
            room: { width: this.roomWidth, length: this.roomLength, height: this.roomHeight },
            objects: this.objects.map(o => ({
                type:     o.userData.furnitureType,
                position: o.position.toArray(),
                rotation: [o.rotation.x, o.rotation.y, o.rotation.z],
                scale:    o.scale.toArray(),
            }))
        };
        console.log('💾 Room data:', JSON.stringify(data, null, 2));
        alert('✅ Room saved – open console (F12) to view JSON');
    }

    // ============================================================
    // RENDER LOOP — FIX #10: performance.now() + delta cap
    // ============================================================
    startRenderLoop() {
        let last = performance.now(), frames = 0, fpsAcc = 0;

        const animate = (now) => {
            requestAnimationFrame(animate);
            const delta = Math.min((now - last) / 1000, 0.1); // cap at 100ms to avoid spiral-of-death
            last = now;

            if (this.mode === 'explore') this.updateCameraExplore(delta);
            else                         this.updateCameraBuild();

            this.renderer.render(this.scene, this.camera);

            frames++;
            fpsAcc += delta;
            if (fpsAcc >= 1) {
                const el = document.getElementById('fps-counter');
                if (el) el.textContent = frames;
                frames = 0; fpsAcc = 0;
            }
        };

        requestAnimationFrame(animate);
    }

    onWindowResize() {
        this.camera.aspect = this.canvas.clientWidth / this.canvas.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(this.canvas.clientWidth, this.canvas.clientHeight);
    }
}

// ============================================================
// INIT
// ============================================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.editor = new AdvancedRoom3DEditor();
        window.editor.init();
    });
} else {
    window.editor = new AdvancedRoom3DEditor();
    window.editor.init();
}