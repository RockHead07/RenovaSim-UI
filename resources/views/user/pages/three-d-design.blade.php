{{-- pages.three-d-design — 3D Design Modeling Gallery --}}
<x-user::layouts.dashboard title="RenovaSim — 3D Design Modeling">
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-['Playfair_Display'] italic text-[28px] text-secondary">3D Design Modeling House</h1>
                <p class="text-sm text-muted-foreground mt-1">
                    Create, edit & explore your renovation designs in three dimensions.
                </p>
            </div>
            <a href="{{ route('user.editor') }}"
               class="inline-flex items-center gap-2 bg-primary text-primary-foreground rounded-full px-6 py-3 text-sm font-medium hover:opacity-90 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                <x-lucide-plus class="w-4 h-4" />
                New 3D Design
            </a>
        </div>

        {{-- Project Gallery --}}
        <div id="projects-loading" class="bg-card rounded-[24px] shadow-sm p-10 min-h-[300px] flex flex-col items-center justify-center">
            <div class="w-8 h-8 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
            <p class="text-sm text-muted-foreground mt-4">Loading your designs...</p>
        </div>

        <div id="projects-empty" class="bg-card rounded-[24px] shadow-sm p-10 min-h-[380px] flex-col items-center justify-center text-center gap-4 hidden">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <x-lucide-box class="w-7 h-7 text-primary" />
            </div>
            <h2 class="font-['Playfair_Display'] text-2xl text-secondary">No Designs Yet</h2>
            <p class="text-sm text-muted-foreground max-w-md mx-auto mt-2">
                Start by creating your first 3D room design. Upload photos of your room and our AI will generate an interactive 3D model.
            </p>
            <a href="{{ route('user.editor') }}"
               class="mt-4 inline-flex items-center gap-2 bg-primary text-primary-foreground rounded-full px-6 py-3 text-sm font-medium hover:opacity-90 transition-opacity">
                <x-lucide-sparkles class="w-4 h-4" />
                Create First Design
            </a>
        </div>

        <div id="projects-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 hidden">
            {{-- Populated by JavaScript --}}
        </div>

        {{-- Server status --}}
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
            <span id="server-dot" class="w-2 h-2 rounded-full bg-red-400"></span>
            <span id="server-status">Checking server...</span>
        </div>
    </div>

    <style>
        .project-card {
            background: var(--card);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border, rgba(0,0,0,0.06));
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .project-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            border-color: hsl(var(--primary));
        }
        .project-thumb {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #1a1d27, #2a2e3d);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .project-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .project-thumb .thumb-placeholder {
            font-size: 48px;
            opacity: 0.5;
        }
        .project-thumb .room-type-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            color: white;
        }
        .room-type-badge.living { background: rgba(59,130,246,0.85); }
        .room-type-badge.bedroom { background: rgba(139,92,246,0.85); }
        .room-type-badge.kitchen { background: rgba(245,158,11,0.85); }
        .room-type-badge.bathroom { background: rgba(20,184,166,0.85); }
        .room-type-badge.office { background: rgba(107,114,128,0.85); }
        .project-thumb .obj-count {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            background: rgba(0,0,0,0.6);
            color: white;
            backdrop-filter: blur(8px);
        }
        .project-body {
            padding: 16px 20px 20px;
        }
        .project-name {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            font-weight: 600;
            color: var(--secondary, #333);
            margin-bottom: 4px;
        }
        .project-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            color: var(--muted-foreground, #888);
            margin-bottom: 12px;
        }
        .project-actions {
            display: flex;
            gap: 8px;
        }
        .project-actions .btn-open {
            flex: 1;
            padding: 8px 16px;
            border-radius: 12px;
            background: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
            border: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .project-actions .btn-open:hover { opacity: 0.85; }
        .project-actions .btn-secondary {
            padding: 8px 12px;
            border-radius: 12px;
            background: var(--muted, #f3f3f3);
            color: var(--muted-foreground, #666);
            border: none;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .project-actions .btn-secondary:hover {
            background: var(--border, #e5e5e5);
        }
        .color-preview {
            display: flex;
            gap: 4px;
        }
        .color-preview .swatch {
            width: 14px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>

    <script>
    (function() {
        const API = 'http://localhost:5000/api';
        const gridEl = document.getElementById('projects-grid');
        const loadingEl = document.getElementById('projects-loading');
        const emptyEl = document.getElementById('projects-empty');
        const dotEl = document.getElementById('server-dot');
        const statusEl = document.getElementById('server-status');

        const ROOM_ICONS = {
            living: '🛋️', bedroom: '🛏️', kitchen: '🍽️',
            bathroom: '🛁', office: '🖥️'
        };

        async function loadProjects() {
            try {
                const r = await fetch(API + '/status');
                const s = await r.json();
                if (s.status === 'online') {
                    dotEl.classList.remove('bg-red-400');
                    dotEl.classList.add('bg-green-400');
                    statusEl.textContent = 'Server online — v' + (s.version || '2.0');
                }
            } catch(e) {
                dotEl.classList.remove('bg-red-400');
                dotEl.classList.add('bg-yellow-400');
                statusEl.textContent = 'Server offline — start python-editor/app_server.py';
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                emptyEl.style.display = 'flex';
                return;
            }

            try {
                const r = await fetch(API + '/projects');
                const data = await r.json();
                loadingEl.classList.add('hidden');

                if (!data.projects || data.projects.length === 0) {
                    emptyEl.classList.remove('hidden');
                    emptyEl.style.display = 'flex';
                    return;
                }

                gridEl.classList.remove('hidden');
                renderProjects(data.projects);
            } catch(e) {
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                emptyEl.style.display = 'flex';
            }
        }

        function renderProjects(projects) {
            gridEl.innerHTML = '';
            projects.forEach(p => {
                const roomType = p.recommended_type || 'living';
                const icon = ROOM_ICONS[roomType] || '🏠';
                const date = p.updated_at || p.created_at;
                const dateStr = date ? new Date(date).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'}) : 'Unknown';
                const dims = `${p.width}m × ${p.length}m`;

                const thumbHtml = p.thumbnail
                    ? `<img src="${p.thumbnail}" alt="${p.name}" />`
                    : `<span class="thumb-placeholder">${icon}</span>`;

                const card = document.createElement('div');
                card.className = 'project-card';
                card.innerHTML = `
                    <div class="project-thumb">
                        ${thumbHtml}
                        <span class="room-type-badge ${roomType}">${roomType}</span>
                        <span class="obj-count">📦 ${p.object_count} objects</span>
                    </div>
                    <div class="project-body">
                        <div class="project-name">${p.name || 'Untitled Room'}</div>
                        <div class="project-meta">
                            <span>📐 ${dims}</span>
                            <span>📅 ${dateStr}</span>
                        </div>
                        <div class="project-meta">
                            <div class="color-preview">
                                <div class="swatch" style="background:${p.wall_color}" title="Wall"></div>
                                <div class="swatch" style="background:${p.floor_color}" title="Floor"></div>
                            </div>
                            <span>${p.status || 'saved'}</span>
                        </div>
                        <div class="project-actions">
                            <a href="/user/editor/${p.id}" class="btn-open">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Open Editor
                            </a>
                            <button class="btn-secondary" title="Delete" onclick="event.stopPropagation(); deleteProject('${p.id}')">🗑</button>
                        </div>
                    </div>
                `;
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.project-actions')) return;
                    window.location.href = '/user/editor/' + p.id;
                });
                gridEl.appendChild(card);
            });
        }

        window.deleteProject = async function(id) {
            if (!confirm('Delete this project? This cannot be undone.')) return;
            try {
                await fetch(API + '/rooms/' + id, { method: 'DELETE' });
                loadProjects();
            } catch(e) { alert('Failed to delete'); }
        };

        loadProjects();
    })();
    </script>
</x-user::layouts.dashboard>
