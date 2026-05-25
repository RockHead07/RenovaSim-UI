<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RenovaSim — 3D Room Editor</title>
    <meta name="description" content="3D Room Editor - Upload room photos and transform them into interactive 3D spaces">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/editor.css', 'resources/js/room-editor.js'])
</head>
<body class="editor-page" data-project-id="{{ $projectId ?? '' }}">

    <!-- ═══ TOOLBAR ═══ -->
    <div class="editor-toolbar">
        <div class="toolbar-left">
            <div class="toolbar-logo">
                <div class="logo-icon">R</div>
                <span>RenovaSim</span>
            </div>
            <div class="toolbar-divider"></div>
            <button class="toolbar-btn" onclick="document.getElementById('upload-overlay').style.display='flex'" title="New Room">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                New
            </button>
            <button class="toolbar-btn" onclick="RenovaEditor.saveProject()" title="Save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
                Save
            </button>
        </div>

        <div class="toolbar-center">
            <div class="mode-toggle">
                <button id="mode-build" class="mode-btn active" onclick="RenovaEditor.switchMode('build')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    Build
                </button>
                <button id="mode-explore" class="mode-btn" onclick="RenovaEditor.switchMode('explore')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polygon points="16.24,7.76 14.12,14.12 7.76,16.24 9.88,9.88"/></svg>
                    Explore
                </button>
            </div>
        </div>

        <div class="toolbar-right">
            <span id="room-info" style="font-size:12px;color:var(--editor-text-muted);">No room loaded</span>
            <div class="toolbar-divider"></div>
            <a href="/user/3d" class="toolbar-btn" title="Back to 3D Designs">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                My Designs
            </a>
        </div>
    </div>

    <!-- ═══ 3D CANVAS ═══ -->
    <div id="editor-canvas"></div>

    <!-- ═══ SIDE PANEL ═══ -->
    <div id="side-panel" class="side-panel">
        <div class="panel-tabs">
            <button class="panel-tab active" onclick="switchTab(this,'tab-assets')">Assets</button>
            <button class="panel-tab" onclick="switchTab(this,'tab-props')">Properties</button>
            <button class="panel-tab" onclick="switchTab(this,'tab-templates')">Templates</button>
            <button class="panel-tab" onclick="switchTab(this,'tab-paint')">Paint</button>
            <button class="panel-tab" onclick="switchTab(this,'tab-room-size')">📐 Size</button>
        </div>

        <!-- Assets Tab -->
        <div id="tab-assets" class="panel-content" style="display:block;">
            <button class="toolbar-btn" style="width:100%;justify-content:center;margin-bottom:12px;border-color:var(--editor-accent);color:var(--editor-accent);font-weight:600;" onclick="RenovaEditor.startWallDraw()">
                🧱 Draw Partition Wall
            </button>
            <div class="property-label" style="margin-bottom:8px;">Scene Objects</div>
            <div id="scene-objects" style="margin-bottom:16px;">
                <p style="color:var(--editor-text-muted);font-size:12px;padding:8px;">No objects in scene</p>
            </div>
            <div class="property-label" style="margin-bottom:8px;">Furniture Catalog</div>
            <div class="category-filter">
                <button class="category-chip active" onclick="filterCatalog(this,'all')">All</button>
                <button class="category-chip" onclick="filterCatalog(this,'living')">Living</button>
                <button class="category-chip" onclick="filterCatalog(this,'bedroom')">Bedroom</button>
                <button class="category-chip" onclick="filterCatalog(this,'kitchen')">Kitchen</button>
                <button class="category-chip" onclick="filterCatalog(this,'bathroom')">Bath</button>
                <button class="category-chip" onclick="filterCatalog(this,'decor')">Decor</button>
            </div>
            <div id="catalog-grid" class="asset-grid"></div>
        </div>

        <!-- Properties Tab -->
        <div id="tab-props" class="panel-content" style="display:none;">
            <div id="props-content">
                <p style="color:var(--editor-text-muted);font-size:13px;text-align:center;padding:20px;">Click an object to select it</p>
            </div>
        </div>

        <!-- Templates Tab -->
        <div id="tab-templates" class="panel-content" style="display:none;">
            <div class="property-label" style="margin-bottom:8px;">Room Templates</div>
            <div id="templates-list"></div>
            <div id="recommendations" style="margin-top:16px;"></div>
        </div>

        <!-- Paint Tab -->
        <div id="tab-paint" class="panel-content" style="display:none;">
            <div class="property-label" style="margin-bottom:12px;">Wall Paint Colors</div>
            <div id="paint-grid" class="color-grid"></div>
            <div style="margin-top:16px;">
                <div class="property-label" style="margin-bottom:8px;">Custom Color</div>
                <input type="color" value="#f5f0eb" style="width:100%;height:40px;border:1px solid var(--editor-border);border-radius:var(--editor-radius-sm);background:var(--editor-bg);cursor:pointer;" onchange="RenovaEditor.paintWall(this.value)">
            </div>
        </div>

        <!-- Room Size Tab -->
        <div id="tab-room-size" class="panel-content" style="display:none;">
            <div class="property-label" style="margin-bottom:12px;">📐 Ukuran Ruangan (cm)</div>
            <div class="property-group">
                <div class="property-row" style="margin-bottom:10px;">
                    <label style="min-width:60px;font-size:13px;color:var(--editor-text-muted);">Lebar</label>
                    <input id="room-width-cm" class="property-input" type="number" min="100" max="2000" step="10" value="800" style="flex:1;">
                    <span style="font-size:11px;color:var(--editor-text-muted);margin-left:4px;">cm</span>
                </div>
                <div class="property-row" style="margin-bottom:10px;">
                    <label style="min-width:60px;font-size:13px;color:var(--editor-text-muted);">Panjang</label>
                    <input id="room-length-cm" class="property-input" type="number" min="100" max="2000" step="10" value="1000" style="flex:1;">
                    <span style="font-size:11px;color:var(--editor-text-muted);margin-left:4px;">cm</span>
                </div>
                <div class="property-row" style="margin-bottom:10px;">
                    <label style="min-width:60px;font-size:13px;color:var(--editor-text-muted);">Tinggi</label>
                    <input id="room-height-cm" class="property-input" type="number" min="200" max="600" step="10" value="320" style="flex:1;">
                    <span style="font-size:11px;color:var(--editor-text-muted);margin-left:4px;">cm</span>
                </div>
            </div>
            <button class="toolbar-btn" style="width:100%;justify-content:center;margin-top:8px;border-color:var(--editor-accent);color:var(--editor-accent);font-weight:600;" onclick="RenovaEditor.updateRoomSize()">
                ✅ Terapkan Ukuran
            </button>
            <div style="margin-top:14px;padding:10px;background:var(--editor-surface);border-radius:var(--editor-radius-sm);border:1px solid var(--editor-border);">
                <div style="font-size:11px;color:var(--editor-text-muted);line-height:1.5;">
                    💡 <strong>Tips:</strong> Masukkan ukuran ruangan dalam satuan sentimeter (cm). Contoh: 400 cm = 4 meter. Furniture yang sudah ada akan otomatis disesuaikan agar tetap di dalam ruangan.
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ EXPLORE HUD ═══ -->
    <div id="explore-hud" class="explore-hud" style="display:none;">
        <button class="hud-btn" onclick="RenovaEditor.switchMode('build')" title="Exit Explore">✕</button>
        <button class="hud-btn" id="explore-catalog-btn" onclick="toggleExploreCatalog()" title="Catalog (C)" style="width:auto;padding:0 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;">
            <span>➕</span> Catalog <span style="font-size:10px;opacity:0.6;">[C]</span>
        </button>
        <div style="display:flex;align-items:center;gap:4px;color:var(--editor-text-dim);font-size:12px;padding:0 12px;">
            <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;">W</kbd>
            <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;">A</kbd>
            <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;">S</kbd>
            <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;">D</kbd>
            <span style="margin-left:4px;">Move</span>
        </div>
        <div style="color:var(--editor-text-dim);font-size:12px;padding:0 8px;display:flex;align-items:center;">
            🖱 Grab/Drop | <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;margin:0 4px;">R</kbd> Rotate | <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;margin:0 4px;">Space</kbd> Jump | <kbd style="padding:2px 6px;background:var(--editor-bg);border:1px solid var(--editor-border);border-radius:4px;font-size:11px;margin:0 4px;">C</kbd> Catalog
        </div>
    </div>

    <!-- ═══ EXPLORE CATALOG OVERLAY (Glassmorphic) ═══ -->
    <div id="explore-catalog" class="explore-catalog" style="display:none;">
        <div class="explore-catalog-content">
            <div class="explore-catalog-header">
                <h3>📦 Add Furniture</h3>
                <button class="close-btn" onclick="toggleExploreCatalog()">✕</button>
            </div>
            <div class="explore-catalog-tabs">
                <button class="cat-tab active" onclick="filterExploreCatalog('all')">All</button>
                <button class="cat-tab" onclick="filterExploreCatalog('living')">Living</button>
                <button class="cat-tab" onclick="filterExploreCatalog('bedroom')">Bedroom</button>
                <button class="cat-tab" onclick="filterExploreCatalog('kitchen')">Kitchen</button>
                <button class="cat-tab" onclick="filterExploreCatalog('bathroom')">Bath</button>
                <button class="cat-tab" onclick="filterExploreCatalog('decor')">Decor</button>
            </div>
            <div id="explore-catalog-grid" class="explore-catalog-grid"></div>
        </div>
    </div>

    <!-- ═══ DRAW WALL BANNER ═══ -->
    <div id="draw-wall-banner" class="draw-wall-banner" style="display:none;">
        <div class="banner-content">
            <span class="banner-icon">🧱</span>
            <span id="draw-wall-text">Wall Drawing: Click floor to set Wall Start Point</span>
            <button class="banner-btn" onclick="RenovaEditor.cancelWallDraw()">Cancel</button>
        </div>
    </div>

    <!-- ═══ CROSSHAIR ═══ -->
    <div id="crosshair" class="crosshair"></div>

    <!-- ═══ TRANSFORM HINT ═══ -->
    <div id="transform-hint" class="transform-hint">
        <kbd>Click</kbd> Select &nbsp; <kbd>Drag</kbd> Move &nbsp; <kbd>R</kbd> Rotate &nbsp; <kbd>Del</kbd> Delete
    </div>

    <!-- ═══ UPLOAD OVERLAY ═══ -->
    <div id="upload-overlay" class="upload-overlay" style="display:none;">
        <div class="upload-modal">
            <h2>🏠 Create Your 3D Room</h2>
            <p>Upload photos of your room (4 sides recommended) and we'll generate an interactive 3D model with detected furniture.</p>
            <div class="upload-dropzone" id="dropzone" onclick="document.getElementById('file-input').click()">
                <span class="drop-icon">📷</span>
                <div class="drop-text"><strong>Click to upload</strong> or drag & drop<br>JPG, PNG — up to 10 photos</div>
            </div>
            <input type="file" id="file-input" multiple accept="image/*" style="display:none" onchange="previewFiles()">
            <div id="upload-preview" class="upload-preview"></div>
            <div style="display:flex;gap:12px;justify-content:center;margin-top:16px;">
                <button class="btn-generate" onclick="RenovaEditor.uploadAndGenerate()">
                    ✨ Generate 3D Room
                </button>
                <button class="toolbar-btn" style="border-radius:50px;padding:14px 24px;" onclick="createQuickDemo()">
                    Skip — Use Demo
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ GENERATING OVERLAY ═══ -->
    <div id="generating-overlay" class="generating-overlay" style="display:none;">
        <div class="gen-spinner"></div>
        <div class="gen-text">Generating your 3D room...</div>
        <div class="gen-sub">Analyzing photos and detecting furniture</div>
    </div>

    <!-- ═══ TOAST CONTAINER ═══ -->
    <div id="toast-container" class="toast-container"></div>

    <!-- ═══ STATUS BAR ═══ -->
    <div class="status-bar">
        <div class="status-item">
            <span id="status-dot" class="status-dot offline"></span>
            <span id="status-text">Checking server...</span>
        </div>
        <div class="status-item">
            RenovaSim 3D Editor v2.0
        </div>
    </div>

    <script>
        // Tab switching
        function switchTab(btn, tabId) {
            document.querySelectorAll('.panel-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.panel-content').forEach(t => t.style.display = 'none');
            btn.classList.add('active');
            document.getElementById(tabId).style.display = 'block';
        }

        // Category filter
        function filterCatalog(btn, cat) {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('#catalog-grid .asset-card').forEach(card => {
                if (cat === 'all') { card.style.display = ''; return; }
                // Filter by data attribute or just show all for simplicity
                card.style.display = '';
            });
        }

        // File preview
        function previewFiles() {
            const input = document.getElementById('file-input');
            const preview = document.getElementById('upload-preview');
            preview.innerHTML = '';
            Array.from(input.files).forEach(f => {
                const img = document.createElement('img');
                img.className = 'preview-thumb';
                img.src = URL.createObjectURL(f);
                preview.appendChild(img);
            });
        }

        // Drag & drop
        const dz = document.getElementById('dropzone');
        if (dz) {
            ['dragenter','dragover'].forEach(e => dz.addEventListener(e, (ev) => { ev.preventDefault(); dz.classList.add('dragover'); }));
            ['dragleave','drop'].forEach(e => dz.addEventListener(e, (ev) => { ev.preventDefault(); dz.classList.remove('dragover'); }));
            dz.addEventListener('drop', (ev) => {
                const input = document.getElementById('file-input');
                input.files = ev.dataTransfer.files;
                previewFiles();
            });
        }

        // Quick demo skip
        function createQuickDemo() {
            document.getElementById('upload-overlay').style.display = 'none';
            // Trigger demo room creation via a simulated upload
            if (window.RenovaEditor && window.RenovaEditor.uploadAndGenerate) {
                // Create a dummy file for demo
                const blob = new Blob(['demo'], {type:'image/jpeg'});
                const file = new File([blob], 'demo.jpg', {type:'image/jpeg'});
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('file-input').files = dt.files;
                RenovaEditor.uploadAndGenerate();
            }
        }

        // Explore mode catalog interactions
        function toggleExploreCatalog() {
            const el = document.getElementById('explore-catalog');
            if (!el) return;
            const open = el.style.display === 'none' || el.style.display === '';
            el.style.display = open ? 'flex' : 'none';
            if (open) {
                if (document.pointerLockElement) document.exitPointerLock();
                renderExploreCatalog();
            } else {
                const canvas = document.getElementById('editor-canvas').querySelector('canvas');
                if (canvas) canvas.requestPointerLock();
            }
        }

        let currentExploreCategory = 'all';
        function filterExploreCatalog(cat) {
            currentExploreCategory = cat;
            document.querySelectorAll('.explore-catalog-tabs .cat-tab').forEach(t => {
                const text = t.textContent.toLowerCase();
                if (text.includes(cat) || (cat === 'all' && text === 'all')) {
                    t.classList.add('active');
                } else {
                    t.classList.remove('active');
                }
            });
            renderExploreCatalog();
        }

        function renderExploreCatalog() {
            const el = document.getElementById('explore-catalog-grid');
            if (!el || !window.RenovaEditor || !window.RenovaEditor.getCatalog) return;
            const cat = window.RenovaEditor.getCatalog();
            let html = '';
            Object.entries(cat).forEach(([key, item]) => {
                if (currentExploreCategory !== 'all' && item.category !== currentExploreCategory) return;
                html += `<div class="explore-catalog-card" onclick="window.RenovaEditor.spawnFurniture('${key}')">
                    <span class="explore-catalog-card-icon">${item.icon || '📦'}</span>
                    <span class="explore-catalog-card-name">${item.name}</span>
                </div>`;
            });
            el.innerHTML = html;
        }
    </script>
</body>
</html>
