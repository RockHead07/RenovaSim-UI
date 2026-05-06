import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class InteriorSceneManager {
    constructor(scene) {
        this.scene = scene;
        this.currentRoom = null;
        this.rooms = new Map();
    }

    /**
     * Create a room interior
     */
    createRoom(roomId, name, width = 4, length = 5, height = 3) {
        const room = {
            id: roomId,
            name: name,
            width: width,
            length: length,
            height: height,
            group: new THREE.Group(),
            objects: [],
        };

        // Create room geometry
        this.setupRoomGeometry(room);

        this.rooms.set(roomId, room);
        return room;
    }

    /**
     * Setup room walls, floor, ceiling
     */
    setupRoomGeometry(room) {
        const { width, length, height, group } = room;

        // Materials
        const floorMaterial = new THREE.MeshStandardMaterial({
            color: 0xd4d4d8,
            metalness: 0.1,
            roughness: 0.8,
        });

        const wallMaterial = new THREE.MeshStandardMaterial({
            color: 0xf1f5f9,
            metalness: 0.0,
            roughness: 0.9,
        });

        // Floor
        const floorGeometry = new THREE.PlaneGeometry(width, length);
        const floor = new THREE.Mesh(floorGeometry, floorMaterial);
        floor.rotation.x = -Math.PI / 2;
        floor.receiveShadow = true;
        floor.userData.isFloor = true;
        group.add(floor);

        // Ceiling
        const ceilingGeometry = new THREE.PlaneGeometry(width, length);
        const ceiling = new THREE.Mesh(ceilingGeometry, wallMaterial);
        ceiling.rotation.x = Math.PI / 2;
        ceiling.position.y = height;
        ceiling.receiveShadow = true;
        group.add(ceiling);

        // Front wall
        const frontWallGeometry = new THREE.PlaneGeometry(width, height);
        const frontWall = new THREE.Mesh(frontWallGeometry, wallMaterial);
        frontWall.position.set(0, height / 2, length / 2);
        frontWall.receiveShadow = true;
        group.add(frontWall);

        // Back wall
        const backWallGeometry = new THREE.PlaneGeometry(width, height);
        const backWall = new THREE.Mesh(backWallGeometry, wallMaterial);
        backWall.rotation.y = Math.PI;
        backWall.position.set(0, height / 2, -length / 2);
        backWall.receiveShadow = true;
        group.add(backWall);

        // Left wall
        const leftWallGeometry = new THREE.PlaneGeometry(length, height);
        const leftWall = new THREE.Mesh(leftWallGeometry, wallMaterial);
        leftWall.rotation.y = Math.PI / 2;
        leftWall.position.set(-width / 2, height / 2, 0);
        leftWall.receiveShadow = true;
        group.add(leftWall);

        // Right wall
        const rightWallGeometry = new THREE.PlaneGeometry(length, height);
        const rightWall = new THREE.Mesh(rightWallGeometry, wallMaterial);
        rightWall.rotation.y = -Math.PI / 2;
        rightWall.position.set(width / 2, height / 2, 0);
        rightWall.receiveShadow = true;
        group.add(rightWall);

        group.position.set(0, 0, 0);
        this.scene.add(group);
    }

    /**
     * Enter a room
     */
    enterRoom(roomId) {
        const room = this.rooms.get(roomId);
        if (!room) {
            console.error(`Room not found: ${roomId}`);
            return null;
        }

        // Hide previous room
        if (this.currentRoom) {
            this.currentRoom.group.visible = false;
        }

        // Show new room
        room.group.visible = true;
        this.currentRoom = room;

        console.log(`[InteriorSceneManager] Entered room: ${room.name}`);
        return room;
    }

    /**
     * Exit current room
     */
    exitRoom() {
        if (this.currentRoom) {
            this.currentRoom.group.visible = false;
            this.currentRoom = null;
        }
    }

    /**
     * Get current room
     */
    getCurrentRoom() {
        return this.currentRoom;
    }

    /**
     * Get room by ID
     */
    getRoom(roomId) {
        return this.rooms.get(roomId);
    }

    /**
     * Add object to current room
     */
    addObjectToRoom(object) {
        if (!this.currentRoom) {
            console.warn('No room is currently active');
            return null;
        }

        this.currentRoom.group.add(object);
        this.currentRoom.objects.push(object);
        return object;
    }

    /**
     * Remove object from current room
     */
    removeObjectFromRoom(object) {
        if (!this.currentRoom) return;

        this.currentRoom.group.remove(object);
        this.currentRoom.objects = this.currentRoom.objects.filter((o) => o !== object);
    }

    /**
     * Get all objects in current room
     */
    getRoomObjects() {
        if (!this.currentRoom) return [];
        return this.currentRoom.objects;
    }

    /**
     * Delete room
     */
    deleteRoom(roomId) {
        const room = this.rooms.get(roomId);
        if (!room) return;

        this.scene.remove(room.group);
        this.rooms.delete(roomId);

        if (this.currentRoom?.id === roomId) {
            this.currentRoom = null;
        }
    }

    /**
     * Get all rooms
     */
    getAllRooms() {
        return Array.from(this.rooms.values());
    }

    /**
     * Clear all rooms
     */
    clearAll() {
        this.rooms.forEach((room) => {
            this.scene.remove(room.group);
        });
        this.rooms.clear();
        this.currentRoom = null;
    }
}

export default InteriorSceneManager;
