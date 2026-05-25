const API = 'http://localhost:5000/api';

export async function checkStatus() {
    try {
        const r = await fetch(`${API}/status`);
        return (await r.json()).status === 'online';
    } catch { return false; }
}

export async function uploadImages(files) {
    const fd = new FormData();
    files.forEach(f => fd.append('images', f));
    const r = await fetch(`${API}/upload-images`, { method: 'POST', body: fd });
    return r.json();
}

export async function getCatalog() {
    const r = await fetch(`${API}/furniture`);
    return (await r.json()).catalog;
}

export async function getTemplates() {
    const r = await fetch(`${API}/templates`);
    return (await r.json()).templates;
}

export async function getPaintColors() {
    const r = await fetch(`${API}/paint-colors`);
    return (await r.json()).colors;
}

export async function getRoom(roomId) {
    const r = await fetch(`${API}/rooms/${roomId}`);
    if (!r.ok) return null;
    return r.json();
}

export async function saveRoom(roomId, data) {
    const r = await fetch(`${API}/rooms/${roomId}/save`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    return r.json();
}

export async function applyTemplate(roomId, templateId) {
    const r = await fetch(`${API}/rooms/${roomId}/apply-template`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ template_id: templateId }),
    });
    return r.json();
}

export async function updateWall(roomId, wallId, color) {
    const r = await fetch(`${API}/rooms/${roomId}/update-wall`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ wall_id: wallId, color }),
    });
    return r.json();
}

export async function saveThumbnail(roomId, thumbnailDataUrl) {
    const r = await fetch(`${API}/rooms/${roomId}/thumbnail`, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ thumbnail: thumbnailDataUrl }),
    });
    return r.json();
}

export async function getProjects() {
    const r = await fetch(`${API}/projects`);
    return (await r.json()).projects;
}
