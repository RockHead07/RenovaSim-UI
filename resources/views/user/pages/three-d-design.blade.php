{{-- pages.three-d-design — 3D Design Modeling Gallery --}}
<x-user::layouts.dashboard title="RenovaSim — 3D Design Modeling">
    <div class="flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-['Playfair_Display'] italic text-xl text-card-foreground">
                    3D Design Modeling
                </h1>
                <p class="font-['DM_Sans'] text-[11px] uppercase tracking-[0.15em] text-muted-foreground mt-0.5">
                    Buat, edit & eksplorasi desain renovasi dalam tiga dimensi
                </p>
            </div>
            <a href="{{ route('user.editor') }}"
               class="inline-flex items-center gap-2 bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-4 py-2.5 hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New 3D Design
            </a>
        </div>

        {{-- Loading state --}}
        <div id="projects-loading"
             class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] p-10 min-h-[300px] flex flex-col items-center justify-center">
            <div class="w-8 h-8 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
            <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-4">Loading your designs...</p>
        </div>

        {{-- Empty state --}}
        <div id="projects-empty"
             class="bg-card rounded-2xl shadow-[0_1px_4px_rgba(0,0,0,0.06)] py-16 px-6 flex-col items-center justify-center text-center hidden">
            <div class="w-14 h-14 rounded-2xl bg-muted flex items-center justify-center mb-4 mx-auto">
                <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line stroke-linecap="round" x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
            </div>
            <p class="font-['Playfair_Display'] italic text-lg text-card-foreground">Belum ada desain</p>
            <p class="font-['DM_Sans'] text-sm text-muted-foreground mt-1 max-w-xs mx-auto leading-relaxed">
                Mulai dengan membuat desain ruangan 3D pertamamu.
            </p>
            <a href="{{ route('user.editor') }}"
               class="mt-5 inline-flex items-center gap-2 bg-primary text-primary-foreground font-['DM_Sans'] font-medium text-sm rounded-xl px-5 py-2.5 hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Desain Pertama
            </a>
        </div>

        {{-- Grid (populated by JS) --}}
        <div id="projects-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 hidden"></div>

        {{-- Server status --}}
        <div class="flex items-center gap-2">
            <span id="server-dot" class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
            <span id="server-status" class="font-['DM_Sans'] text-[11px] text-muted-foreground">Checking server...</span>
        </div>

    </div>

    <script>
    (function() {
        const API       = 'http://localhost:5000/api';
        const USER_ID   = '{{ auth()->id() }}';
        const gridEl    = document.getElementById('projects-grid');
        const loadingEl = document.getElementById('projects-loading');
        const emptyEl   = document.getElementById('projects-empty');
        const dotEl     = document.getElementById('server-dot');
        const statusEl  = document.getElementById('server-status');

        /* ── SVG icon strings (no emoji) ─────────────────────── */
        const SVG = {
            box: `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`,
            ruler: `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21.3 8.7 8.7 21.3c-.5.5-1.1.7-1.7.7s-1.2-.2-1.7-.7L2.7 19c-.9-.9-.9-2.5 0-3.4L15.3 2.7c.9-.9 2.5-.9 3.4 0l2.6 2.6c.9.9.9 2.5 0 3.4z"/><path d="m7.5 10.5 2 2"/><path d="m10.5 7.5 2 2"/><path d="m13.5 4.5 2 2"/></svg>`,
            calendar: `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
            edit: `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
            trash: `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>`,
            cube: `<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="opacity:.3;color:var(--muted-foreground)"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`,
        };

        const ROOM_COLORS = {
            living:   'rgba(59,130,246,0.85)',
            bedroom:  'rgba(139,92,246,0.85)',
            kitchen:  'rgba(245,158,11,0.85)',
            bathroom: 'rgba(20,184,166,0.85)',
            office:   'rgba(107,114,128,0.85)',
        };

        /* ── API calls ────────────────────────────────────────── */
        async function loadProjects() {
            try {
                const r = await fetch(API + '/status');
                const s = await r.json();
                if (s.status === 'online') {
                    dotEl.className = 'w-2 h-2 rounded-full bg-green-400 shrink-0';
                    statusEl.textContent = 'Server online — v' + (s.version || '2.0');
                }
            } catch(e) {
                dotEl.className = 'w-2 h-2 rounded-full bg-yellow-400 shrink-0';
                statusEl.textContent = 'RAI server offline — jalankan python app_server.py';
                loadingEl.classList.add('hidden');
                showEmpty();
                return;
            }

            try {
                const r    = await fetch(API + '/projects?user_id=' + USER_ID);
                const data = await r.json();
                loadingEl.classList.add('hidden');

                if (!data.projects || data.projects.length === 0) { showEmpty(); return; }

                gridEl.classList.remove('hidden');
                renderProjects(data.projects);
            } catch(e) {
                loadingEl.classList.add('hidden');
                showEmpty();
            }
        }

        function showEmpty() {
            emptyEl.classList.remove('hidden');
            emptyEl.style.display = 'flex';
        }

        /* ── Card renderer ────────────────────────────────────── */
        function renderProjects(projects) {
            gridEl.innerHTML = '';
            projects.forEach(p => {
                const roomType   = p.recommended_type || 'living';
                const badgeColor = ROOM_COLORS[roomType] || ROOM_COLORS.living;
                const date       = p.updated_at || p.created_at;
                const dateStr    = date
                    ? new Date(date).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})
                    : '—';

                const thumbHtml = p.thumbnail
                    ? `<img src="${p.thumbnail}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;">`
                    : `<div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">${SVG.cube}</div>`;

                const card = document.createElement('div');
                card.style.cssText = 'background:var(--card);border-radius:16px;overflow:hidden;border:1px solid var(--border);cursor:pointer;transition:box-shadow .25s ease, transform .25s ease;';
                card.onmouseenter = () => { card.style.boxShadow = '0 8px 24px rgba(0,0,0,0.10)'; card.style.transform = 'translateY(-2px)'; };
                card.onmouseleave = () => { card.style.boxShadow = ''; card.style.transform = ''; };

                card.innerHTML = `
                    <div style="height:180px;background:linear-gradient(135deg,#1a1d27,#2a2e3d);position:relative;overflow:hidden;">
                        ${thumbHtml}
                        <span style="position:absolute;top:10px;left:10px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;text-transform:capitalize;color:#fff;background:${badgeColor};font-family:'DM Sans',sans-serif;letter-spacing:.02em;">${roomType}</span>
                        <span style="position:absolute;top:10px;right:10px;padding:3px 9px;border-radius:20px;font-size:11px;background:rgba(0,0,0,0.55);color:#fff;backdrop-filter:blur(8px);display:inline-flex;align-items:center;gap:5px;font-family:'DM Sans',sans-serif;">${SVG.box} ${p.object_count} objects</span>
                    </div>
                    <div style="padding:14px 16px 16px;">
                        <p style="font-family:'Playfair Display',serif;font-size:16px;font-style:italic;color:var(--card-foreground);margin:0 0 5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${p.name || 'Untitled Room'}</p>
                        <div style="display:flex;align-items:center;gap:12px;font-family:'DM Sans',sans-serif;font-size:12px;color:var(--muted-foreground);margin-bottom:10px;">
                            <span style="display:inline-flex;align-items:center;gap:4px;">${SVG.ruler} ${p.width}m × ${p.length}m</span>
                            <span style="display:inline-flex;align-items:center;gap:4px;">${SVG.calendar} ${dateStr}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
                            <div style="display:flex;gap:4px;">
                                <div style="width:13px;height:13px;border-radius:3px;background:${p.wall_color || '#eee'};border:1px solid rgba(0,0,0,0.1);" title="Wall color"></div>
                                <div style="width:13px;height:13px;border-radius:3px;background:${p.floor_color || '#ccc'};border:1px solid rgba(0,0,0,0.1);" title="Floor color"></div>
                            </div>
                            <span style="font-family:'DM Sans',sans-serif;font-size:12px;color:var(--muted-foreground);">${p.status || 'generated'}</span>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <a href="/user/editor/${p.id}"
                               style="flex:1;padding:8px 14px;border-radius:12px;background:hsl(var(--primary));color:hsl(var(--primary-foreground));font-size:13px;font-weight:500;cursor:pointer;text-align:center;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-family:'DM Sans',sans-serif;">
                                ${SVG.edit} Open Editor
                            </a>
                            <button onclick="event.stopPropagation(); deleteProject('${p.id}')"
                                    title="Hapus desain"
                                    style="padding:8px 12px;border-radius:12px;background:var(--muted);color:var(--muted-foreground);border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;"
                                    onmouseenter="this.style.background='hsl(0 84% 95%)';this.style.color='hsl(0 72% 51%)';"
                                    onmouseleave="this.style.background='var(--muted)';this.style.color='var(--muted-foreground)';">
                                ${SVG.trash}
                            </button>
                        </div>
                    </div>
                `;

                card.addEventListener('click', e => {
                    if (e.target.closest('button') || e.target.closest('a')) return;
                    window.location.href = '/user/editor/' + p.id;
                });

                gridEl.appendChild(card);
            });
        }

        window.deleteProject = async function(id) {
            if (!confirm('Hapus desain ini? Tindakan ini tidak dapat dibatalkan.')) return;
            try {
                await fetch(API + '/rooms/' + id + '?user_id=' + USER_ID, { method: 'DELETE' });
                loadProjects();
            } catch(e) { alert('Gagal menghapus desain.'); }
        };

        loadProjects();
    })();
    </script>
</x-user::layouts.dashboard>
