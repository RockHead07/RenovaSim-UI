import * as THREE from 'https://cdn.jsdelivr.net/npm/three@r128/build/three.module.js';

export class OpenWorldGenerator {
    constructor(scene) {
        this.scene = scene;
        this.gridSize = 10; // 10x10 blocks
        this.blockSize = 5; // Each block is 5x5 units
        this.totalSize = this.gridSize * this.blockSize; // Total world size
        this.chunks = new Map(); // For chunk management
        this.seed = Math.random() * 1000;
    }

    /**
     * Create infinite-like ground with infinite loop
     */
    generateInfiniteGround() {
        // Create main plane that covers the visible area
        const geometry = new THREE.PlaneGeometry(this.totalSize * 2, this.totalSize * 2);
        const material = new THREE.MeshStandardMaterial({
            color: 0x7cb342, // grass green
            metalness: 0.0,
            roughness: 0.9,
        });
        const ground = new THREE.Mesh(geometry, material);
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = 0;
        ground.receiveShadow = true;
        ground.userData.isFloor = true;

        this.scene.add(ground);

        // Add grid lines for visual reference
        this.addGridLines();

        return ground;
    }

    /**
     * Add grid visualization
     */
    addGridLines() {
        const gridSize = this.totalSize;
        const gridDivisions = this.gridSize;
        const gridHelper = new THREE.GridHelper(gridSize, gridDivisions, 0x444444, 0x888888);
        gridHelper.position.y = 0.01;
        this.scene.add(gridHelper);
    }

    /**
     * Generate procedural city buildings like Infinitown
     */
    generateProceduralCity(centerX = 0, centerZ = 0, radius = 40) {
        const buildings = [];
        const blockSpacing = this.blockSize;

        // Generate buildings in a grid pattern
        for (let x = centerX - radius; x < centerX + radius; x += blockSpacing) {
            for (let z = centerZ - radius; z < centerZ + radius; z += blockSpacing) {
                // Use seed for consistent generation
                const seed = Math.sin(x * 12.9898 + z * 78.233 + this.seed) * 43758.5453;
                const random = seed - Math.floor(seed);

                // 60% chance to place building
                if (random > 0.4) {
                    const building = this.generateRandomBuilding(x, z, random);
                    buildings.push(building);
                    this.scene.add(building);
                }
            }
        }

        return buildings;
    }

    /**
     * Generate a random building
     */
    generateRandomBuilding(x, z, seed) {
        const group = new THREE.Group();

        // Building dimensions
        const width = 3 + Math.sin(seed * 100) * 1.5;
        const depth = 3 + Math.cos(seed * 150) * 1.5;
        const height = 3 + Math.sin(seed * 200) * 4; // 3-7 units tall

        // Building body
        const buildingGeometry = new THREE.BoxGeometry(width, height, depth);
        const colors = [0xff6b6b, 0x4ecdc4, 0xffe66d, 0x95e1d3, 0xc7b3e5, 0xa8d8ea];
        const colorIndex = Math.floor(seed * colors.length);
        const buildingMaterial = new THREE.MeshStandardMaterial({
            color: colors[colorIndex % colors.length],
            metalness: 0.1,
            roughness: 0.7,
        });
        const building = new THREE.Mesh(buildingGeometry, buildingMaterial);
        building.position.set(x, height / 2, z);
        building.castShadow = true;
        building.receiveShadow = true;
        group.add(building);

        // Add windows
        this.addWindowsToBuilding(building, width, depth, height, seed);

        // Add roof
        const roofGeometry = new THREE.ConeGeometry(
            Math.max(width, depth) / 1.5,
            height * 0.3,
            4
        );
        const roofMaterial = new THREE.MeshStandardMaterial({
            color: 0x8b4513,
            metalness: 0.2,
            roughness: 0.8,
        });
        const roof = new THREE.Mesh(roofGeometry, roofMaterial);
        roof.position.set(0, height / 2 + height * 0.15, 0);
        roof.castShadow = true;
        roof.receiveShadow = true;
        group.add(roof);

        // Add door
        const doorGeometry = new THREE.BoxGeometry(0.8, 1.8, 0.1);
        const doorMaterial = new THREE.MeshStandardMaterial({ color: 0x8b4513 });
        const door = new THREE.Mesh(doorGeometry, doorMaterial);
        door.position.set(0, 0.9, depth / 2 + 0.05);
        door.castShadow = true;
        group.add(door);

        group.position.set(x, 0, z);
        return group;
    }

    /**
     * Add windows to building
     */
    addWindowsToBuilding(building, width, depth, height, seed) {
        const windowSize = 0.4;
        const spacing = 1.2;
        const parent = building.parent || this.scene;

        // Front windows
        for (let y = 1; y < height - 1; y += spacing) {
            for (let x = -width / 2 + 0.5; x < width / 2; x += spacing) {
                const windowGeometry = new THREE.BoxGeometry(windowSize, windowSize, 0.1);
                const windowMaterial = new THREE.MeshStandardMaterial({
                    color: 0x87ceeb,
                    metalness: 0.8,
                    roughness: 0.2,
                });
                const window = new THREE.Mesh(windowGeometry, windowMaterial);
                window.position.set(x, y, depth / 2 + 0.05);
                building.add(window);
            }
        }
    }

    /**
     * Generate trees around city
     */
    generateTrees(centerX = 0, centerZ = 0, count = 50) {
        const trees = [];

        for (let i = 0; i < count; i++) {
            const angle = (i / count) * Math.PI * 2;
            const radius = 25 + Math.random() * 15;
            const x = centerX + Math.cos(angle) * radius;
            const z = centerZ + Math.sin(angle) * radius;

            const tree = this.createTree(x, z);
            trees.push(tree);
            this.scene.add(tree);
        }

        return trees;
    }

    /**
     * Create a single tree
     */
    createTree(x, z) {
        const group = new THREE.Group();

        // Trunk
        const trunkGeometry = new THREE.CylinderGeometry(0.3, 0.4, 2, 8);
        const trunkMaterial = new THREE.MeshStandardMaterial({
            color: 0x8b6914,
            metalness: 0.0,
            roughness: 0.9,
        });
        const trunk = new THREE.Mesh(trunkGeometry, trunkMaterial);
        trunk.position.y = 1;
        trunk.castShadow = true;
        trunk.receiveShadow = true;
        group.add(trunk);

        // Foliage
        const foliageGeometry = new THREE.SphereGeometry(1.2, 8, 8);
        const foliageMaterial = new THREE.MeshStandardMaterial({
            color: 0x228b22,
            metalness: 0.0,
            roughness: 0.8,
        });
        const foliage = new THREE.Mesh(foliageGeometry, foliageMaterial);
        foliage.position.y = 2.5;
        foliage.castShadow = true;
        foliage.receiveShadow = true;
        group.add(foliage);

        group.position.set(x, 0, z);
        return group;
    }

    /**
     * Generate roads in a grid pattern
     */
    generateRoads(centerX = 0, centerZ = 0, radius = 40) {
        const blockSpacing = this.blockSize;
        const roadWidth = 1.5;
        const roadMaterial = new THREE.MeshStandardMaterial({
            color: 0x333333,
            metalness: 0.3,
            roughness: 0.7,
        });

        const roads = [];

        // Horizontal roads
        for (let x = centerX - radius; x < centerX + radius; x += blockSpacing * 2) {
            const roadGeometry = new THREE.PlaneGeometry(radius * 2, roadWidth);
            const road = new THREE.Mesh(roadGeometry, roadMaterial);
            road.rotation.x = -Math.PI / 2;
            road.position.set(centerX, 0.01, x);
            road.receiveShadow = true;
            this.scene.add(road);
            roads.push(road);
        }

        // Vertical roads
        for (let z = centerZ - radius; z < centerZ + radius; z += blockSpacing * 2) {
            const roadGeometry = new THREE.PlaneGeometry(roadWidth, radius * 2);
            const road = new THREE.Mesh(roadGeometry, roadMaterial);
            road.rotation.x = -Math.PI / 2;
            road.position.set(z, 0.01, centerZ);
            road.receiveShadow = true;
            this.scene.add(road);
            roads.push(road);
        }

        return roads;
    }

    /**
     * Clear all generated objects
     */
    clear() {
        this.chunks.forEach((chunk) => {
            this.scene.remove(chunk);
        });
        this.chunks.clear();
    }
}

export default OpenWorldGenerator;
