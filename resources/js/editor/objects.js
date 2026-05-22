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
    } else if (type.includes('tv') || type.includes('monitor')) {
        // Slim base
        const base = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.18, s[0]*0.22, 0.02, 16), darkMat);
        base.position.y = 0.01;
        // Thin neck
        const neck = new THREE.Mesh(new THREE.BoxGeometry(0.04, s[1]*0.25, 0.04), darkMat);
        neck.position.y = s[1]*0.13;
        // Screen panel (very thin)
        const panel = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.65, 0.03), darkMat);
        panel.position.set(0, s[1]*0.6, 0);
        // Bezel
        const bezel = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.96, s[1]*0.61, 0.005),
            new THREE.MeshStandardMaterial({color: 0x111118, metalness: 0.95, roughness: 0.05, emissive: 0x0a0a1a, emissiveIntensity: 0.3}));
        bezel.position.set(0, s[1]*0.6, 0.018);
        group.add(base, neck, panel, bezel);
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

    // FIX FLOATING: original system pos[1] was s[1]/2. 
    // Since our wrapper floor is at Y=0, we must subtract s[1]/2 to place it on the floor.
    wrapper.position.set(pos[0], pos[1] - s[1]/2, pos[2]);
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
}

export function endDrag() { isDragging = false; }
export function getDragging() { return isDragging; }

export function serializeObjects() {
    return objectList.filter(o => o.parent).map(o => ({
        id: o.userData.id, type: o.userData.furnitureType, name: o.userData.name,
        category: o.userData.category,
        position: [o.position.x, o.position.y + (o.userData.scale[1]/2), o.position.z],
        rotation: [o.rotation.x, o.rotation.y, o.rotation.z],
        scale: o.userData.scale,
        color: '#888888', // Cannot easily extract single color from group
    }));
}

export function clearObjects(scene) {
    objectList.forEach(o => { if (o.parent) scene.remove(o); });
    objectList = [];
    removeOutline();
    selectedObj = null;
}
