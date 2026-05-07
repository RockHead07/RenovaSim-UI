import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

// Procedural wood texture
function createWoodTexture() {
    const c = document.createElement('canvas'); c.width = 512; c.height = 512;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#c4a882';
    ctx.fillRect(0, 0, 512, 512);
    for (let i = 0; i < 80; i++) {
        const y = Math.random() * 512;
        ctx.strokeStyle = `rgba(${140+Math.random()*40},${120+Math.random()*30},${80+Math.random()*20},${0.15+Math.random()*0.15})`;
        ctx.lineWidth = 1 + Math.random() * 3;
        ctx.beginPath(); ctx.moveTo(0, y + Math.random()*6);
        for (let x = 0; x < 512; x += 20) ctx.lineTo(x, y + Math.sin(x*0.02)*4 + Math.random()*2);
        ctx.stroke();
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(3, 3);
    return tex;
}

// Procedural wall texture
function createWallTexture(color) {
    const c = document.createElement('canvas'); c.width = 256; c.height = 256;
    const ctx = c.getContext('2d');
    ctx.fillStyle = color || '#f5f0eb';
    ctx.fillRect(0, 0, 256, 256);
    for (let i = 0; i < 2000; i++) {
        const x = Math.random()*256, y = Math.random()*256;
        ctx.fillStyle = `rgba(${Math.random()>0.5?255:200},${Math.random()>0.5?255:200},${Math.random()>0.5?255:200},0.03)`;
        ctx.fillRect(x, y, 1+Math.random()*2, 1+Math.random()*2);
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(2, 2);
    return tex;
}

export function createScene(container) {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1a1d27);
    scene.fog = new THREE.FogExp2(0x1a1d27, 0.012);

    const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(6, 8, 10);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.3;
    container.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.maxPolarAngle = Math.PI / 2.05;
    controls.minDistance = 2;
    controls.maxDistance = 30;

    // Lights — more realistic warm/cool balance
    const ambient = new THREE.AmbientLight(0xfff5e6, 0.4);
    scene.add(ambient);
    const dirLight = new THREE.DirectionalLight(0xfff8f0, 1.0);
    dirLight.position.set(8, 12, 6);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.set(2048, 2048);
    dirLight.shadow.camera.near = 0.5;
    dirLight.shadow.camera.far = 50;
    dirLight.shadow.camera.left = -15;
    dirLight.shadow.camera.right = 15;
    dirLight.shadow.camera.top = 15;
    dirLight.shadow.camera.bottom = -15;
    dirLight.shadow.bias = -0.0005;
    scene.add(dirLight);

    const fillLight = new THREE.DirectionalLight(0x8ecae6, 0.25);
    fillLight.position.set(-5, 6, -3);
    scene.add(fillLight);

    const hemi = new THREE.HemisphereLight(0xffffff, 0x8d7b6a, 0.35);
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

    // Floor with wood texture
    const floorTex = createWoodTexture();
    const floorGeo = new THREE.PlaneGeometry(w, l);
    const floorMat = new THREE.MeshStandardMaterial({
        map: floorTex,
        color: new THREE.Color(floorColor || '#c4a882'),
        roughness: 0.65, metalness: 0.05
    });
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.receiveShadow = true;
    floor.userData = { type: 'floor' };
    group.add(floor);

    // Baseboard trim around floor edges
    const bbMat = new THREE.MeshStandardMaterial({ color: 0xf0ebe5, roughness: 0.6 });
    const bbH = 0.08, bbD = 0.02;
    [[w, bbH, bbD, 0, bbH/2, -l/2+bbD/2, 0],
     [w, bbH, bbD, 0, bbH/2, l/2-bbD/2, 0],
     [bbD, bbH, l, -w/2+bbD/2, bbH/2, 0, 0],
     [bbD, bbH, l, w/2-bbD/2, bbH/2, 0, 0]].forEach(([bw,bh,bd,bx,by,bz]) => {
        const bb = new THREE.Mesh(new THREE.BoxGeometry(bw,bh,bd), bbMat);
        bb.position.set(bx,by,bz); bb.receiveShadow = true;
        group.add(bb);
    });

    // Subtle grid
    const grid = new THREE.GridHelper(Math.max(w, l), Math.max(w, l) * 2, 0x333333, 0x222222);
    grid.position.y = 0.01;
    grid.material.opacity = 0.3;
    grid.material.transparent = true;
    group.add(grid);

    // Walls with texture
    const wc = new THREE.Color(wallColor || '#f5f0eb');
    const wallTex = createWallTexture(wallColor || '#f5f0eb');
    const wallMat = new THREE.MeshStandardMaterial({
        map: wallTex, color: wc, roughness: 0.85, metalness: 0, side: THREE.DoubleSide
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

    // Ceiling (subtle)
    const ceilGeo = new THREE.PlaneGeometry(w, l);
    const ceilMat = new THREE.MeshStandardMaterial({ color: 0xfaf8f5, roughness: 0.95, side: THREE.DoubleSide });
    const ceil = new THREE.Mesh(ceilGeo, ceilMat);
    ceil.rotation.x = Math.PI / 2;
    ceil.position.y = h;
    group.add(ceil);

    scene.add(group);
    return group;
}
