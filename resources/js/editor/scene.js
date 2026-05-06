import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

export function createScene(container) {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1a1d27);
    scene.fog = new THREE.FogExp2(0x1a1d27, 0.015);

    const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(6, 8, 10);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.2;
    container.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.maxPolarAngle = Math.PI / 2.05;
    controls.minDistance = 2;
    controls.maxDistance = 30;

    // Lights
    const ambient = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambient);
    const dirLight = new THREE.DirectionalLight(0xffffff, 1.2);
    dirLight.position.set(8, 12, 6);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.set(2048, 2048);
    dirLight.shadow.camera.near = 0.5;
    dirLight.shadow.camera.far = 50;
    dirLight.shadow.camera.left = -15;
    dirLight.shadow.camera.right = 15;
    dirLight.shadow.camera.top = 15;
    dirLight.shadow.camera.bottom = -15;
    scene.add(dirLight);

    const fillLight = new THREE.DirectionalLight(0x8ecae6, 0.3);
    fillLight.position.set(-5, 6, -3);
    scene.add(fillLight);

    const hemi = new THREE.HemisphereLight(0xffffff, 0x444444, 0.4);
    scene.add(hemi);

    window.addEventListener('resize', () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });

    return { scene, camera, renderer, controls };
}

export function buildRoom(scene, w, l, h, wallColor, floorColor) {
    const group = new THREE.Group();
    group.name = 'room';

    // Floor
    const floorGeo = new THREE.PlaneGeometry(w, l);
    const floorMat = new THREE.MeshStandardMaterial({
        color: new THREE.Color(floorColor || '#c4a882'),
        roughness: 0.7, metalness: 0.05
    });
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.receiveShadow = true;
    floor.userData = { type: 'floor' };
    group.add(floor);

    // Grid
    const grid = new THREE.GridHelper(Math.max(w, l), Math.max(w, l) * 2, 0x333333, 0x222222);
    grid.position.y = 0.01;
    group.add(grid);

    // Walls
    const wc = new THREE.Color(wallColor || '#f5f0eb');
    const wallMat = new THREE.MeshStandardMaterial({
        color: wc, roughness: 0.85, metalness: 0, side: THREE.DoubleSide
    });
    const walls = [
        { pos: [0, h/2, -l/2], rot: [0, 0, 0], size: [w, h] },
        { pos: [0, h/2, l/2], rot: [0, Math.PI, 0], size: [w, h] },
        { pos: [-w/2, h/2, 0], rot: [0, Math.PI/2, 0], size: [l, h] },
        { pos: [w/2, h/2, 0], rot: [0, -Math.PI/2, 0], size: [l, h] },
    ];
    walls.forEach((wd, i) => {
        const geo = new THREE.PlaneGeometry(wd.size[0], wd.size[1]);
        const mat = wallMat.clone();
        const mesh = new THREE.Mesh(geo, mat);
        mesh.position.set(...wd.pos);
        mesh.rotation.set(...wd.rot);
        mesh.receiveShadow = true;
        mesh.userData = { type: 'wall', wallId: i };
        mesh.name = `wall_${i}`;
        group.add(mesh);
    });

    scene.add(group);
    return group;
}
