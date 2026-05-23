import * as THREE from 'three';

let fpCamera, moveState, euler, velocity;
let locked = false;

// Eye-level height: lower for a more realistic seating/standing feel (~1.45m)
const EYE_HEIGHT = 1.45;
const JUMP_VELOCITY = 3.8;
const GRAVITY = 12;
const OBJ_GRAVITY = 9.8;
const MOVE_SPEED = 3.5;
const MOUSE_SENSITIVITY = 0.0018;

// Wall-mounted items are exempt from gravity
const WALL_MOUNTED_TYPES = ['mirror', 'painting', 'clock', 'curtain'];

// Per-object vertical velocity for smooth physics
const objVelocities = new Map();

export function initExplore(camera) {
    fpCamera = camera;
    moveState = { forward: false, backward: false, left: false, right: false };
    euler = new THREE.Euler(0, 0, 0, 'YXZ');
    velocity = new THREE.Vector3();
    objVelocities.clear();
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

/**
 * Apply gravity and AABB stacking collision to all scene objects.
 * Objects fall until they hit the floor (Y=0) or land on top of another object.
 * Wall-mounted items (mirror, painting, clock, curtain) and rugs are exempt.
 *
 * @param {Array} objects - all furniture wrapper Groups from objectList
 * @param {THREE.Object3D|null} heldObject - currently held object (skip gravity)
 * @param {number} delta - frame delta time in seconds
 */
export function applyGravityToObjects(objects, heldObject, delta) {
    for (const obj of objects) {
        // Skip the object the player is currently holding
        if (obj === heldObject) continue;

        const ft = obj.userData.furnitureType || '';
        // Skip wall-mounted types — they stick to walls
        if (WALL_MOUNTED_TYPES.some(t => ft.includes(t))) continue;
        // Skip partition walls and structural walls — they are static
        if (ft === 'partition_wall' || obj.userData.type === 'wall') continue;

        const s = obj.userData.scale || [1, 1, 1];

        // Get or initialise vertical velocity for this object
        if (!objVelocities.has(obj.uuid)) {
            objVelocities.set(obj.uuid, 0);
        }
        let vy = objVelocities.get(obj.uuid);

        // ── Find the highest surface below this object (floor or another object) ──
        let landingY = 0; // floor baseline

        for (const other of objects) {
            if (other === obj || other === heldObject) continue;
            const os = other.userData.scale || [1, 1, 1];
            const oft = other.userData.furnitureType || '';
            // Ignore rugs as landing surfaces
            if (oft.includes('rug') || oft.includes('carpet')) continue;

            // Horizontal AABB overlap test (slightly shrunk to avoid edge-sticking)
            const overlapX = Math.abs(obj.position.x - other.position.x) < (s[0] + os[0]) * 0.4;
            const overlapZ = Math.abs(obj.position.z - other.position.z) < (s[2] + os[2]) * 0.4;

            if (overlapX && overlapZ) {
                const otherTop = other.position.y + os[1];
                // Only consider surfaces that are at or below our current position
                if (otherTop <= obj.position.y + 0.02 && otherTop > landingY) {
                    landingY = otherTop;
                }
            }
        }

        // ── Apply gravity ──
        vy -= OBJ_GRAVITY * delta;
        obj.position.y += vy * delta;

        // ── Floor / stack collision ──
        if (obj.position.y <= landingY) {
            obj.position.y = landingY;
            vy = 0;
        }

        objVelocities.set(obj.uuid, vy);
    }
}
