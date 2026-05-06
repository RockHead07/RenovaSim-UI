/**
 * 3D Interior Editor - Standalone Version
 * Waits for THREE to be loaded, then initializes
 */

// Wait for THREE to be loaded
function waitForTHREE(callback, attempts = 0) {
    if (typeof THREE !== 'undefined' && THREE.Raycaster && window.TransformControls) {
        console.log('✅ THREE.js and TransformControls loaded!');
        callback();
    } else if (attempts < 100) {
        setTimeout(() => waitForTHREE(callback, attempts + 1), 100);
    } else {
        console.error('❌ Failed to load THREE.js or TransformControls');
        console.log('THREE:', typeof THREE, THREE?.Raycaster);
        console.log('TransformControls:', typeof window.TransformControls);
    }
}

class Room3DEditor {
    constructor() {
        // Ensure THREE is loaded
        if (typeof THREE === 'undefined') {
            throw new Error('THREE.js is not loaded');
        }

        this.canvas = document.getElementById('canvas');
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.transformControls = null;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        // State
        this.isExploreMode = true;
        this.selectedObject = null;
        this.currentTool = null;
        this.sceneObjects = [];
        this.pointerLocked = false;

        // Room data
        this.roomData = window.roomData;
        this.lastTime = Date.now();

        // Furniture definitions
        this.furnitureTypes = {
            bed: { emoji: '🛏️', size: [1.4, 0.6, 2.0], color: 0x8b7355 },
            chair: { emoji: '🪑', size: [0.6, 0.8, 0.6], color: 0x654321 },
            table: { emoji: '📦', size: [1.0, 0.8, 1.0], color: 0xa0826d },
            sofa: { emoji: '🛋️', size: [2.0, 0.8, 0.9], color: 0x6b5b4f },
            desk: { emoji: '🖥️', size: [1.2, 0.75, 0.6], color: 0x8b7355 },
            shelf: { emoji: '📚', size: [0.8, 1.5, 0.4], color: 0x654321 },
            lamp: { emoji: '🔦', size: [0.2, 0.5, 0.2], color: 0xffff00 },
            plant: { emoji: '🪴', size: [0.4, 0.5, 0.4], color: 0x228b22 },
        };
    }

    init() {
        // Initialize Three.js scene
        this.setupScene();
        this.setupLights();
        this.setupRoom();
        this.setupControls();
        this.setupUI();
        this.loadObjects();
        this.setupEventListeners();
        this.startRenderLoop();
        
        console.log('✅ 3D Editor initialized successfully');
    }

    setupScene() {
        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x1e293b);
        this.scene.fog = new THREE.Fog(0x1e293b, 50, 100);

        // Camera
        const width = window.innerWidth;
        const height = window.innerHeight - 60;
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        
        const roomWidth = this.roomData.room.width;
        const roomLength = this.roomData.room.length;
        const roomHeight = this.roomData.room.height;
        
        this.camera.position.set(roomWidth / 2, roomHeight / 2, roomLength / 2 + 2);
        this.camera.lookAt(roomWidth / 2, roomHeight / 1.5, roomLength / 2);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFShadowShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;

        window.addEventListener('resize', () => this.onWindowResize());
    }

    setupLights() {
        const w = this.roomData.room.width;
        const h = this.roomData.room.height;
        const l = this.roomData.room.length;

        // Ambient light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        // Directional light
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(w / 2, h, l / 2);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        directionalLight.shadow.camera.left = -w;
        directionalLight.shadow.camera.right = w;
        directionalLight.shadow.camera.top = h;
        directionalLight.shadow.camera.bottom = 0;
        directionalLight.shadow.camera.near = 0.5;
        directionalLight.shadow.camera.far = h * 2;
        this.scene.add(directionalLight);

        // Point light
        const pointLight = new THREE.PointLight(0xffffff, 0.3);
        pointLight.position.set(w / 4, h * 0.8, l / 4);
        this.scene.add(pointLight);
    }

    setupRoom() {
        const w = this.roomData.room.width;
        const l = this.roomData.room.length;
        const h = this.roomData.room.height;

        // Materials
        const floorMat = new THREE.MeshStandardMaterial({
            color: 0xd4d4d8,
            metalness: 0.1,
            roughness: 0.8,
        });

        const wallMat = new THREE.MeshStandardMaterial({
            color: 0xf1f5f9,
            metalness: 0.0,
            roughness: 0.9,
        });

        // Floor
        const floorGeo = new THREE.PlaneGeometry(w, l);
        const floor = new THREE.Mesh(floorGeo, floorMat);
        floor.rotation.x = -Math.PI / 2;
        floor.receiveShadow = true;
        floor.userData.isFloor = true;
        this.scene.add(floor);

        // Ceiling
        const ceilingGeo = new THREE.PlaneGeometry(w, l);
        const ceiling = new THREE.Mesh(ceilingGeo, wallMat);
        ceiling.position.y = h;
        ceiling.rotation.x = Math.PI / 2;
        this.scene.add(ceiling);

        // Walls
        const wallConfigs = [
            { w, h, x: 0, y: h / 2, z: l / 2, rx: 0, ry: 0 },
            { w, h, x: 0, y: h / 2, z: -l / 2, rx: 0, ry: 0 },
            { w: l, h, x: w / 2, y: h / 2, z: 0, rx: 0, ry: Math.PI / 2 },
            { w: l, h, x: -w / 2, y: h / 2, z: 0, rx: 0, ry: Math.PI / 2 },
        ];

        wallConfigs.forEach(cfg => {
            const geo = new THREE.PlaneGeometry(cfg.w, cfg.h);
            const wall = new THREE.Mesh(geo, wallMat);
            wall.position.set(cfg.x, cfg.y, cfg.z);
            wall.rotation.x = cfg.rx;
            wall.rotation.y = cfg.ry;
            wall.receiveShadow = true;
            this.scene.add(wall);
        });

        // Grid
        const gridHelper = new THREE.GridHelper(Math.max(w, l) * 1.5, 20, 0x444444, 0x888888);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    setupControls() {
        // Explore controls
        this.exploreCamera = {
            yaw: 0,
            pitch: 0,
            speed: 5,
            mouseSensitivity: 0.002,
            mouseDelta: { x: 0, y: 0 },
            keys: {},
        };

        // Transform controls - try to get from window or THREE
        const TC = window.TransformControls || (window.THREE && window.THREE.TransformControls);
        if (!TC) {
            console.warn('⚠️ TransformControls not available, edit mode will have limited functionality');
            this.transformControls = null;
        } else {
            try {
                this.transformControls = new TC(this.camera, this.renderer.domElement);
                this.transformControls.addEventListener('change', () => this.renderer.render(this.scene, this.camera));
                // Only add to scene if it's actually an Object3D
                if (this.transformControls instanceof THREE.Object3D) {
                    this.scene.add(this.transformControls);
                }
                console.log('✅ TransformControls initialized');
            } catch (err) {
                console.warn('⚠️ Failed to initialize Transform Controls:', err.message);
                this.transformControls = null;
            }
        }
    }

    setupUI() {
        const uiPanel = document.getElementById('editor-ui');
        uiPanel.innerHTML = `
            <style>
                #editor-ui {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    pointer-events: none;
                    z-index: 10;
                    font-family: system-ui, -apple-system, sans-serif;
                }

                .ui-panel {
                    pointer-events: all;
                    background: rgba(15, 23, 42, 0.95);
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(71, 85, 105, 0.5);
                    border-radius: 8px;
                    padding: 16px;
                    color: white;
                    position: absolute;
                }

                #controls-panel {
                    top: 20px;
                    left: 20px;
                    width: 280px;
                }

                #furniture-panel {
                    bottom: 20px;
                    left: 20px;
                    max-width: 400px;
                }

                #status-panel {
                    bottom: 20px;
                    right: 20px;
                    width: 300px;
                }

                .button {
                    display: block;
                    width: 100%;
                    padding: 10px;
                    background: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    margin: 8px 0;
                    font-weight: 500;
                    transition: background 0.2s;
                }

                .button:hover {
                    background: #2563eb;
                }

                .button.danger {
                    background: #dc2626;
                }

                .button.danger:hover {
                    background: #b91c1c;
                }

                .furniture-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
                    gap: 8px;
                    margin: 12px 0;
                }

                .furniture-item {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 8px;
                    background: rgba(71, 85, 105, 0.3);
                    border: 2px solid rgba(71, 85, 105, 0.5);
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 24px;
                    transition: all 0.2s;
                }

                .furniture-item:hover {
                    background: rgba(71, 85, 105, 0.6);
                    border-color: #3b82f6;
                }

                .furniture-item.selected {
                    background: rgba(59, 130, 246, 0.3);
                    border-color: #3b82f6;
                }

                .furniture-label {
                    font-size: 10px;
                    color: #94a3b8;
                    margin-top: 4px;
                }

                .mode-badge {
                    display: inline-block;
                    padding: 6px 12px;
                    background: #8b5cf6;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    margin-bottom: 8px;
                }

                .mode-badge.edit {
                    background: #06b6d4;
                }

                .section-title {
                    font-size: 14px;
                    font-weight: 600;
                    margin: 12px 0 8px 0;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #cbd5e1;
                }

                .info-text {
                    font-size: 12px;
                    color: #94a3b8;
                    margin: 4px 0;
                }
            </style>

            <div id="controls-panel" class="ui-panel">
                <div class="section-title">3D Editor</div>
                <div class="mode-badge" id="mode-display">EXPLORE MODE</div>
                
                <button class="button" id="toggle-mode-btn">Switch Mode [E]</button>
                <button class="button danger" id="clear-btn">Clear Scene</button>
                <button class="button" id="save-btn">💾 Save</button>

                <div class="section-title">Info</div>
                <div class="info-text">Objects: <span id="obj-count">0</span></div>
                <div class="info-text">Mode: <span id="mode-text">Explore</span></div>
            </div>

            <div id="furniture-panel" class="ui-panel">
                <div class="section-title">Furniture</div>
                <div class="furniture-grid" id="furniture-grid"></div>
            </div>

            <div id="status-panel" class="ui-panel">
                <div class="section-title">Status</div>
                <div class="info-text" id="status-msg">Ready</div>
                <div class="info-text" id="fps">FPS: 60</div>
            </div>
        `;

        // Populate furniture
        const grid = document.getElementById('furniture-grid');
        Object.entries(this.furnitureTypes).forEach(([type, cfg]) => {
            const item = document.createElement('div');
            item.className = 'furniture-item';
            item.innerHTML = `<div>${cfg.emoji}</div><div class="furniture-label">${type}</div>`;
            item.addEventListener('click', () => this.selectTool(type, item));
            grid.appendChild(item);
        });

        // Button events
        document.getElementById('toggle-mode-btn').addEventListener('click', () => this.toggleMode());
        document.getElementById('clear-btn').addEventListener('click', () => {
            if (confirm('Clear all objects?')) this.clearScene();
        });
        document.getElementById('save-btn').addEventListener('click', () => this.saveRoom());
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => {
            this.exploreCamera.keys[e.key.toLowerCase()] = true;
            if (e.key.toLowerCase() === 'e') this.toggleMode();
        });

        document.addEventListener('keyup', (e) => {
            this.exploreCamera.keys[e.key.toLowerCase()] = false;
        });

        // Mouse
        this.canvas.addEventListener('click', () => {
            if (this.isExploreMode && !this.pointerLocked) {
                this.canvas.requestPointerLock?.();
            } else if (!this.isExploreMode) {
                this.onCanvasClick(event);
            }
        });

        document.addEventListener('pointerlockchange', () => {
            this.pointerLocked = document.pointerLockElement === this.canvas;
        });

        document.addEventListener('mousemove', (e) => {
            if (this.isExploreMode && this.pointerLocked) {
                this.exploreCamera.mouseDelta.x += e.movementX * this.exploreCamera.mouseSensitivity;
                this.exploreCamera.mouseDelta.y += e.movementY * this.exploreCamera.mouseSensitivity;
            } else if (!this.isExploreMode) {
                const rect = this.canvas.getBoundingClientRect();
                this.mouse.x = ((e.clientX - rect.left) / window.innerWidth) * 2 - 1;
                this.mouse.y = -((e.clientY - rect.top - 60) / (window.innerHeight - 60)) * 2 + 1;
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!this.isExploreMode) {
                if (e.key === 'Delete') this.deleteSelected();
                if (this.transformControls) {
                    if (e.key.toLowerCase() === 'g') this.transformControls.setMode('translate');
                    if (e.key.toLowerCase() === 'r') this.transformControls.setMode('rotate');
                    if (e.key.toLowerCase() === 's') this.transformControls.setMode('scale');
                }
            }
        });
    }

    selectTool(type, element) {
        document.querySelectorAll('.furniture-item').forEach(el => el.classList.remove('selected'));
        if (element) element.classList.add('selected');
        this.currentTool = type;
    }

    toggleMode() {
        this.isExploreMode = !this.isExploreMode;

        if (this.isExploreMode) {
            this.transformControls.detach();
            document.exitPointerLock?.();
            document.getElementById('mode-display').textContent = 'EXPLORE MODE';
            document.getElementById('mode-display').className = 'mode-badge';
            document.getElementById('mode-text').textContent = 'Explore';
        } else {
            if (this.transformControls) this.transformControls.attach(this.selectedObject || new THREE.Object3D());
            document.getElementById('mode-display').textContent = 'EDIT MODE';
            document.getElementById('mode-display').className = 'mode-badge edit';
            document.getElementById('mode-text').textContent = 'Edit';
        }

        document.getElementById('status-msg').textContent = this.isExploreMode ? 'Explore active' : 'Edit active';
    }

    onCanvasClick(e) {
        this.raycaster.setFromCamera(this.mouse, this.camera);

        // Check object intersection
        const intersects = this.raycaster.intersectObjects(this.sceneObjects);
        if (intersects.length > 0) {
            if (this.selectedObject) {
                if (this.transformControls) this.transformControls.detach(this.selectedObject);
            }
            this.selectedObject = intersects[0].object;
            if (this.transformControls) this.transformControls.attach(this.selectedObject);
            return;
        }

        // Place furniture
        if (this.currentTool) {
            const floorPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 0);
            const point = new THREE.Vector3();
            this.raycaster.ray.intersectPlane(floorPlane, point);
            this.addObject(this.currentTool, [point.x, 0.1, point.z]);
        }
    }

    addObject(type, position = [0, 0.1, 0], rotation = [0, 0, 0], scale = [1, 1, 1]) {
        const cfg = this.furnitureTypes[type];
        if (!cfg) return;

        const [w, h, d] = cfg.size;
        const geo = new THREE.BoxGeometry(w, h, d);
        const mat = new THREE.MeshStandardMaterial({
            color: cfg.color,
            metalness: 0.2,
            roughness: 0.7,
        });

        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(...position);
        mesh.rotation.set(...rotation);
        mesh.scale.set(...scale);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        mesh.userData.type = type;
        mesh.userData.emoji = cfg.emoji;

        this.scene.add(mesh);
        this.sceneObjects.push(mesh);
    }

    deleteSelected() {
        if (this.selectedObject && this.sceneObjects.includes(this.selectedObject)) {
            this.scene.remove(this.selectedObject);
            this.sceneObjects = this.sceneObjects.filter(o => o !== this.selectedObject);
            this.transformControls.detach();
            this.selectedObject = null;
        }
    }

    clearScene() {
        this.sceneObjects.forEach(obj => this.scene.remove(obj));
        this.sceneObjects = [];
        this.selectedObject = null;
        document.getElementById('status-msg').textContent = 'Scene cleared';
    }

    loadObjects() {
        if (this.roomData.objects && this.roomData.objects.length > 0) {
            this.roomData.objects.forEach(obj => {
                this.addObject(obj.type, obj.position, obj.rotation, obj.scale);
            });
        }
    }

    async saveRoom() {
        const btn = document.getElementById('save-btn');
        btn.disabled = true;
        btn.textContent = '⏳ Saving...';

        try {
            const objects = this.sceneObjects.map(obj => ({
                type: obj.userData.type,
                position: [obj.position.x, obj.position.y, obj.position.z],
                rotation: [obj.rotation.x, obj.rotation.y, obj.rotation.z],
                scale: [obj.scale.x, obj.scale.y, obj.scale.z],
            }));

            const response = await fetch(window.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                },
                body: JSON.stringify({ objects }),
            });

            if (response.ok) {
                document.getElementById('status-msg').textContent = '✅ Saved!';
                setTimeout(() => {
                    document.getElementById('status-msg').textContent = 'Ready';
                }, 2000);
            } else {
                throw new Error('Save failed');
            }
        } catch (error) {
            document.getElementById('status-msg').textContent = '❌ Error: ' + error.message;
        } finally {
            btn.disabled = false;
            btn.textContent = '💾 Save';
        }
    }

    updateExploreControls() {
        if (!this.isExploreMode || !this.pointerLocked) return;

        // Update rotation
        this.exploreCamera.yaw += this.exploreCamera.mouseDelta.x;
        this.exploreCamera.pitch -= this.exploreCamera.mouseDelta.y;
        this.exploreCamera.pitch = Math.max(-Math.PI / 2, Math.min(Math.PI / 2, this.exploreCamera.pitch));

        this.camera.rotation.order = 'YXZ';
        this.camera.rotation.y = this.exploreCamera.yaw;
        this.camera.rotation.x = this.exploreCamera.pitch;

        this.exploreCamera.mouseDelta.x = 0;
        this.exploreCamera.mouseDelta.y = 0;

        // Movement
        const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.exploreCamera.yaw);
        const right = new THREE.Vector3(1, 0, 0).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.exploreCamera.yaw);
        const direction = new THREE.Vector3();

        if (this.exploreCamera.keys['w']) direction.add(forward);
        if (this.exploreCamera.keys['s']) direction.add(forward.clone().multiplyScalar(-1));
        if (this.exploreCamera.keys['a']) direction.add(right.clone().multiplyScalar(-1));
        if (this.exploreCamera.keys['d']) direction.add(right);

        if (direction.length() > 0) {
            direction.normalize().multiplyScalar(this.exploreCamera.speed * 0.016);
            this.camera.position.add(direction);
        }
    }

    onWindowResize() {
        const width = window.innerWidth;
        const height = window.innerHeight - 60;
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    startRenderLoop() {
        const animate = () => {
            requestAnimationFrame(animate);

            const now = Date.now();
            const deltaTime = (now - this.lastTime) / 1000;
            this.lastTime = now;

            this.updateExploreControls();
            this.renderer.render(this.scene, this.camera);

            // Update UI
            document.getElementById('obj-count').textContent = this.sceneObjects.length;
            const fps = Math.round(1 / deltaTime);
            document.getElementById('fps').textContent = `FPS: ${fps}`;
        };

        animate();
    }
}

// Initialization is handled by bootstrap-editor.js which loads THREE first
console.log('✅ Room3DEditor class loaded and ready for initialization');
