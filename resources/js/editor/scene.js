import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

// ── High-quality procedural wood floor texture ──
function createWoodTexture() {
    const c = document.createElement('canvas'); c.width = 1024; c.height = 1024;
    const ctx = c.getContext('2d');
    
    // Base warm wood color
    ctx.fillStyle = '#b8956a';
    ctx.fillRect(0, 0, 1024, 1024);
    
    // Plank layout - realistic hardwood planks
    const plankH = 128;
    for (let row = 0; row < 8; row++) {
        const y = row * plankH;
        // Alternate plank offsets for realistic pattern
        const offset = (row % 2) * 256;
        
        // Individual plank color variation
        for (let col = -1; col < 5; col++) {
            const x = col * 512 + offset;
            const brightness = 0.92 + Math.random() * 0.16;
            const r = Math.floor(184 * brightness);
            const g = Math.floor(149 * brightness);
            const b = Math.floor(106 * brightness);
            ctx.fillStyle = `rgb(${r},${g},${b})`;
            ctx.fillRect(x + 2, y + 1, 508, plankH - 2);
        }
        
        // Plank gap lines
        ctx.strokeStyle = 'rgba(80, 55, 30, 0.5)';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(1024, y);
        ctx.stroke();
        
        // Vertical gaps (staggered)
        for (let col = 0; col < 3; col++) {
            const x = col * 512 + offset;
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x, y + plankH);
            ctx.stroke();
        }
    }
    
    // Wood grain lines
    for (let i = 0; i < 200; i++) {
        const y = Math.random() * 1024;
        const plankRow = Math.floor(y / plankH);
        const startX = (plankRow % 2) * 256;
        ctx.strokeStyle = `rgba(${100 + Math.random() * 60},${80 + Math.random() * 40},${40 + Math.random() * 30},${0.06 + Math.random() * 0.08})`;
        ctx.lineWidth = 0.5 + Math.random() * 1.5;
        ctx.beginPath();
        ctx.moveTo(startX, y + Math.random() * 4);
        for (let x = startX; x < startX + 520; x += 15) {
            ctx.lineTo(x, y + Math.sin(x * 0.015) * 2.5 + Math.random() * 1.5);
        }
        ctx.stroke();
    }
    
    // Knots (occasional)
    for (let i = 0; i < 5; i++) {
        const kx = Math.random() * 1024;
        const ky = Math.random() * 1024;
        const kr = 4 + Math.random() * 8;
        const grad = ctx.createRadialGradient(kx, ky, 0, kx, ky, kr);
        grad.addColorStop(0, 'rgba(90, 60, 30, 0.4)');
        grad.addColorStop(0.5, 'rgba(110, 75, 40, 0.2)');
        grad.addColorStop(1, 'rgba(140, 100, 60, 0)');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(kx, ky, kr, 0, Math.PI * 2);
        ctx.fill();
    }
    
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(2, 2);
    tex.anisotropy = 8;
    return tex;
}

// ── High-quality procedural wall texture ──
export function createWallTexture(color) {
    const c = document.createElement('canvas'); c.width = 512; c.height = 512;
    const ctx = c.getContext('2d');
    
    // Parse the input color to RGB
    const tempDiv = document.createElement('div');
    tempDiv.style.color = color || '#f5f0eb';
    document.body.appendChild(tempDiv);
    const computed = getComputedStyle(tempDiv).color;
    document.body.removeChild(tempDiv);
    
    ctx.fillStyle = color || '#f5f0eb';
    ctx.fillRect(0, 0, 512, 512);
    
    // Subtle plaster/paint texture noise
    for (let i = 0; i < 8000; i++) {
        const x = Math.random() * 512, y = Math.random() * 512;
        const brightness = Math.random() > 0.5 ? 255 : 200;
        ctx.fillStyle = `rgba(${brightness},${brightness},${brightness},0.015)`;
        ctx.fillRect(x, y, 1 + Math.random() * 2, 1 + Math.random() * 2);
    }
    
    // Very subtle vertical brush strokes (paint roller effect)
    for (let i = 0; i < 30; i++) {
        const x = Math.random() * 512;
        ctx.strokeStyle = `rgba(255,255,255,0.02)`;
        ctx.lineWidth = 8 + Math.random() * 16;
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x + Math.random() * 8 - 4, 512);
        ctx.stroke();
    }
    
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(1.5, 1.5);
    tex.anisotropy = 4;
    return tex;
}

// ── Ceiling texture ──
function createCeilingTexture() {
    const c = document.createElement('canvas'); c.width = 256; c.height = 256;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#faf8f5';
    ctx.fillRect(0, 0, 256, 256);
    for (let i = 0; i < 3000; i++) {
        const x = Math.random() * 256, y = Math.random() * 256;
        ctx.fillStyle = `rgba(255,255,255,0.02)`;
        ctx.fillRect(x, y, 1, 1);
    }
    const tex = new THREE.CanvasTexture(c);
    tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
    tex.repeat.set(2, 2);
    return tex;
}

export function createScene(container) {
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1a1d27);
    scene.fog = new THREE.FogExp2(0x1a1d27, 0.008);

    const camera = new THREE.PerspectiveCamera(55, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(6, 8, 10);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.2;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    container.appendChild(renderer.domElement);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.maxPolarAngle = Math.PI / 2.05;
    controls.minDistance = 2;
    controls.maxDistance = 30;

    // ── Lighting Setup — Realistic interior warm/cool balance ──
    
    // Soft ambient (simulates bounced light)
    const ambient = new THREE.AmbientLight(0xfff5e6, 0.35);
    scene.add(ambient);
    
    // Main sun/window light
    const dirLight = new THREE.DirectionalLight(0xfff8f0, 0.9);
    dirLight.position.set(8, 14, 6);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.set(2048, 2048);
    dirLight.shadow.camera.near = 0.5;
    dirLight.shadow.camera.far = 50;
    dirLight.shadow.camera.left = -15;
    dirLight.shadow.camera.right = 15;
    dirLight.shadow.camera.top = 15;
    dirLight.shadow.camera.bottom = -15;
    dirLight.shadow.bias = -0.0003;
    dirLight.shadow.normalBias = 0.02;
    scene.add(dirLight);

    // Cool fill light (from opposite side, simulates sky bounce)
    const fillLight = new THREE.DirectionalLight(0x8ecae6, 0.2);
    fillLight.position.set(-5, 6, -3);
    scene.add(fillLight);

    // Warm back fill (simulates interior bounce from floor)
    const backFill = new THREE.DirectionalLight(0xffe0c0, 0.15);
    backFill.position.set(0, 1, -8);
    scene.add(backFill);

    // Hemisphere sky/ground
    const hemi = new THREE.HemisphereLight(0xf0f0ff, 0x8d7b6a, 0.3);
    scene.add(hemi);
    
    // Subtle point light inside the room (simulates ceiling light)
    const roomLight = new THREE.PointLight(0xfff0e0, 0.4, 15, 1.5);
    roomLight.position.set(0, 2.8, 0);
    roomLight.castShadow = false;
    scene.add(roomLight);

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

    // ── Floor with realistic wood texture ──
    const floorTex = createWoodTexture();
    const floorGeo = new THREE.PlaneGeometry(w, l);
    const floorMat = new THREE.MeshStandardMaterial({
        map: floorTex,
        color: new THREE.Color(floorColor || '#c4a882'),
        roughness: 0.55, metalness: 0.02,
        envMapIntensity: 0.3
    });
    const floor = new THREE.Mesh(floorGeo, floorMat);
    floor.rotation.x = -Math.PI / 2;
    floor.receiveShadow = true;
    floor.name = 'floor';
    floor.userData = { type: 'floor' };
    group.add(floor);

    // ── Baseboard trim (realistic profile) ──
    const bbMat = new THREE.MeshStandardMaterial({ color: 0xf0ebe5, roughness: 0.5, metalness: 0.02 });
    const bbH = 0.1, bbD = 0.025;
    [[w, bbH, bbD, 0, bbH / 2, -l / 2 + bbD / 2, 0],
     [w, bbH, bbD, 0, bbH / 2, l / 2 - bbD / 2, 0],
     [bbD, bbH, l, -w / 2 + bbD / 2, bbH / 2, 0, 0],
     [bbD, bbH, l, w / 2 - bbD / 2, bbH / 2, 0, 0]].forEach(([bw, bh, bd, bx, by, bz]) => {
        const bb = new THREE.Mesh(new THREE.BoxGeometry(bw, bh, bd), bbMat);
        bb.position.set(bx, by, bz);
        bb.receiveShadow = true;
        bb.castShadow = true;
        group.add(bb);
    });

    // ── Crown molding at ceiling ──
    const crownMat = new THREE.MeshStandardMaterial({ color: 0xf5f0eb, roughness: 0.5 });
    const cmH = 0.06, cmD = 0.04;
    [[w + 0.04, cmH, cmD, 0, h - cmH / 2, -l / 2 + cmD / 2],
     [w + 0.04, cmH, cmD, 0, h - cmH / 2, l / 2 - cmD / 2],
     [cmD, cmH, l + 0.04, -w / 2 + cmD / 2, h - cmH / 2, 0],
     [cmD, cmH, l + 0.04, w / 2 - cmD / 2, h - cmH / 2, 0]].forEach(([cw, ch, cd, cx, cy, cz]) => {
        const cm = new THREE.Mesh(new THREE.BoxGeometry(cw, ch, cd), crownMat);
        cm.position.set(cx, cy, cz);
        group.add(cm);
    });

    // ── Subtle floor grid (editor aid) ──
    const grid = new THREE.GridHelper(Math.max(w, l), Math.max(w, l) * 2, 0x333333, 0x222222);
    grid.position.y = 0.005;
    grid.material.opacity = 0.15;
    grid.material.transparent = true;
    group.add(grid);

    // ── Walls with realistic plaster texture ──
    const wc = new THREE.Color(wallColor || '#f5f0eb');
    const wallTex = createWallTexture(wallColor || '#f5f0eb');
    const wallMat = new THREE.MeshStandardMaterial({
        map: wallTex, color: wc, roughness: 0.82, metalness: 0, side: THREE.FrontSide,
        envMapIntensity: 0.15
    });
    const walls = [
        { pos: [0, h / 2, -l / 2], rot: [0, 0, 0], size: [w, h] },
        { pos: [0, h / 2, l / 2], rot: [0, Math.PI, 0], size: [w, h] },
        { pos: [-w / 2, h / 2, 0], rot: [0, Math.PI / 2, 0], size: [l, h] },
        { pos: [w / 2, h / 2, 0], rot: [0, -Math.PI / 2, 0], size: [l, h] },
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

    // ── Ceiling with subtle texture ──
    const ceilTex = createCeilingTexture();
    const ceilGeo = new THREE.PlaneGeometry(w, l);
    const ceilMat = new THREE.MeshStandardMaterial({
        map: ceilTex, color: 0xfaf8f5, roughness: 0.95, side: THREE.FrontSide
    });
    const ceil = new THREE.Mesh(ceilGeo, ceilMat);
    ceil.rotation.x = Math.PI / 2;
    ceil.position.y = h;
    ceil.name = 'ceiling';
    group.add(ceil);

    // ── Ceiling light fixture (simple recessed light disc) ──
    const lightFixGeo = new THREE.CylinderGeometry(0.12, 0.12, 0.02, 16);
    const lightFixMat = new THREE.MeshStandardMaterial({
        color: 0xffffff, emissive: 0xfffae6, emissiveIntensity: 0.8,
        roughness: 0.2, metalness: 0.3
    });
    const lightFix = new THREE.Mesh(lightFixGeo, lightFixMat);
    lightFix.position.set(0, h - 0.01, 0);
    group.add(lightFix);

    scene.add(group);
    return group;
}
