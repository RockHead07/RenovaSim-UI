import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class Furniture {
    static MODELS = {
        bed: {
            emoji: '🛏️',
            size: [1.4, 0.6, 2.0],
            color: 0x8b7355,
        },
        chair: {
            emoji: '🪑',
            size: [0.6, 0.8, 0.6],
            color: 0x654321,
        },
        table: {
            emoji: '📦',
            size: [1.0, 0.8, 1.0],
            color: 0xa0826d,
        },
        sofa: {
            emoji: '🛋️',
            size: [2.0, 0.8, 0.9],
            color: 0x6b5b4f,
        },
        desk: {
            emoji: '🖥️',
            size: [1.2, 0.75, 0.6],
            color: 0x8b7355,
        },
        shelf: {
            emoji: '📚',
            size: [0.8, 1.5, 0.4],
            color: 0x654321,
        },
        lamp: {
            emoji: '🔦',
            size: [0.2, 0.5, 0.2],
            color: 0xffff00,
        },
        plant: {
            emoji: '🪴',
            size: [0.4, 0.5, 0.4],
            color: 0x228b22,
        },
    };

    static createModel(type) {
        const config = Furniture.MODELS[type];
        if (!config) {
            console.warn(`Unknown furniture type: ${type}`);
            return null;
        }

        const [w, h, d] = config.size;
        const geometry = new THREE.BoxGeometry(w, h, d);
        const material = new THREE.MeshStandardMaterial({
            color: config.color,
            metalness: 0.2,
            roughness: 0.7,
        });

        const mesh = new THREE.Mesh(geometry, material);
        mesh.castShadow = true;
        mesh.receiveShadow = true;

        // Store metadata
        mesh.userData.type = type;
        mesh.userData.emoji = config.emoji;

        return mesh;
    }

    static getAvailableTypes() {
        return Object.keys(Furniture.MODELS);
    }
}

export class Raycaster3D {
    constructor(camera, scene) {
        this.camera = camera;
        this.scene = scene;
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();
        this.selectedObject = null;
    }

    setMousePosition(event) {
        const rect = event.target.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / window.innerWidth) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top - 60) / (window.innerHeight - 60)) * 2 + 1;
    }

    getIntersections(includeFloor = false) {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        let objects = this.scene.children.filter(obj => {
            if (!includeFloor && obj.userData.isFloor) return false;
            return obj instanceof THREE.Mesh && obj.userData.type !== undefined;
        });

        return this.raycaster.intersectObjects(objects);
    }

    getFloorIntersection() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const floorObjects = this.scene.children.filter(obj => obj.userData.isFloor);
        return this.raycaster.intersectObjects(floorObjects);
    }

    selectObject(mesh) {
        if (this.selectedObject) {
            this.selectedObject.userData.highlight?.remove();
        }

        this.selectedObject = mesh;

        if (mesh) {
            // Add highlight
            const edges = new THREE.EdgesGeometry(mesh.geometry);
            const wireframe = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: 0x00ff00, linewidth: 2 }));
            mesh.add(wireframe);
            mesh.userData.highlight = wireframe;
        }

        return mesh;
    }

    deselectObject() {
        return this.selectObject(null);
    }

    getSelectedObject() {
        return this.selectedObject;
    }
}

export default { Furniture, Raycaster3D };
