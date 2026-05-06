import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class ThirdPersonController {
    constructor(camera, canvas) {
        this.camera = camera;
        this.canvas = canvas;

        // Character (capsule-like mesh)
        this.character = new THREE.Group();
        this.characterMesh = this.createCharacterMesh();
        this.character.add(this.characterMesh);
        this.character.position.set(0, 1, 0);

        // Movement
        this.velocity = new THREE.Vector3();
        this.direction = new THREE.Vector3();
        this.keys = {};
        this.speed = 5;
        this.sprintSpeed = 10;
        this.isMoving = false;
        this.isSprinting = false;

        // Camera offset from character (behind and above)
        this.cameraOffset = new THREE.Vector3(0, 1.5, 3);
        this.cameraDistance = 3;
        this.cameraHeight = 1.5;

        // Rotation
        this.yaw = 0;
        this.characterRotation = 0;
        this.turnSpeed = 0.1;

        // Physics
        this.gravity = 20;
        this.isGrounded = false;
        this.groundY = 0;

        // Bounds
        this.minX = -50;
        this.maxX = 50;
        this.minZ = -50;
        this.maxZ = 50;

        this.setupEventListeners();
    }

    createCharacterMesh() {
        const group = new THREE.Group();

        // Head (sphere)
        const headGeometry = new THREE.SphereGeometry(0.25, 16, 16);
        const headMaterial = new THREE.MeshStandardMaterial({ color: 0xffdbac }); // skin color
        const head = new THREE.Mesh(headGeometry, headMaterial);
        head.position.y = 0.65;
        head.castShadow = true;
        head.receiveShadow = true;
        group.add(head);

        // Body (capsule)
        const bodyGeometry = new THREE.CapsuleGeometry(0.25, 0.8, 8, 8);
        const bodyMaterial = new THREE.MeshStandardMaterial({ color: 0x3b82f6 }); // blue shirt
        const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
        body.position.y = 0.3;
        body.castShadow = true;
        body.receiveShadow = true;
        group.add(body);

        // Legs (capsule)
        const legsGeometry = new THREE.CapsuleGeometry(0.2, 0.8, 8, 8);
        const legsMaterial = new THREE.MeshStandardMaterial({ color: 0x1e1e1e }); // black pants
        const legs = new THREE.Mesh(legsGeometry, legsMaterial);
        legs.position.y = -0.35;
        legs.castShadow = true;
        legs.receiveShadow = true;
        group.add(legs);

        return group;
    }

    setupEventListeners() {
        // Keyboard
        document.addEventListener('keydown', (e) => {
            this.keys[e.key.toLowerCase()] = true;
        });

        document.addEventListener('keyup', (e) => {
            this.keys[e.key.toLowerCase()] = false;
        });

        // Mouse look
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
                this.yaw -= e.movementX * 0.003;
            }
        });
    }

    update(deltaTime) {
        // Movement input
        this.direction.set(0, 0, 0);
        this.isMoving = false;
        this.isSprinting = this.keys['shift'];

        const speed = this.isSprinting ? this.sprintSpeed : this.speed;

        // Get forward/right based on yaw
        const forward = new THREE.Vector3(0, 0, -1).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.yaw);
        const right = new THREE.Vector3(1, 0, 0).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.yaw);

        if (this.keys['w']) {
            this.direction.add(forward);
            this.isMoving = true;
        }
        if (this.keys['s']) {
            this.direction.add(forward.multiplyScalar(-1));
            this.isMoving = true;
        }
        if (this.keys['a']) {
            this.direction.add(right.multiplyScalar(-1));
            this.isMoving = true;
        }
        if (this.keys['d']) {
            this.direction.add(right);
            this.isMoving = true;
        }

        // Normalize direction
        if (this.direction.length() > 0) {
            this.direction.normalize();
            this.velocity.x = this.direction.x * speed;
            this.velocity.z = this.direction.z * speed;

            // Smooth character rotation towards movement direction
            const targetRotation = Math.atan2(this.direction.x, this.direction.z);
            const diff = targetRotation - this.characterRotation;
            const shortestAngle = Math.atan2(Math.sin(diff), Math.cos(diff));
            this.characterRotation += shortestAngle * this.turnSpeed;
            this.characterMesh.rotation.y = this.characterRotation;
        } else {
            this.velocity.x *= 0.9; // Friction
            this.velocity.z *= 0.9;
        }

        // Apply gravity
        this.velocity.y -= this.gravity * deltaTime;

        // Update position
        this.character.position.x += this.velocity.x * deltaTime;
        this.character.position.y += this.velocity.y * deltaTime;
        this.character.position.z += this.velocity.z * deltaTime;

        // Ground detection and collision
        if (this.character.position.y <= this.groundY + 1) {
            this.character.position.y = this.groundY + 1;
            this.velocity.y = 0;
            this.isGrounded = true;

            // Jump
            if (this.keys[' ']) {
                this.velocity.y = 6;
                this.isGrounded = false;
            }
        } else {
            this.isGrounded = false;
        }

        // Enforce bounds
        this.character.position.x = Math.max(this.minX, Math.min(this.maxX, this.character.position.x));
        this.character.position.z = Math.max(this.minZ, Math.min(this.maxZ, this.character.position.z));

        // Update camera to follow character (TPP)
        this.updateCameraPosition();
    }

    updateCameraPosition() {
        // Calculate camera position based on yaw
        const cameraOffsetRotated = new THREE.Vector3(
            this.cameraOffset.x,
            this.cameraOffset.y,
            this.cameraOffset.z
        ).applyAxisAngle(new THREE.Vector3(0, 1, 0), this.yaw);

        const targetCameraPos = this.character.position.clone().add(cameraOffsetRotated);

        // Smooth camera movement
        this.camera.position.lerp(targetCameraPos, 0.1);

        // Look at character's head area
        const lookAtTarget = this.character.position.clone();
        lookAtTarget.y += 0.5;
        this.camera.lookAt(lookAtTarget);
    }

    setBounds(minX, maxX, minZ, maxZ, groundY = 0) {
        this.minX = minX;
        this.maxX = maxX;
        this.minZ = minZ;
        this.maxZ = maxZ;
        this.groundY = groundY;
        this.character.position.y = groundY + 1;
    }

    getCharacterMesh() {
        return this.character;
    }

    getCharacterPosition() {
        return this.character.position.clone();
    }

    setCharacterPosition(x, y, z) {
        this.character.position.set(x, y, z);
    }

    exitPointerLock() {
        document.exitPointerLock?.();
        this.pointerLocked = false;
    }
}

export default ThirdPersonController;
