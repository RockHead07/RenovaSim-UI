const API     = '/api/3d';
const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';

const headers = {
    'X-CSRF-TOKEN': CSRF,
    'Accept': 'application/json',
};

const fetchOpts = { headers, credentials: 'same-origin' };

export async function checkStatus() {
    try {
        const r = await fetch(`${API}/status`, fetchOpts);
        return (await r.json()).status === 'online';
    } catch { return false; }
}

export async function uploadImages(files) {
    const fd = new FormData();
    files.forEach(f => fd.append('images', f));
    const r = await fetch(`${API}/upload-images`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        credentials: 'same-origin',
        body: fd,
    });
    return r.json();
}

export async function getCatalog() {
    const r = await fetch(`${API}/furniture`, fetchOpts);
    return (await r.json()).catalog;
}

export async function getTemplates() {
    const r = await fetch(`${API}/templates`, fetchOpts);
    return (await r.json()).templates;
}

export async function getPaintColors() {
    const r = await fetch(`${API}/paint-colors`, fetchOpts);
    return (await r.json()).colors;
}

export async function getRoom(roomId) {
    const r = await fetch(`${API}/rooms/${roomId}`, fetchOpts);
    if (!r.ok) return null;
    return r.json();
}

export async function saveRoom(roomId, data) {
    const r = await fetch(`${API}/rooms/${roomId}/save`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data),
    });
    return r.json();
}

export async function applyTemplate(roomId, templateId) {
    const r = await fetch(`${API}/rooms/${roomId}/apply-template`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ template_id: templateId }),
    });
    return r.json();
}

export async function updateWall(roomId, wallId, color) {
    const r = await fetch(`${API}/rooms/${roomId}/update-wall`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ wall_id: wallId, color }),
    });
    return r.json();
}

export async function saveThumbnail(roomId, thumbnailDataUrl) {
    const r = await fetch(`${API}/rooms/${roomId}/thumbnail`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ thumbnail: thumbnailDataUrl }),
    });
    return r.json();
}

export async function getProjects() {
    const r = await fetch(`${API}/projects`, fetchOpts);
    return (await r.json()).projects;
}

export async function deleteRoom(roomId) {
    const r = await fetch(`${API}/rooms/${roomId}`, {
        method: 'DELETE',
        ...fetchOpts,
    });
    return r.json();
}

export async function renameRoom(roomId, name) {
    const r = await fetch(`${API}/rooms/${roomId}/rename`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ name }),
    });
    return r.json();
}
