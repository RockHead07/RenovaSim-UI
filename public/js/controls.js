import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class ExploreControls {
    constructor(camera, canvas) {
        this.camera = camera;
        this.canvas = canvas;

        // Movement state
        this.keys = {};
        this.mouseDown = false;
        this.mouseDelta = { x: 0, y: 0 };

        // Camera state
        this.yaw = 0;
        this.pitch = 0;
        this.speed = 5;
        this.mouseSensitivity = 0.002;
        this.pointerLocked = false;

        // Bounds
        this.minY = 0.1;
        this.maxY = 10;
        this.minX = 0;
        this.maxX = 10;
        this.minZ = 0;
        this.maxZ = 10;

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => {
            this.keys[e.key.toLowerCase()] = true;
        });

        document.addEventListener('keyup', (e) => {
            this.keys[e.key.toLowerCase()] = false;
        });

        // Mouse
        this.canvas.addEventListener('click', () => {
            if (!this.pointerLocked) {
                this.canvas.requestPointerLock?.();
            }
        });

        document.addEventListener('pointerlockchange', () => {
            this.pointerLocked = document.pointerLockElement === this.canvas;
        });

        document.addEventListener('mousemove', (e) => {
            if (this.pointerLocked) {
                this.mouseDelta.x += e.movementX * this.mouseSensitivity;
                this.mouseDelta.y += e.movementY * this.mouseSensitivity;
            }
        });
    }

    update(deltaTime) {
        if (!this.pointerLocked) return;

        // Update rotation
        this.yaw += this.mouseDelta.x;
        this.pitch -= this.mouseDelta.y;
        this.pitch = Math.max(-Math.PI / 2, Math.min(Math.PI / 2, this.pitch));

        // Apply rotation to camera
        this.camera.rotation.order = 'YXZ';
        this.camera.rotation.y = this.yaw;
        this.camera.rotation.x = this.pitch;

        this.mouseDelta.x = 0;
        this.mouseDelta.y = 0;

        // Movement
        const direction = new THREE.Vector3();
        const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.yaw);
        const right = new THREE.Vector3(1, 0, 0).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.yaw);

        if (this.keys['w']) direction.add(forward);
        if (this.keys['s']) direction.add(forward.multiplyScalar(-1));
        if (this.keys['a']) direction.add(right.multiplyScalar(-1));
        if (this.keys['d']) direction.add(right);

        direction.y = 0;
        if (direction.length() > 0) {
            direction.normalize();
            const movement = direction.multiplyScalar(this.speed * deltaTime);
            this.camera.position.add(movement);
        }

        // Enforce bounds
        this.camera.position.x = Math.max(this.minX, Math.min(this.maxX, this.camera.position.x));
        this.camera.position.y = Math.max(this.minY, Math.min(this.maxY, this.camera.position.y));
        this.camera.position.z = Math.max(this.minZ, Math.min(this.maxZ, this.camera.position.z));
    }

    exit() {
        if (this.pointerLocked) {
            document.exitPointerLock?.();
        }
    }

    setBounds(minX, maxX, minY, maxY, minZ, maxZ) {
        this.minX = minX;
        this.maxX = maxX;
        this.minY = minY;
        this.maxY = maxY;
        this.minZ = minZ;
        this.maxZ = maxZ;
    }
}

export default ExploreControls;
