import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { createWallTexture } from './scene.js';

const gltfLoader = new GLTFLoader();
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();
let selectedObj = null;
let dragPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 0);
let dragOffset = new THREE.Vector3();
let isDragging = false;
let objectList = [];
let outlineMesh = null;

export function getSelected() { return selectedObj; }
export function getObjects() { return objectList; }
export function setSelected(obj) {
    removeOutline();
    selectedObj = obj;
    if (obj) addOutline(obj);
}

function addOutline(group) {
    removeOutline();
    // find hitBox which is the second child usually, or just use userData.scale
    const s = group.userData.scale || [1, 1, 1];
    const geo = new THREE.BoxGeometry(s[0], s[1], s[2]);
    const mat = new THREE.MeshBasicMaterial({ color: 0x7cb342, wireframe: true, transparent: true, opacity: 0.6 });
    outlineMesh = new THREE.Mesh(geo, mat);
    outlineMesh.scale.multiplyScalar(1.02);
    outlineMesh.position.y = s[1]/2;
    group.add(outlineMesh);
}

function removeOutline() {
    if (outlineMesh && outlineMesh.parent) {
        outlineMesh.parent.remove(outlineMesh);
        outlineMesh.geometry.dispose();
        outlineMesh.material.dispose();
    }
    outlineMesh = null;
}

export function createFurniture(type, catalog, pos, rot, id, customScale) {
    let info = catalog[type];
    if (type === 'partition_wall') {
        info = info || { name: "Partition Wall", category: "build", color: "#f5f0eb", scale: [2.0, 3.2, 0.15] };
    }
    if (!info) return null;
    const s = customScale || info.scale || [1, 1, 1];
    
    const group = new THREE.Group();
    const color = new THREE.Color(info.color || '#888888');
    // Realistic PBR materials
    const mat = new THREE.MeshStandardMaterial({ color, roughness: 0.75, metalness: 0.02, envMapIntensity: 0.3 });
    const woodMat = new THREE.MeshStandardMaterial({ color: 0x7a4e2d, roughness: 0.85, metalness: 0.0, envMapIntensity: 0.2 });
    const darkWoodMat = new THREE.MeshStandardMaterial({ color: 0x4a2f1a, roughness: 0.8, metalness: 0.0 });
    const darkMat = new THREE.MeshStandardMaterial({ color: 0x1a1a1a, roughness: 0.25, metalness: 0.85, envMapIntensity: 0.6 });
    const fabricMat = new THREE.MeshStandardMaterial({ color: new THREE.Color(info.color || '#6b5b4f'), roughness: 0.95, metalness: 0.0 });
    const chromeMat = new THREE.MeshStandardMaterial({ color: 0xd0d0d0, roughness: 0.1, metalness: 0.95, envMapIntensity: 0.8 });
    const porcelainMat = new THREE.MeshStandardMaterial({ color: 0xf0ece8, roughness: 0.15, metalness: 0.05, envMapIntensity: 0.4 });
    
    // External asset loading if available
    if (info.asset_url) {
        gltfLoader.load(info.asset_url, (gltf) => {
            const model = gltf.scene;
            
            // Auto scale to match the info.scale
            const box = new THREE.Box3().setFromObject(model);
            const size = new THREE.Vector3();
            box.getSize(size);
            
            const scaleX = s[0] / size.x;
            const scaleY = s[1] / size.y;
            const scaleZ = s[2] / size.z;
            const minScale = Math.min(scaleX, scaleY, scaleZ);
            
            model.scale.set(minScale, minScale, minScale);
            
            // Center the model properly inside the group
            const center = new THREE.Vector3();
            box.getCenter(center);
            model.position.set(-center.x * minScale, (-center.y * minScale) + (s[1]/2), -center.z * minScale);
            
            // Enable shadows
            model.traverse(c => {
                if (c.isMesh) {
                    c.castShadow = true;
                    c.receiveShadow = true;
                }
            });
            
            group.add(model);
        }, undefined, (error) => {
            console.error('Error loading model:', error);
            // fallback to procedural
            const fallback = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), mat);
            fallback.position.y = s[1]/2;
            group.add(fallback);
        });
    } else if (type === 'dining_chair') {
        const chairWood = woodMat.clone();
        const cushMat = new THREE.MeshStandardMaterial({color: 0xe8e4e0, roughness: 0.9}); // Light fabric cushion
        const legH = s[1] * 0.48; // Seat is at half of chair height
        const legR = 0.02;
        
        // 4 thin legs
        [[-1,-1],[1,-1],[-1,1],[1,1]].forEach(([lx,lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(legR, legR*1.2, legH, 8), chairWood);
            leg.position.set(lx*(s[0]*0.4), legH/2, lz*(s[2]*0.4));
            group.add(leg);
        });
        
        // Seat cushion
        const seat = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.04, s[2]), cushMat);
        seat.position.y = legH + 0.02;
        group.add(seat);
        
        // Chair back support poles
        const backH = s[1]*0.48;
        [-1, 1].forEach(side => {
            const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.015, backH, 8), chairWood);
            pole.position.set(side*(s[0]*0.4), legH + 0.04 + backH/2, -s[2]*0.4);
            group.add(pole);
        });
        
        // Chair back rest slats
        const slatW = s[0]*0.8;
        const slatMat = chairWood;
        for (let i = 0; i < 3; i++) {
            const slat = new THREE.Mesh(new THREE.BoxGeometry(slatW, 0.04, 0.015), slatMat);
            slat.position.set(0, legH + 0.12 + i*(backH*0.3), -s[2]*0.4);
            group.add(slat);
        }
    } else if (type.includes('sofa') || type.includes('chair')) {
        const cushMat = new THREE.MeshStandardMaterial({color: new THREE.Color(info.color||'#6b5b4f').multiplyScalar(1.1), roughness: 0.95, metalness: 0, envMapIntensity: 0.1});
        const legMat = new THREE.MeshStandardMaterial({color: 0x5a3a1a, roughness: 0.75, metalness: 0.02});
        const legH = s[1]*0.15, legR = 0.025;
        // 4 tapered legs
        [[-1,-1],[1,-1],[-1,1],[1,1]].forEach(([lx,lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(legR, legR*1.4, legH, 8), legMat);
            leg.position.set(lx*(s[0]*0.42), legH/2, lz*(s[2]*0.38));
            group.add(leg);
        });
        // Seat frame
        const base = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.12, s[2]), mat);
        base.position.y = legH + s[1]*0.06;
        group.add(base);
        // Seat cushion (rounded)
        const seatW = type.includes('sofa') ? s[0]*0.85 : s[0]*0.8;
        const seat = new THREE.Mesh(new THREE.BoxGeometry(seatW, s[1]*0.15, s[2]*0.75, 2, 2, 2), cushMat);
        seat.position.set(0, legH+s[1]*0.19, s[2]*0.05);
        seat.geometry.computeVertexNormals();
        group.add(seat);
        // Back rest
        const backMat = mat.clone();
        const back = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.92, s[1]*0.55, s[2]*0.15), backMat);
        back.position.set(0, legH+s[1]*0.45, -s[2]*0.38);
        group.add(back);
        // Back cushions
        const nc = type.includes('sofa') ? 3 : 1;
        for (let ci=0; ci<nc; ci++) {
            const cx = nc===1?0:(ci-1)*(s[0]*0.3);
            const bc = new THREE.Mesh(new THREE.SphereGeometry(s[0]/(nc*2.5), 12, 8), cushMat);
            bc.scale.set(1, 1.2, 0.4);
            bc.position.set(cx, legH+s[1]*0.48, -s[2]*0.3);
            group.add(bc);
        }
        if (type.includes('sofa')) {
            // Armrests (rounded)
            [-1,1].forEach(side => {
                const arm = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.1, s[1]*0.35, s[2]*0.85), mat);
                arm.position.set(side*s[0]*0.45, legH+s[1]*0.3, -s[2]*0.02);
                const armTop = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.05, s[0]*0.05, s[2]*0.85, 8), mat);
                armTop.rotation.x = Math.PI/2;
                armTop.position.set(side*s[0]*0.45, legH+s[1]*0.48, -s[2]*0.02);
                group.add(arm, armTop);
            });
        }
    } else if (type.includes('table') || type.includes('desk')) {
        // Tabletop with edge banding
        const top = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.04, s[2]), woodMat);
        top.position.y = s[1] - 0.02;
        const edge = new THREE.Mesh(new THREE.BoxGeometry(s[0]+0.01, 0.02, s[2]+0.01), new THREE.MeshStandardMaterial({color:0x6b4a2a, roughness:0.6}));
        edge.position.y = s[1] - 0.04;
        group.add(top, edge);
        // Tapered legs
        const r = 0.035, lh = s[1]-0.06;
        [[-1,-1],[1,-1],[-1,1],[1,1]].forEach(([lx,lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(r*0.7, r, lh, 8), woodMat.clone());
            leg.position.set(lx*(s[0]/2-r*3), lh/2, lz*(s[2]/2-r*3));
            group.add(leg);
        });
        // Cross brace for desks
        if (type.includes('desk')) {
            const brace = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.8, 0.03, 0.03), woodMat);
            brace.position.set(0, lh*0.3, -s[2]/2+r*4);
            group.add(brace);
        }
    } else if (type.includes('bed')) {
        const frameMat = woodMat.clone();
        // Bed frame with legs
        const fH = 0.18;
        [[-1,-1],[1,-1],[-1,1],[1,1]].forEach(([lx,lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, fH, 8), frameMat);
            leg.position.set(lx*(s[0]/2-0.06), fH/2, lz*(s[2]/2-0.06));
            group.add(leg);
        });
        const frame = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.06, s[2]), frameMat);
        frame.position.y = fH + 0.03;
        // Slats
        for (let si=0; si<6; si++) {
            const slat = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.95, 0.02, 0.08), frameMat);
            slat.position.set(0, fH+0.07, -s[2]*0.35 + si*(s[2]*0.7/5));
            group.add(slat);
        }
        // Mattress (soft look)
        const mattMat = new THREE.MeshStandardMaterial({color: 0xf5f0e8, roughness: 0.95});
        const matt = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.94, 0.2, s[2]*0.92), mattMat);
        matt.position.y = fH + 0.18;
        // Blanket/duvet
        const blanketMat = new THREE.MeshStandardMaterial({color: 0xd4c8b8, roughness: 0.95});
        const blanket = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.92, 0.06, s[2]*0.6), blanketMat);
        blanket.position.set(0, fH+0.31, s[2]*0.12);
        // Headboard (curved top)
        const hb = new THREE.Mesh(new THREE.BoxGeometry(s[0]+0.04, s[1]*0.9, 0.08), frameMat);
        hb.position.set(0, s[1]*0.45, -s[2]/2+0.04);
        // Pillows (soft spheres)
        const pillMat = new THREE.MeshStandardMaterial({color: 0xfaf8f5, roughness: 0.9});
        const np = type.includes('double') ? 2 : 1;
        for (let pi=0; pi<np; pi++) {
            const px = np===1 ? 0 : (pi-0.5)*s[0]*0.45;
            const pill = new THREE.Mesh(new THREE.SphereGeometry(0.18, 12, 8), pillMat);
            pill.scale.set(1.8, 0.5, 1.2);
            pill.position.set(px, fH+0.38, -s[2]/2+0.35);
            group.add(pill);
        }
        group.add(frame, matt, blanket, hb);
    } else if (type === 'tv_stand') {
        // ── TV Stand: wooden media console cabinet with flat-screen TV on top ──
        const cabinetMat = woodMat.clone();
        const cabinetH = s[1] * 0.55;
        // Cabinet body
        const cab = new THREE.Mesh(new THREE.BoxGeometry(s[0], cabinetH, s[2]), cabinetMat);
        cab.position.y = cabinetH / 2;
        group.add(cab);
        // Cabinet top surface (slightly wider)
        const topSlab = new THREE.Mesh(new THREE.BoxGeometry(s[0] + 0.02, 0.03, s[2] + 0.02), new THREE.MeshStandardMaterial({ color: 0x5a3a20, roughness: 0.6, metalness: 0.05 }));
        topSlab.position.y = cabinetH + 0.015;
        group.add(topSlab);
        // Cabinet doors (2 panels)
        const doorMat2 = new THREE.MeshStandardMaterial({ color: 0x6b4a2a, roughness: 0.7 });
        [-1, 1].forEach(side => {
            const door = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.46, cabinetH * 0.8, 0.015), doorMat2);
            door.position.set(side * s[0] * 0.24, cabinetH * 0.45, s[2] / 2 + 0.008);
            group.add(door);
            // Door handle knob
            const knob = new THREE.Mesh(new THREE.SphereGeometry(0.015, 8, 8), chromeMat.clone());
            knob.position.set(side * s[0] * 0.08, cabinetH * 0.45, s[2] / 2 + 0.02);
            group.add(knob);
        });
        // Small legs
        const legH2 = 0.06;
        [[-1, -1], [1, -1], [-1, 1], [1, 1]].forEach(([lx, lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, legH2, 8), darkMat);
            leg.position.set(lx * (s[0] / 2 - 0.05), -legH2/2 + 0, lz * (s[2] / 2 - 0.05));
            // legs are below cabinet, we shift cabinet up
        });
        // TV screen on top of cabinet
        const tvH = s[1] * 0.55;
        const tvW = s[0] * 0.85;
        // TV base stand
        const tvBase = new THREE.Mesh(new THREE.BoxGeometry(tvW * 0.25, 0.015, s[2] * 0.4), darkMat);
        tvBase.position.set(0, cabinetH + 0.038, 0);
        group.add(tvBase);
        // TV neck
        const tvNeck = new THREE.Mesh(new THREE.BoxGeometry(0.03, 0.06, 0.03), darkMat);
        tvNeck.position.set(0, cabinetH + 0.068, 0);
        group.add(tvNeck);
        // TV panel
        const tvPanel = new THREE.Mesh(new THREE.BoxGeometry(tvW, tvH, 0.025), darkMat);
        tvPanel.position.set(0, cabinetH + 0.03 + tvH / 2 + 0.07, 0);
        group.add(tvPanel);
        // TV screen face (emissive dark blue)
        const screenMat = new THREE.MeshStandardMaterial({ color: 0x080810, metalness: 0.95, roughness: 0.05, emissive: 0x060612, emissiveIntensity: 0.4 });
        const tvScreen = new THREE.Mesh(new THREE.BoxGeometry(tvW * 0.95, tvH * 0.92, 0.003), screenMat);
        tvScreen.position.set(0, cabinetH + 0.03 + tvH / 2 + 0.07, 0.014);
        group.add(tvScreen);
    } else if (type.includes('tv') || type.includes('monitor')) {
        // ── Standalone TV / Monitor ──
        const base = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.18, s[0]*0.22, 0.02, 16), darkMat);
        base.position.y = 0.01;
        const neck = new THREE.Mesh(new THREE.BoxGeometry(0.04, s[1]*0.25, 0.04), darkMat);
        neck.position.y = s[1]*0.13;
        const panel = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.65, 0.03), darkMat);
        panel.position.set(0, s[1]*0.6, 0);
        const bezel = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.96, s[1]*0.61, 0.005),
            new THREE.MeshStandardMaterial({color: 0x111118, metalness: 0.95, roughness: 0.05, emissive: 0x0a0a1a, emissiveIntensity: 0.3}));
        bezel.position.set(0, s[1]*0.6, 0.018);
        group.add(base, neck, panel, bezel);
    } else if (type === 'nightstand') {
        // ── Nightstand: small bedside table with drawer ──
        const nsWood = woodMat.clone();
        // Legs
        const legH3 = s[1] * 0.2;
        [[-1, -1], [1, -1], [-1, 1], [1, 1]].forEach(([lx, lz]) => {
            const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.022, legH3, 8), nsWood);
            leg.position.set(lx * (s[0] / 2 - 0.04), legH3 / 2, lz * (s[2] / 2 - 0.04));
            group.add(leg);
        });
        // Body
        const nsBody = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1] * 0.75, s[2]), nsWood);
        nsBody.position.y = legH3 + s[1] * 0.375;
        group.add(nsBody);
        // Top surface
        const nsTop = new THREE.Mesh(new THREE.BoxGeometry(s[0] + 0.02, 0.025, s[2] + 0.02), new THREE.MeshStandardMaterial({ color: 0x5c3d1e, roughness: 0.55 }));
        nsTop.position.y = legH3 + s[1] * 0.75 + 0.012;
        group.add(nsTop);
        // Drawer panel line
        const drawerPanel = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.85, s[1] * 0.3, 0.012), new THREE.MeshStandardMaterial({ color: 0x8a6540, roughness: 0.7 }));
        drawerPanel.position.set(0, legH3 + s[1] * 0.35, s[2] / 2 + 0.007);
        group.add(drawerPanel);
        // Drawer groove line
        const groove = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.82, 0.003, 0.003), darkMat);
        groove.position.set(0, legH3 + s[1] * 0.55, s[2] / 2 + 0.014);
        group.add(groove);
        // Handle knob
        const nsKnob = new THREE.Mesh(new THREE.SphereGeometry(0.012, 8, 8), chromeMat.clone());
        nsKnob.position.set(0, legH3 + s[1] * 0.35, s[2] / 2 + 0.02);
        group.add(nsKnob);
    } else if (type === 'oven') {
        // ── Oven: kitchen appliance with glass door, handle, dials ──
        const ovenBodyMat = new THREE.MeshStandardMaterial({ color: 0xc0c0c5, roughness: 0.25, metalness: 0.6 });
        // Main body
        const ovenBody = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), ovenBodyMat);
        ovenBody.position.y = s[1] / 2;
        group.add(ovenBody);
        // Top control panel strip
        const ctrlPanel = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.95, s[1] * 0.1, 0.008), new THREE.MeshStandardMaterial({ color: 0x2a2a2a, roughness: 0.3, metalness: 0.8 }));
        ctrlPanel.position.set(0, s[1] * 0.88, s[2] / 2 + 0.005);
        group.add(ctrlPanel);
        // Control dials (4 small cylinders)
        for (let di = 0; di < 4; di++) {
            const dx = (di - 1.5) * (s[0] * 0.2);
            const dial = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, 0.012, 12), chromeMat.clone());
            dial.rotation.x = Math.PI / 2;
            dial.position.set(dx, s[1] * 0.88, s[2] / 2 + 0.015);
            group.add(dial);
        }
        // Glass door window
        const glassMat = new THREE.MeshStandardMaterial({ color: 0x1a1a2e, roughness: 0.05, metalness: 0.3, transparent: true, opacity: 0.7 });
        const glass = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.8, s[1] * 0.55, 0.008), glassMat);
        glass.position.set(0, s[1] * 0.42, s[2] / 2 + 0.005);
        group.add(glass);
        // Glass door frame
        const frameMat2 = new THREE.MeshStandardMaterial({ color: 0x3a3a3a, roughness: 0.3, metalness: 0.7 });
        // Top frame
        const ft = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.84, 0.02, 0.01), frameMat2);
        ft.position.set(0, s[1] * 0.72, s[2] / 2 + 0.006);
        group.add(ft);
        // Bottom frame
        const fb = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.84, 0.02, 0.01), frameMat2);
        fb.position.set(0, s[1] * 0.12, s[2] / 2 + 0.006);
        group.add(fb);
        // Handle bar
        const hdlBar = new THREE.Mesh(new THREE.BoxGeometry(s[0] * 0.6, 0.02, 0.025), chromeMat.clone());
        hdlBar.position.set(0, s[1] * 0.78, s[2] / 2 + 0.02);
        group.add(hdlBar);
    } else if (type === 'kitchen_counter') {
        // ── Kitchen Counter: cabinet body + marble countertop slab ──
        const kcWood = new THREE.MeshStandardMaterial({ color: 0x6b4a2a, roughness: 0.8, metalness: 0 });
        const counterH = s[1] * 0.88;
        // Cabinet body
        const kcBody = new THREE.Mesh(new THREE.BoxGeometry(s[0], counterH, s[2]), kcWood);
        kcBody.position.y = counterH / 2;
        group.add(kcBody);
        // Cabinet door panels
        const numDoors = Math.max(1, Math.round(s[0] / 0.5));
        const doorW = (s[0] - 0.04) / numDoors;
        for (let di = 0; di < numDoors; di++) {
            const dx = -s[0] / 2 + 0.02 + doorW / 2 + di * doorW;
            const kcDoor = new THREE.Mesh(new THREE.BoxGeometry(doorW - 0.02, counterH * 0.75, 0.012), new THREE.MeshStandardMaterial({ color: 0x7a5c3a, roughness: 0.7 }));
            kcDoor.position.set(dx, counterH * 0.4, s[2] / 2 + 0.007);
            group.add(kcDoor);
            // Handle
            const kcH = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.008, 0.015), chromeMat.clone());
            kcH.position.set(dx, counterH * 0.5, s[2] / 2 + 0.018);
            group.add(kcH);
        }
        // Marble countertop
        const marbleMat = new THREE.MeshStandardMaterial({ color: 0xf0ece6, roughness: 0.15, metalness: 0.05, envMapIntensity: 0.4 });
        const marble = new THREE.Mesh(new THREE.BoxGeometry(s[0] + 0.04, 0.04, s[2] + 0.03), marbleMat);
        marble.position.y = counterH + 0.02;
        group.add(marble);
        // Marble edge bevel
        const edgeMat = new THREE.MeshStandardMaterial({ color: 0xe8e2da, roughness: 0.2 });
        const marbleEdge = new THREE.Mesh(new THREE.BoxGeometry(s[0] + 0.04, 0.02, 0.008), edgeMat);
        marbleEdge.position.set(0, counterH + 0.01, s[2] / 2 + 0.02);
        group.add(marbleEdge);
    } else if (type === 'mirror') {
        // ── Wall Mirror: rectangular frame with reflective face ──
        const mirrorFrameMat = new THREE.MeshStandardMaterial({ color: 0xc0c0c0, roughness: 0.15, metalness: 0.85 });
        const frameW = 0.025;
        // Frame pieces (top, bottom, left, right)
        const fTop = new THREE.Mesh(new THREE.BoxGeometry(s[0], frameW, s[2] + 0.01), mirrorFrameMat);
        fTop.position.y = s[1] / 2 + s[1] / 2 - frameW / 2;
        group.add(fTop);
        const fBot = new THREE.Mesh(new THREE.BoxGeometry(s[0], frameW, s[2] + 0.01), mirrorFrameMat);
        fBot.position.y = s[1] / 2 - s[1] / 2 + frameW / 2;
        group.add(fBot);
        const fLeft = new THREE.Mesh(new THREE.BoxGeometry(frameW, s[1], s[2] + 0.01), mirrorFrameMat);
        fLeft.position.set(-s[0] / 2 + frameW / 2, s[1] / 2, 0);
        group.add(fLeft);
        const fRight = new THREE.Mesh(new THREE.BoxGeometry(frameW, s[1], s[2] + 0.01), mirrorFrameMat);
        fRight.position.set(s[0] / 2 - frameW / 2, s[1] / 2, 0);
        group.add(fRight);
        // Reflective mirror surface
        const mirrorFaceMat = new THREE.MeshStandardMaterial({ color: 0xc8dce8, roughness: 0.02, metalness: 0.95, envMapIntensity: 1.0 });
        const mirrorFace = new THREE.Mesh(new THREE.BoxGeometry(s[0] - frameW * 2, s[1] - frameW * 2, s[2]), mirrorFaceMat);
        mirrorFace.position.y = s[1] / 2;
        group.add(mirrorFace);
        // Backing board
        const backing = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], 0.008), new THREE.MeshStandardMaterial({ color: 0x3a3a3a, roughness: 0.9 }));
        backing.position.set(0, s[1] / 2, -s[2] / 2);
        group.add(backing);
    } else if (type === 'painting') {
        // ── Wall Painting: wooden frame + colorful art canvas ──
        const paintFrameMat = new THREE.MeshStandardMaterial({ color: 0x8b6914, roughness: 0.6, metalness: 0.1 });
        const fw = 0.03;
        // Frame
        const pfTop = new THREE.Mesh(new THREE.BoxGeometry(s[0] + fw, fw, s[2] + 0.012), paintFrameMat);
        pfTop.position.y = s[1] / 2 + s[1] / 2;
        group.add(pfTop);
        const pfBot = new THREE.Mesh(new THREE.BoxGeometry(s[0] + fw, fw, s[2] + 0.012), paintFrameMat);
        pfBot.position.y = s[1] / 2 - s[1] / 2;
        group.add(pfBot);
        const pfL = new THREE.Mesh(new THREE.BoxGeometry(fw, s[1] + fw, s[2] + 0.012), paintFrameMat);
        pfL.position.set(-s[0] / 2, s[1] / 2, 0);
        group.add(pfL);
        const pfR = new THREE.Mesh(new THREE.BoxGeometry(fw, s[1] + fw, s[2] + 0.012), paintFrameMat);
        pfR.position.set(s[0] / 2, s[1] / 2, 0);
        group.add(pfR);
        // Canvas with abstract art (multiple colored blocks)
        const canvasBg = new THREE.Mesh(new THREE.BoxGeometry(s[0] - fw, s[1] - fw, s[2]), new THREE.MeshStandardMaterial({ color: 0xf5f0e8, roughness: 0.9 }));
        canvasBg.position.y = s[1] / 2;
        group.add(canvasBg);
        // Abstract art blocks
        const artColors = [0xc44e52, 0x4a90d9, 0xe8a838, 0x50a878, 0x9b59b6];
        for (let ai = 0; ai < 5; ai++) {
            const aw = (s[0] - fw * 2) * (0.2 + Math.sin(ai * 1.7) * 0.15);
            const ah = (s[1] - fw * 2) * (0.2 + Math.cos(ai * 2.3) * 0.12);
            const ax = (Math.sin(ai * 3.1) * 0.3) * (s[0] - fw * 2);
            const ay = (Math.cos(ai * 2.7) * 0.25) * (s[1] - fw * 2);
            const artBlock = new THREE.Mesh(
                new THREE.BoxGeometry(aw, ah, 0.003),
                new THREE.MeshStandardMaterial({ color: artColors[ai], roughness: 0.8 })
            );
            artBlock.position.set(ax, s[1] / 2 + ay, s[2] / 2 + 0.002);
            group.add(artBlock);
        }
    } else if (type === 'clock') {
        // ── Wall Clock: circular chrome frame + white face + hands ──
        const clockRadius = Math.min(s[0], s[1]) / 2;
        // Chrome rim
        const rimMat = new THREE.MeshStandardMaterial({ color: 0x303030, roughness: 0.15, metalness: 0.9 });
        const clockRim = new THREE.Mesh(new THREE.TorusGeometry(clockRadius, 0.02, 12, 32), rimMat);
        clockRim.position.y = s[1] / 2;
        group.add(clockRim);
        // Clock face
        const faceMat = new THREE.MeshStandardMaterial({ color: 0xf8f6f2, roughness: 0.85 });
        const clockFace = new THREE.Mesh(new THREE.CircleGeometry(clockRadius - 0.015, 32), faceMat);
        clockFace.position.set(0, s[1] / 2, s[2] / 2 + 0.003);
        group.add(clockFace);
        // Backing disc
        const backDisc = new THREE.Mesh(new THREE.CircleGeometry(clockRadius, 32), new THREE.MeshStandardMaterial({ color: 0x1a1a1a, roughness: 0.9 }));
        backDisc.position.set(0, s[1] / 2, -s[2] / 2);
        backDisc.rotation.y = Math.PI;
        group.add(backDisc);
        // Hour markers (12 small dots)
        const markerMat = new THREE.MeshStandardMaterial({ color: 0x1a1a1a, roughness: 0.5 });
        for (let hi = 0; hi < 12; hi++) {
            const angle = (hi / 12) * Math.PI * 2 - Math.PI / 2;
            const mx = Math.cos(angle) * clockRadius * 0.82;
            const my = Math.sin(angle) * clockRadius * 0.82;
            const isMain = hi % 3 === 0;
            const marker = new THREE.Mesh(new THREE.BoxGeometry(isMain ? 0.02 : 0.008, isMain ? 0.04 : 0.02, 0.004), markerMat);
            marker.position.set(mx, s[1] / 2 + my, s[2] / 2 + 0.005);
            marker.rotation.z = angle + Math.PI / 2;
            group.add(marker);
        }
        // Hour hand (pointing to ~10)
        const hourHandMat = new THREE.MeshStandardMaterial({ color: 0x1a1a1a, roughness: 0.5, metalness: 0.3 });
        const hourHand = new THREE.Mesh(new THREE.BoxGeometry(0.015, clockRadius * 0.5, 0.005), hourHandMat);
        hourHand.position.set(0, s[1] / 2, s[2] / 2 + 0.007);
        hourHand.geometry.translate(0, clockRadius * 0.25, 0);
        hourHand.rotation.z = Math.PI / 6; // ~10 o'clock
        group.add(hourHand);
        // Minute hand (pointing to ~2)
        const minHand = new THREE.Mesh(new THREE.BoxGeometry(0.01, clockRadius * 0.7, 0.005), hourHandMat);
        minHand.position.set(0, s[1] / 2, s[2] / 2 + 0.009);
        minHand.geometry.translate(0, clockRadius * 0.35, 0);
        minHand.rotation.z = -Math.PI / 3; // ~10:10
        group.add(minHand);
        // Center cap
        const capMat = new THREE.MeshStandardMaterial({ color: 0x2a2a2a, roughness: 0.2, metalness: 0.8 });
        const cap = new THREE.Mesh(new THREE.SphereGeometry(0.015, 12, 8), capMat);
        cap.position.set(0, s[1] / 2, s[2] / 2 + 0.011);
        group.add(cap);
    } else if (type === 'curtain') {
        // ── Curtain: chrome rod + pleated fabric panels ──
        const rodMat = new THREE.MeshStandardMaterial({ color: 0xc0c0c0, roughness: 0.1, metalness: 0.9 });
        // Curtain rod
        const rod = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, s[0] + 0.1, 12), rodMat);
        rod.rotation.z = Math.PI / 2;
        rod.position.set(0, s[1] - 0.02, 0);
        group.add(rod);
        // Rod finials (end caps)
        [-1, 1].forEach(side => {
            const finial = new THREE.Mesh(new THREE.SphereGeometry(0.02, 8, 8), rodMat);
            finial.position.set(side * (s[0] / 2 + 0.05), s[1] - 0.02, 0);
            group.add(finial);
        });
        // Rod brackets
        [-1, 1].forEach(side => {
            const bracket = new THREE.Mesh(new THREE.BoxGeometry(0.025, 0.04, 0.025), rodMat);
            bracket.position.set(side * (s[0] / 2 - 0.05), s[1] - 0.04, -s[2] / 2 + 0.01);
            group.add(bracket);
        });
        // Fabric curtain panels (left and right, with pleats)
        const curtainColor = new THREE.Color(info.color || '#d4a574');
        const curtainMat1 = new THREE.MeshStandardMaterial({ color: curtainColor, roughness: 0.92, metalness: 0, side: THREE.DoubleSide });
        const curtainMat2 = new THREE.MeshStandardMaterial({ color: curtainColor.clone().multiplyScalar(0.9), roughness: 0.92, metalness: 0, side: THREE.DoubleSide });
        // Left panel pleats
        const panelW = s[0] * 0.35;
        const numPleats = 5;
        const pleatW = panelW / numPleats;
        [-1, 1].forEach(side => {
            const panelX = side * (s[0] / 2 - panelW / 2);
            for (let pi = 0; pi < numPleats; pi++) {
                const px = panelX + (pi - numPleats / 2 + 0.5) * pleatW;
                const depth = (pi % 2 === 0) ? 0.015 : -0.015;
                const pleat = new THREE.Mesh(
                    new THREE.BoxGeometry(pleatW * 0.95, s[1] * 0.92, s[2] * 0.6),
                    pi % 2 === 0 ? curtainMat1 : curtainMat2
                );
                pleat.position.set(px, s[1] * 0.46, depth);
                group.add(pleat);
            }
        });
    } else if (type.includes('plant')) {
        // Terracotta pot with rim
        const potMat = new THREE.MeshStandardMaterial({color: 0xc4785a, roughness: 0.85});
        const pot = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.3, s[0]*0.2, s[1]*0.25, 16), potMat);
        pot.position.y = s[1]*0.125;
        const rim = new THREE.Mesh(new THREE.TorusGeometry(s[0]*0.3, 0.02, 8, 16), potMat);
        rim.rotation.x = Math.PI/2; rim.position.y = s[1]*0.25;
        // Soil
        const soil = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.28, s[0]*0.28, 0.03, 16),
            new THREE.MeshStandardMaterial({color: 0x3a2a1a, roughness: 1}));
        soil.position.y = s[1]*0.24;
        // Trunk
        const trMat = new THREE.MeshStandardMaterial({color: 0x5a4030, roughness: 0.9});
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.025, s[1]*0.4, 6), trMat);
        trunk.position.y = s[1]*0.42;
        // Multiple leaf clusters
        const lfMat = new THREE.MeshStandardMaterial({color: 0x2d7a2d, roughness: 0.85});
        const lfMat2 = new THREE.MeshStandardMaterial({color: 0x3a9a3a, roughness: 0.85});
        [[0, s[1]*0.7, 0, 0.35],[0.08, s[1]*0.8, 0.05, 0.25],[-0.06, s[1]*0.65, 0.08, 0.2],[0.05, s[1]*0.75, -0.06, 0.22]].forEach(([x,y,z,r], i) => {
            const lf = new THREE.Mesh(new THREE.SphereGeometry(s[0]*r, 10, 8), i%2===0?lfMat:lfMat2);
            lf.scale.set(1, 1.2, 1); lf.position.set(x, y, z);
            group.add(lf);
        });
        group.add(pot, rim, soil, trunk);
    } else if (type.includes('rug') || type.includes('carpet')) {
        const rugMat = new THREE.MeshStandardMaterial({color, roughness: 1.0});
        const rug = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.015, s[2]), rugMat);
        rug.position.y = 0.008;
        // Border trim
        const borderMat = new THREE.MeshStandardMaterial({color: new THREE.Color(color).multiplyScalar(0.7), roughness: 1});
        const bw = 0.06;
        [[s[0], bw, 0, s[2]/2-bw/2],[s[0], bw, 0, -s[2]/2+bw/2],[bw, s[2], -s[0]/2+bw/2, 0],[bw, s[2], s[0]/2-bw/2, 0]].forEach(([w,d,x,z]) => {
            const b = new THREE.Mesh(new THREE.BoxGeometry(w, 0.018, d), borderMat);
            b.position.set(x, 0.01, z); group.add(b);
        });
        group.add(rug);
    } else if (type === 'bookshelf' || type.includes('bookshelf')) {
        const shelfMat = woodMat.clone();
        const frame = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), shelfMat);
        frame.position.y = s[1]/2;
        group.add(frame);
        
        // Inner cavity cutout (procedural visual via darker backing box)
        const innerCutout = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.88, s[1]*0.92, s[2]*0.95), new THREE.MeshStandardMaterial({color: 0x422a18, roughness: 0.9}));
        innerCutout.position.set(0, s[1]/2, s[2]*0.02);
        group.add(innerCutout);
        
        // Shelves
        const numShelves = 4;
        const sh = (s[1]*0.92) / numShelves;
        const bookColors = [0xa33535, 0x2e5984, 0x3d7a5a, 0xd4a35c, 0x6d5c5c];
        
        for (let i = 1; i < numShelves; i++) {
            const sy = sh * i;
            const shelf = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.88, 0.02, s[2]*0.9), shelfMat);
            shelf.position.set(0, sy, s[2]*0.02);
            group.add(shelf);
            
            // Add some colorful books on each shelf!
            const numBooks = 3 + Math.floor(Math.random()*4);
            const bookW = 0.03, bookH = 0.18, bookD = 0.14;
            const startX = -s[0]*0.32;
            for (let b = 0; b < numBooks; b++) {
                const bookColor = bookColors[Math.floor(Math.random()*bookColors.length)];
                const bookMat = new THREE.MeshStandardMaterial({color: bookColor, roughness: 0.6});
                const book = new THREE.Mesh(new THREE.BoxGeometry(bookW, bookH, bookD), bookMat);
                // Slight random lean angle
                const lean = (Math.random() < 0.25) ? (Math.random()*0.18 - 0.09) : 0;
                book.rotation.z = lean;
                book.position.set(startX + b * (bookW + 0.018), sy + bookH/2 + 0.01, s[2]*0.05);
                group.add(book);
            }
        }
    } else if (type.includes('wardrobe') || type.includes('cabinet') || type.includes('dresser') || type.includes('shelf')) {
        const wMat = woodMat.clone();
        // Main body
        const body = new THREE.Mesh(new THREE.BoxGeometry(s[0]-0.02, s[1]-0.02, s[2]), wMat);
        body.position.y = s[1]/2;
        // Top crown molding
        const crown = new THREE.Mesh(new THREE.BoxGeometry(s[0]+0.04, 0.04, s[2]+0.02), wMat);
        crown.position.y = s[1]-0.01;
        // Door panels
        const doorMat = new THREE.MeshStandardMaterial({color: 0x7a5c3a, roughness: 0.75});
        const nd = s[0] > 0.8 ? 2 : 1;
        for (let di=0; di<nd; di++) {
            const dx = nd===1 ? 0 : (di-0.5)*(s[0]*0.48);
            const door = new THREE.Mesh(new THREE.BoxGeometry(s[0]/nd-0.06, s[1]*0.85, 0.02), doorMat);
            door.position.set(dx, s[1]*0.45, s[2]/2+0.01);
            // Handle
            const h = new THREE.Mesh(new THREE.CylinderGeometry(0.01, 0.01, 0.12, 8), darkMat);
            h.position.set(dx+(di===0?s[0]/(nd*2.5):-s[0]/(nd*2.5)), s[1]*0.5, s[2]/2+0.025);
            group.add(door, h);
        }
        group.add(body, crown);
    } else if (type.includes('lamp')) {
        const lMat = new THREE.MeshStandardMaterial({color: 0x2a2a2a, roughness: 0.4, metalness: 0.6});
        if (type.includes('floor')) {
            // Floor lamp: base, pole, shade
            const base = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.14, 0.03, 16), lMat);
            base.position.y = 0.015;
            const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.015, s[1]*0.85, 8), lMat);
            pole.position.y = s[1]*0.45;
            const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.15, s[1]*0.2, 16, 1, true),
                new THREE.MeshStandardMaterial({color: 0xf5e6c8, roughness: 0.9, side: THREE.DoubleSide, emissive: 0xffd700, emissiveIntensity: 0.15}));
            shade.position.y = s[1]*0.88;
            group.add(base, pole, shade);
        } else {
            const base = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.07, 0.02, 12), lMat);
            base.position.y = 0.01;
            const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, s[1]*0.5, 8), lMat);
            pole.position.y = s[1]*0.27;
            const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.09, s[1]*0.35, 12, 1, true),
                new THREE.MeshStandardMaterial({color: 0xf0e0c0, roughness: 0.9, side: THREE.DoubleSide, emissive: 0xffd700, emissiveIntensity: 0.1}));
            shade.position.y = s[1]*0.75;
            group.add(base, pole, shade);
        }
    } else if (type.includes('fridge')) {
        const fridgeMat = new THREE.MeshStandardMaterial({color: 0xdcdce1, roughness: 0.2, metalness: 0.5, envMapIntensity: 0.5});
        const body = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), fridgeMat);
        body.position.y = s[1]/2;
        // Door line
        const line = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.9, 0.005, 0.005), darkMat);
        line.position.set(0, s[1]*0.6, s[2]/2+0.003);
        // Handle
        const hdl = new THREE.Mesh(new THREE.BoxGeometry(0.02, s[1]*0.25, 0.03), chromeMat.clone());
        hdl.position.set(s[0]*0.35, s[1]*0.4, s[2]/2+0.02);
        group.add(body, line, hdl);
    } else if (type.includes('toilet')) {
        const porMat = porcelainMat.clone();
        // Bowl
        const bowl = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.4, s[0]*0.35, s[1]*0.4, 16), porMat);
        bowl.position.y = s[1]*0.2;
        // Seat
        const seat = new THREE.Mesh(new THREE.TorusGeometry(s[0]*0.32, 0.04, 8, 16), porMat);
        seat.rotation.x = Math.PI/2; seat.position.y = s[1]*0.42;
        // Tank
        const tank = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.7, s[1]*0.45, s[2]*0.3), porMat);
        tank.position.set(0, s[1]*0.55, -s[2]*0.3);
        group.add(bowl, seat, tank);
    } else if (type.includes('bath')) {
        const bathMat = porcelainMat.clone();
        const outer = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), bathMat);
        outer.position.y = s[1]/2;
        // Inner (darker)
        const inner = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.9, s[1]*0.7, s[2]*0.85),
            new THREE.MeshStandardMaterial({color: 0xe8e4e0, roughness: 0.15}));
        inner.position.y = s[1]*0.55;
        // Faucet
        const faucet = new THREE.Mesh(new THREE.CylinderGeometry(0.015, 0.015, 0.15, 8), chromeMat.clone());
        faucet.position.set(0, s[1]+0.07, -s[2]*0.35);
        group.add(outer, inner, faucet);
    } else if (type === 'partition_wall') {
        const pColor = info.color || '#f5f0eb';
        const wallTex = createWallTexture(pColor);
        const wallMat = new THREE.MeshStandardMaterial({
            map: wallTex,
            color: new THREE.Color(pColor),
            roughness: 0.82,
            metalness: 0,
            envMapIntensity: 0.15
        });
        const wallMesh = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), wallMat);
        wallMesh.position.y = s[1]/2;
        wallMesh.userData = { type: 'wall' };
        group.add(wallMesh);
    } else if (type.includes('sink')) {
        const sinkMat = porcelainMat.clone();
        const cab = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.7, s[2]), woodMat);
        cab.position.y = s[1]*0.35;
        const top = new THREE.Mesh(new THREE.BoxGeometry(s[0]+0.02, 0.04, s[2]+0.02), sinkMat);
        top.position.y = s[1]*0.72;
        const basin = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.25, s[0]*0.2, 0.1, 16), sinkMat);
        basin.position.y = s[1]*0.68;
        // Faucet
        const fc = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, 0.18, 8), chromeMat.clone());
        fc.position.set(0, s[1]*0.82, -s[2]*0.2);
        group.add(cab, top, basin, fc);
    } else {
        const box = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), mat);
        box.position.y = s[1]/2;
        group.add(box);
    }

    const wrapper = new THREE.Group();
    wrapper.add(group);
    
    const hitBoxGeo = new THREE.BoxGeometry(s[0], s[1], s[2]);
    const hitBox = new THREE.Mesh(hitBoxGeo, new THREE.MeshBasicMaterial({visible: false}));
    hitBox.position.y = s[1]/2;
    wrapper.add(hitBox);

    // Snapping wall-mounted items (curtains, mirrors, paintings, clocks) to the outer walls
    const adjustedPos = snapWallMountedItem(type, pos, rot, s);
    wrapper.position.set(adjustedPos[0], adjustedPos[1] - s[1]/2, adjustedPos[2]);
    if (rot) wrapper.rotation.set(rot[0], rot[1], rot[2]);
    
    wrapper.traverse(c => {
        if (c.isMesh && c !== hitBox) {
            c.castShadow = true;
            c.receiveShadow = true;
        }
    });

    wrapper.userData = { type: 'furniture', furnitureType: type, name: info.name, id: id || THREE.MathUtils.generateUUID().slice(0,8), category: info.category, scale: s };
    wrapper.name = `furniture_${type}_${wrapper.userData.id}`;
    objectList.push(wrapper);
    return wrapper;
}

export function deleteSelected(scene) {
    if (!selectedObj) return;
    scene.remove(selectedObj);
    objectList = objectList.filter(o => o !== selectedObj);
    removeOutline();
    selectedObj = null;
}

export function handleClick(event, camera, scene, container, mode, onSelect) {
    if (mode === 'explore') {
        mouse.x = 0;
        mouse.y = 0;
    } else {
        const rect = container.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    }
    raycaster.setFromCamera(mouse, camera);
    const targets = objectList.filter(o => o.parent);
    
    // FIX SELECTION: Use recursive=true to hit the child meshes of our wrapper Group
    const hits = raycaster.intersectObjects(targets, true);
    if (hits.length > 0) {
        // Since we outline the group, hits[0].object is likely the mesh, we want its parent Group
        let hit = hits[0].object;
        if (hit.parent && hit.parent.userData.type === 'furniture') hit = hit.parent;
        else if (hit.parent && hit.parent.parent && hit.parent.parent.userData.type === 'furniture') hit = hit.parent.parent;
        
        setSelected(hit);
        if (onSelect) onSelect(hit);
        return hit;
    } else {
        setSelected(null);
        if (onSelect) onSelect(null);
        return null;
    }
}

export function startDrag(event, camera, container) {
    if (!selectedObj) return false;
    const rect = container.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);
    const hits = raycaster.intersectObjects([selectedObj], false);
    if (hits.length > 0) {
        isDragging = true;
        dragPlane.set(new THREE.Vector3(0, 1, 0), -selectedObj.position.y);
        const pt = new THREE.Vector3();
        raycaster.ray.intersectPlane(dragPlane, pt);
        dragOffset.subVectors(selectedObj.position, pt);
        return true;
    }
    return false;
}

export function doDrag(event, camera, container) {
    if (!isDragging || !selectedObj) return;
    const rect = container.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);
    const pt = new THREE.Vector3();
    raycaster.ray.intersectPlane(dragPlane, pt);
    selectedObj.position.x = pt.x + dragOffset.x;
    selectedObj.position.z = pt.z + dragOffset.z;
    
    // Snapping!
    if (selectedObj.parent) {
        applyAlignmentAssist(selectedObj, selectedObj.parent);
    }
    
    // Constrain position to prevent wall penetration
    constrainObjectPosition(selectedObj, roomWidth, roomLength, objectList);
}

export function endDrag() { isDragging = false; }
export function getDragging() { return isDragging; }

export function serializeObjects() {
    return objectList.filter(o => o.parent).map(o => ({
        id: o.userData.id, type: o.userData.furnitureType, name: o.userData.name,
        category: o.userData.category,
        position: [o.position.x, o.position.y + (o.userData.scale[1]*o.scale.y/2), o.position.z],
        rotation: [o.rotation.x, o.rotation.y, o.rotation.z],
        scale: [
            o.userData.scale[0] * o.scale.x,
            o.userData.scale[1] * o.scale.y,
            o.userData.scale[2] * o.scale.z
        ],
        color: '#888888', // Cannot easily extract single color from group
    }));
}

export function clearObjects(scene) {
    objectList.forEach(o => { if (o.parent) scene.remove(o); });
    objectList = [];
    removeOutline();
    selectedObj = null;
}

// ── Collision and Boundary Constraint System ──
let roomWidth = 8;
let roomLength = 10;

export function setRoomDimensions(w, l) {
    roomWidth = w;
    roomLength = l;
}

function getOBBCorners(center, halfSize, angle) {
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);
    
    const axX = halfSize.x * cos;
    const axZ = -halfSize.x * sin;
    const azX = halfSize.z * sin;
    const azZ = halfSize.z * cos;
    
    return [
        { x: center.x - axX - azX, z: center.z - axZ - azZ },
        { x: center.x + axX - azX, z: center.z + axZ - azZ },
        { x: center.x + axX + azX, z: center.z + axZ + azZ },
        { x: center.x - axX + azX, z: center.z - axZ + azZ }
    ];
}

function getOBBAxes(halfSize, angle) {
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);
    return [
        { x: cos, z: -sin }, // local X axis in world coordinates
        { x: sin, z: cos }   // local Z axis in world coordinates
    ];
}

function projectOBB(corners, axis) {
    let min = Infinity;
    let max = -Infinity;
    for (const p of corners) {
        const dot = p.x * axis.x + p.z * axis.z;
        if (dot < min) min = dot;
        if (dot > max) max = dot;
    }
    return { min, max };
}

function checkOBBOverlap(cornersA, axesA, cornersB, axesB) {
    const axes = [...axesA, ...axesB];
    let minOverlap = Infinity;
    let translationAxis = null;

    for (const axis of axes) {
        const len = Math.sqrt(axis.x * axis.x + axis.z * axis.z);
        if (len === 0) continue;
        const normAxis = { x: axis.x / len, z: axis.z / len };

        const projA = projectOBB(cornersA, normAxis);
        const projB = projectOBB(cornersB, normAxis);

        if (projA.max < projB.min || projB.max < projA.min) {
            return null; // Separating axis found, no collision
        }

        const overlap = Math.min(projA.max, projB.max) - Math.max(projA.min, projB.min);
        if (overlap < minOverlap) {
            minOverlap = overlap;
            translationAxis = normAxis;
        }
    }

    return { axis: translationAxis, overlap: minOverlap };
}

function constrainToRoom(obj, w, l) {
    const baseS = obj.userData.scale || [1, 1, 1];
    const s = [
        baseS[0] * obj.scale.x,
        baseS[1] * obj.scale.y,
        baseS[2] * obj.scale.z
    ];
    const halfSize = { x: s[0] / 2, z: s[2] / 2 };
    const corners = getOBBCorners(obj.position, halfSize, obj.rotation.y);
    
    let minX = Infinity, maxX = -Infinity;
    let minZ = Infinity, maxZ = -Infinity;
    for (const p of corners) {
        if (p.x < minX) minX = p.x;
        if (p.x > maxX) maxX = p.x;
        if (p.z < minZ) minZ = p.z;
        if (p.z > maxZ) maxZ = p.z;
    }
    
    const halfW = w / 2;
    const halfL = l / 2;
    const margin = 0.05; // Small margin to prevent touching walls
    
    if (minX < -halfW + margin) obj.position.x += -halfW + margin - minX;
    if (maxX > halfW - margin) obj.position.x += halfW - margin - maxX;
    if (minZ < -halfL + margin) obj.position.z += -halfL + margin - minZ;
    if (maxZ > halfL - margin) obj.position.z += halfL - margin - maxZ;
}

function constrainToPartitionWalls(obj, objects) {
    const baseS = obj.userData.scale || [1, 1, 1];
    const s = [
        baseS[0] * obj.scale.x,
        baseS[1] * obj.scale.y,
        baseS[2] * obj.scale.z
    ];
    const halfSizeA = { x: s[0] / 2, z: s[2] / 2 };
    
    for (const other of objects) {
        if (other === obj) continue;
        const isPartition = other.userData && (other.userData.furnitureType === 'partition_wall' || other.userData.type === 'wall');
        if (!isPartition) continue;
        
        const baseOS = other.userData.scale || [1, 1, 1];
        const os = [
            baseOS[0] * other.scale.x,
            baseOS[1] * other.scale.y,
            baseOS[2] * other.scale.z
        ];
        const halfSizeB = { x: os[0] / 2, z: os[2] / 2 };
        
        const cornersA = getOBBCorners(obj.position, halfSizeA, obj.rotation.y);
        const axesA = getOBBAxes(halfSizeA, obj.rotation.y);
        
        const cornersB = getOBBCorners(other.position, halfSizeB, other.rotation.y);
        const axesB = getOBBAxes(halfSizeB, other.rotation.y);
        
        const collision = checkOBBOverlap(cornersA, axesA, cornersB, axesB);
        if (collision) {
            const dirX = obj.position.x - other.position.x;
            const dirZ = obj.position.z - other.position.z;
            const dot = dirX * collision.axis.x + dirZ * collision.axis.z;
            const sign = dot < 0 ? -1 : 1;
            
            // Add small buffer to prevent overlapping
            const buffer = 0.05;
            obj.position.x += collision.axis.x * (collision.overlap + buffer) * sign;
            obj.position.z += collision.axis.z * (collision.overlap + buffer) * sign;
        }
    }
}

export function constrainObjectPosition(obj, w, l, objects) {
    if (!obj || !obj.userData) return;
    const ft = obj.userData.furnitureType || '';
    
    const isWallMounted = ['mirror', 'painting', 'clock', 'curtain'].some(t => ft.includes(t));
    if (isWallMounted) return;
    
    // 1. Constrain to room boundaries
    constrainToRoom(obj, w, l);
    
    // 2. Constrain to partition walls
    constrainToPartitionWalls(obj, objects);
}

export function snapWallMountedItem(type, pos, rot, scale) {
    const ft = type || '';
    const isWallMounted = ['mirror', 'painting', 'clock', 'curtain'].some(t => ft.includes(t));
    if (!isWallMounted) return pos;

    const angle = rot ? rot[1] : 0;
    const thickness = scale ? scale[2] : 0.05;
    const offset = thickness / 2;

    const hw = roomWidth / 2;
    const hl = roomLength / 2;

    const normalAngle = Math.round(angle / (Math.PI / 2)) * (Math.PI / 2);
    const cos = Math.round(Math.cos(normalAngle));
    const sin = Math.round(Math.sin(normalAngle));

    const snappedPos = [...pos];

    if (cos === 1 && sin === 0) {
        snappedPos[2] = -hl + offset;
    }
    else if (cos === -1 && sin === 0) {
        snappedPos[2] = hl - offset;
    }
    else if (cos === 0 && sin === 1) {
        snappedPos[0] = -hw + offset;
    }
    else if (cos === 0 && sin === -1) {
        snappedPos[0] = hw - offset;
    }

    return snappedPos;
}

// ─── CANVA-STYLE SNAP ALIGNMENT ASSIST SYSTEM ───
let alignmentAssistEnabled = true;
let activeGuides = [];

export function setAlignmentAssistEnabled(enabled) {
    alignmentAssistEnabled = enabled;
    clearGuides(window.RenovaEngine ? window.RenovaEngine.scene : null);
}

export function clearGuides(scene) {
    const sc = scene || (window.RenovaEngine ? window.RenovaEngine.scene : null);
    if (!sc) return;
    activeGuides.forEach(g => sc.remove(g));
    activeGuides = [];
}

export function applyAlignmentAssist(obj, scene) {
    const sc = scene || (window.RenovaEngine ? window.RenovaEngine.scene : null);
    if (!alignmentAssistEnabled || !sc) return;
    
    // Clear old guides first
    clearGuides(sc);
    
    const snapThreshold = 0.15;
    let snappedX = false;
    let snappedZ = false;
    
    // 1. Check center of the room snapping (x = 0 or z = 0)
    if (Math.abs(obj.position.x) < snapThreshold) {
        obj.position.x = 0;
        snappedX = true;
        createGuideLine(sc, new THREE.Vector3(0, 0.02, -roomLength/2), new THREE.Vector3(0, 0.02, roomLength/2), 0xd946ef);
    }
    
    if (Math.abs(obj.position.z) < snapThreshold) {
        obj.position.z = 0;
        snappedZ = true;
        createGuideLine(sc, new THREE.Vector3(-roomWidth/2, 0.02, 0), new THREE.Vector3(roomWidth/2, 0.02, 0), 0xd946ef);
    }
    
    // 2. Check alignment with other objects
    const objs = objectList.filter(o => o.parent && o !== obj && o.userData.type !== 'wall');
    
    for (const other of objs) {
        // Snap to other object's X position
        if (!snappedX && Math.abs(obj.position.x - other.position.x) < snapThreshold) {
            obj.position.x = other.position.x;
            snappedX = true;
            createGuideLine(sc, 
                new THREE.Vector3(obj.position.x, 0.02, obj.position.z), 
                new THREE.Vector3(other.position.x, 0.02, other.position.z), 
                0x00f0ff
            );
        }
        
        // Snap to other object's Z position
        if (!snappedZ && Math.abs(obj.position.z - other.position.z) < snapThreshold) {
            obj.position.z = other.position.z;
            snappedZ = true;
            createGuideLine(sc, 
                new THREE.Vector3(obj.position.x, 0.02, obj.position.z), 
                new THREE.Vector3(other.position.x, 0.02, other.position.z), 
                0x00f0ff
            );
        }
    }
}

function createGuideLine(scene, start, end, colorHex) {
    const points = [start, end];
    const geometry = new THREE.BufferGeometry().setFromPoints(points);
    const material = new THREE.LineDashedMaterial({
        color: colorHex,
        dashSize: 0.15,
        gapSize: 0.1,
        linewidth: 2,
        depthTest: false
    });
    const line = new THREE.Line(geometry, material);
    line.computeLineDistances();
    line.renderOrder = 999;
    scene.add(line);
    activeGuides.push(line);
}

