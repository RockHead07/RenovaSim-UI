import * as THREE from 'three';

let fpCamera, moveState, euler, velocity;
let locked = false;

// Eye-level height: lowered per user request
const EYE_HEIGHT = 1.1;
const JUMP_VELOCITY = 3.8;
const GRAVITY = 12;
const MOVE_SPEED = 4.2;
const MOUSE_SENSITIVITY = 0.002;

export function initExplore(camera) {
    fpCamera = camera;
    moveState = { forward: false, backward: false, left: false, right: false };
    euler = new THREE.Euler(0, 0, 0, 'YXZ');
    velocity = new THREE.Vector3();
}

export function lockPointer(canvas) {
    canvas.requestPointerLock();
}

export function isLocked() { return locked; }

export function setupExploreEvents(canvas) {
    document.addEventListener('pointerlockchange', () => {
        locked = document.pointerLockElement === canvas;
    });

    document.addEventListener('mousemove', (e) => {
        if (!locked) return;
        euler.setFromQuaternion(fpCamera.quaternion);
        euler.y -= e.movementX * MOUSE_SENSITIVITY;
        euler.x -= e.movementY * MOUSE_SENSITIVITY;
        euler.x = Math.max(-Math.PI / 2.2, Math.min(Math.PI / 2.2, euler.x));
        fpCamera.quaternion.setFromEuler(euler);
    });

    const onKey = (e, val) => {
        switch (e.code) {
            case 'KeyW': case 'ArrowUp': moveState.forward = val; break;
            case 'KeyS': case 'ArrowDown': moveState.backward = val; break;
            case 'KeyA': case 'ArrowLeft': moveState.left = val; break;
            case 'KeyD': case 'ArrowRight': moveState.right = val; break;
            case 'Space': if (val && fpCamera && fpCamera.position.y <= EYE_HEIGHT + 0.01) velocity.y = JUMP_VELOCITY; break;
        }
    };
    document.addEventListener('keydown', (e) => onKey(e, true));
    document.addEventListener('keyup', (e) => onKey(e, false));
}

export function updateExplore(delta) {
    if (!locked || !fpCamera) return;
    
    // Apply Gravity
    velocity.y -= GRAVITY * delta;
    
    // Horizontal movement vector
    const hVelocity = new THREE.Vector3();
    const dir = new THREE.Vector3();
    fpCamera.getWorldDirection(dir);
    dir.y = 0;
    dir.normalize();
    const side = new THREE.Vector3().crossVectors(fpCamera.up, dir).normalize();

    if (moveState.forward) hVelocity.add(dir.clone().multiplyScalar(MOVE_SPEED));
    if (moveState.backward) hVelocity.add(dir.clone().multiplyScalar(-MOVE_SPEED));
    if (moveState.left) hVelocity.add(side.clone().multiplyScalar(MOVE_SPEED));
    if (moveState.right) hVelocity.add(side.clone().multiplyScalar(-MOVE_SPEED));

    velocity.x = hVelocity.x;
    velocity.z = hVelocity.z;

    fpCamera.position.x += velocity.x * delta;
    fpCamera.position.z += velocity.z * delta;
    fpCamera.position.y += velocity.y * delta;

    // Floor collision — eye height
    if (fpCamera.position.y < EYE_HEIGHT) {
        fpCamera.position.y = EYE_HEIGHT;
        velocity.y = 0;
    }
}

export function enterExploreMode(camera) {
    camera.position.set(0, EYE_HEIGHT, 0);
    camera.rotation.set(0, 0, 0);
    initExplore(camera);
}

export function exitExploreMode() {
    if (document.pointerLockElement) document.exitPointerLock();
    locked = false;
    moveState = { forward: false, backward: false, left: false, right: false };
}
