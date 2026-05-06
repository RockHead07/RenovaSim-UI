import * as THREE from 'three';

let fpCamera, moveState, euler, velocity;
let locked = false;

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
        euler.y -= e.movementX * 0.002;
        euler.x -= e.movementY * 0.002;
        euler.x = Math.max(-Math.PI / 2.2, Math.min(Math.PI / 2.2, euler.x));
        fpCamera.quaternion.setFromEuler(euler);
    });

    const onKey = (e, val) => {
        switch (e.code) {
            case 'KeyW': case 'ArrowUp': moveState.forward = val; break;
            case 'KeyS': case 'ArrowDown': moveState.backward = val; break;
            case 'KeyA': case 'ArrowLeft': moveState.left = val; break;
            case 'KeyD': case 'ArrowRight': moveState.right = val; break;
            case 'Space': if (val && fpCamera && fpCamera.position.y <= 1.71) velocity.y = 4.5; break;
        }
    };
    document.addEventListener('keydown', (e) => onKey(e, true));
    document.addEventListener('keyup', (e) => onKey(e, false));
}

export function updateExplore(delta) {
    if (!locked || !fpCamera) return;
    const speed = 5;
    
    // Apply Gravity
    velocity.y -= 15 * delta;
    
    // Horizontal movement vector
    const hVelocity = new THREE.Vector3();
    const dir = new THREE.Vector3();
    fpCamera.getWorldDirection(dir);
    dir.y = 0;
    dir.normalize();
    const side = new THREE.Vector3().crossVectors(fpCamera.up, dir).normalize();

    if (moveState.forward) hVelocity.add(dir.clone().multiplyScalar(speed));
    if (moveState.backward) hVelocity.add(dir.clone().multiplyScalar(-speed));
    if (moveState.left) hVelocity.add(side.clone().multiplyScalar(speed));
    if (moveState.right) hVelocity.add(side.clone().multiplyScalar(-speed));

    velocity.x = hVelocity.x;
    velocity.z = hVelocity.z;

    fpCamera.position.x += velocity.x * delta;
    fpCamera.position.z += velocity.z * delta;
    fpCamera.position.y += velocity.y * delta;

    // Floor collision
    if (fpCamera.position.y < 1.7) {
        fpCamera.position.y = 1.7;
        velocity.y = 0;
    }
}

export function enterExploreMode(camera) {
    camera.position.set(0, 1.7, 0);
    camera.rotation.set(0, 0, 0);
    initExplore(camera);
}

export function exitExploreMode() {
    if (document.pointerLockElement) document.exitPointerLock();
    locked = false;
    moveState = { forward: false, backward: false, left: false, right: false };
}
