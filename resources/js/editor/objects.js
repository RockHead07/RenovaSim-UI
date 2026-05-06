import * as THREE from 'three';

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

export function createFurniture(type, catalog, pos, rot, id) {
    const info = catalog[type];
    if (!info) return null;
    const s = info.scale || [1, 1, 1];
    
    const group = new THREE.Group();
    const color = new THREE.Color(info.color || '#888888');
    const mat = new THREE.MeshStandardMaterial({ color, roughness: 0.8, metalness: 0.1 });
    const woodMat = new THREE.MeshStandardMaterial({ color: 0x8b5a2b, roughness: 0.9, metalness: 0.0 });
    const darkMat = new THREE.MeshStandardMaterial({ color: 0x222222, roughness: 0.4, metalness: 0.8 });
    
    // Procedural generation based on keywords
    if (type.includes('sofa') || type.includes('chair')) {
        const base = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.3, s[2]), mat);
        base.position.y = s[1]*0.15;
        // Cushions
        const cush = new THREE.Mesh(new THREE.CylinderGeometry(s[2]*0.4, s[2]*0.4, s[0]*0.9, 16), new THREE.MeshStandardMaterial({color: 0xeeeeee, roughness: 0.9}));
        cush.rotation.z = Math.PI / 2;
        cush.scale.set(1, 1, 0.4); // flatten
        cush.position.set(0, s[1]*0.35, s[2]*0.1);
        
        const back = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.6, s[2]*0.2), mat);
        back.position.set(0, s[1]*0.6, -s[2]*0.4);
        
        if (type.includes('sofa')) {
            const armL = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.15, s[1]*0.5, s[2]), mat);
            armL.position.set(-s[0]*0.425, s[1]*0.35, 0);
            const armR = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.15, s[1]*0.5, s[2]), mat);
            armR.position.set(s[0]*0.425, s[1]*0.35, 0);
            group.add(armL, armR);
        }
        group.add(base, cush, back);
    } else if (type.includes('table') || type.includes('desk')) {
        const top = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.05, s[2]), woodMat);
        top.position.y = s[1] - 0.025;
        const r = 0.04;
        const l1 = new THREE.Mesh(new THREE.CylinderGeometry(r, r*0.5, s[1]-0.05, 8), darkMat); l1.position.set(-s[0]/2+r*2, (s[1]-0.05)/2, -s[2]/2+r*2);
        const l2 = new THREE.Mesh(new THREE.CylinderGeometry(r, r*0.5, s[1]-0.05, 8), darkMat); l2.position.set(s[0]/2-r*2, (s[1]-0.05)/2, -s[2]/2+r*2);
        const l3 = new THREE.Mesh(new THREE.CylinderGeometry(r, r*0.5, s[1]-0.05, 8), darkMat); l3.position.set(-s[0]/2+r*2, (s[1]-0.05)/2, s[2]/2-r*2);
        const l4 = new THREE.Mesh(new THREE.CylinderGeometry(r, r*0.5, s[1]-0.05, 8), darkMat); l4.position.set(s[0]/2-r*2, (s[1]-0.05)/2, s[2]/2-r*2);
        group.add(top, l1, l2, l3, l4);
    } else if (type.includes('bed')) {
        const frame = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.25, s[2]), woodMat);
        frame.position.y = 0.125;
        const mattress = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.95, 0.25, s[2]*0.95), new THREE.MeshStandardMaterial({color: 0xffffff, roughness: 0.9}));
        mattress.position.y = 0.375;
        const headboard = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], 0.1), woodMat);
        headboard.position.set(0, s[1]/2, -s[2]/2 + 0.05);
        const pillow1 = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.4, 0.1, 0.3), new THREE.MeshStandardMaterial({color: 0xf0f0f0}));
        pillow1.position.set(-s[0]*0.25, 0.55, -s[2]/2 + 0.3);
        pillow1.rotation.x = 0.15;
        const pillow2 = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.4, 0.1, 0.3), new THREE.MeshStandardMaterial({color: 0xf0f0f0}));
        pillow2.position.set(s[0]*0.25, 0.55, -s[2]/2 + 0.3);
        pillow2.rotation.x = 0.15;
        group.add(frame, mattress, headboard, pillow1, pillow2);
    } else if (type.includes('tv') || type.includes('monitor')) {
        const stand = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.4, 0.04, s[2]*0.6), darkMat);
        stand.position.y = 0.02;
        const neck = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, s[1]*0.3), darkMat);
        neck.position.y = s[1]*0.15;
        const back = new THREE.Mesh(new THREE.BoxGeometry(s[0]*0.95, s[1]*0.65, 0.06), darkMat);
        back.position.set(0, s[1]*0.65, 0.03);
        const screen = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1]*0.7, 0.02), new THREE.MeshStandardMaterial({color: 0x050505, metalness: 0.9, roughness: 0.1}));
        screen.position.set(0, s[1]*0.65, 0.07);
        group.add(stand, neck, back, screen);
    } else if (type.includes('plant')) {
        const pot = new THREE.Mesh(new THREE.CylinderGeometry(s[0]*0.35, s[0]*0.25, s[1]*0.3, 16), new THREE.MeshStandardMaterial({color: 0xd4c4b7}));
        pot.position.y = s[1]*0.15;
        const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, s[1]*0.5), new THREE.MeshStandardMaterial({color: 0x4a3b2c}));
        trunk.position.y = s[1]*0.4;
        const leaves1 = new THREE.Mesh(new THREE.SphereGeometry(s[0]*0.4, 16, 16), new THREE.MeshStandardMaterial({color: 0x2e7d32, roughness: 0.8}));
        leaves1.position.y = s[1]*0.6; leaves1.scale.y = 1.3;
        const leaves2 = new THREE.Mesh(new THREE.SphereGeometry(s[0]*0.3, 16, 16), new THREE.MeshStandardMaterial({color: 0x388e3c, roughness: 0.8}));
        leaves2.position.set(0.1, s[1]*0.75, 0.1); leaves2.scale.y = 1.2;
        group.add(pot, trunk, leaves1, leaves2);
    } else if (type.includes('rug') || type.includes('carpet')) {
        const rug = new THREE.Mesh(new THREE.BoxGeometry(s[0], 0.02, s[2]), new THREE.MeshStandardMaterial({color, roughness: 1.0}));
        rug.position.y = 0.01;
        group.add(rug);
    } else if (type.includes('wardrobe') || type.includes('cabinet')) {
        const body = new THREE.Mesh(new THREE.BoxGeometry(s[0], s[1], s[2]), woodMat);
        body.position.y = s[1]/2;
        const handle1 = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.4, 0.02), darkMat);
        handle1.position.set(-0.05, s[1]/2, s[2]/2 + 0.01);
        const handle2 = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.4, 0.02), darkMat);
        handle2.position.set(0.05, s[1]/2, s[2]/2 + 0.01);
        group.add(body, handle1, handle2);
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
