"""
Room Editor 3D
==============
- EXPLORE MODE  : House Flipper style - walk around room in first person
- BUILD MODE    : Unity Editor style  - free orbit camera + gizmo controls

Requirements:
    pip install ursina

Controls:
  [TAB]          Toggle Explore / Build mode
  
  EXPLORE MODE:
    WASD          Move
    Mouse         Look around
    Scroll        Zoom / sprint toggle
    [F]           Interact / pick up nearby object
    [ESC]         Release mouse
  
  BUILD MODE:
    Middle Mouse  Pan
    Right Mouse   Orbit
    Scroll        Zoom
    Left Click    Select object
    [W]           Move gizmo
    [E]           Rotate gizmo
    [R]           Scale gizmo
    [Delete]      Delete selected
    [F]           Focus camera on selected
    [G]           Toggle grid snap
    [Ctrl+Z]      Undo
    [Ctrl+S]      Save scene
    Sidebar       Place furniture
"""

from ursina import *
from ursina.prefabs.first_person_controller import FirstPersonController
from ursina.shaders import lit_with_shadows_shader
import json, os, copy, math

# ─────────────────────────────────────────────
#  CONSTANTS
# ─────────────────────────────────────────────
ROOM_W, ROOM_L, ROOM_H = 8, 10, 3.2
GRID_SNAP = 0.5
SAVE_FILE  = "scene_save.json"

FURNITURE_CATALOG = {
    "Bed":        {"color": color.rgb(139,115, 85), "scale": (1.4,0.6,2.0), "emoji": "🛏"},
    "Sofa":       {"color": color.rgb(107, 91, 79), "scale": (2.0,0.8,0.9), "emoji": "🛋"},
    "Chair":      {"color": color.rgb(101, 67, 33), "scale": (0.6,0.8,0.6), "emoji": "🪑"},
    "Table":      {"color": color.rgb(160,130,109), "scale": (1.0,0.8,1.0), "emoji": "🍽"},
    "Desk":       {"color": color.rgb(139,115, 85), "scale": (1.2,0.75,0.6),"emoji": "🖥"},
    "Bookshelf":  {"color": color.rgb(101, 67, 33), "scale": (0.8,1.5,0.4), "emoji": "📚"},
    "Wardrobe":   {"color": color.rgb( 80, 60, 40), "scale": (1.0,1.8,0.5), "emoji": "🚪"},
    "TV Stand":   {"color": color.rgb( 50, 50, 50), "scale": (1.4,0.5,0.4), "emoji": "📺"},
    "Lamp":       {"color": color.rgb(255,220, 80), "scale": (0.2,1.2,0.2), "emoji": "💡"},
    "Plant":      {"color": color.rgb( 34,139, 34), "scale": (0.4,0.6,0.4), "emoji": "🪴"},
    "Bathtub":    {"color": color.rgb(220,230,235), "scale": (1.8,0.6,0.9), "emoji": "🛁"},
    "Toilet":     {"color": color.rgb(230,230,230), "scale": (0.5,0.8,0.7), "emoji": "🚽"},
    "Sink":       {"color": color.rgb(200,210,215), "scale": (0.6,0.8,0.5), "emoji": "🚰"},
    "Fridge":     {"color": color.rgb(220,220,225), "scale": (0.7,1.7,0.7), "emoji": "🧊"},
    "Oven":       {"color": color.rgb(190,190,195), "scale": (0.7,0.85,0.7),"emoji": "♨"},
    "Painting":   {"color": color.rgb(180,100, 60), "scale": (0.8,0.6,0.05),"emoji": "🖼"},
    "Mirror":     {"color": color.rgb(180,210,230), "scale": (0.6,1.0,0.05),"emoji": "🪞"},
    "Rug":        {"color": color.rgb(160, 80, 80), "scale": (2.0,0.02,3.0),"emoji": "🟥"},
}

WALL_COLOR   = color.rgb(241,245,249)
FLOOR_COLOR  = color.rgb(200,185,160)
CEILING_COLOR= color.rgb(250,250,252)
ACCENT_COLOR = color.rgb( 99,102,241)   # Indigo


# ─────────────────────────────────────────────
#  ROOM BUILDER
# ─────────────────────────────────────────────
def build_room():
    """Create floor, ceiling, four walls with proper normals."""
    entities = []
    hw, hl, hh = ROOM_W/2, ROOM_L/2, ROOM_H/2

    # Floor
    f = Entity(model='cube', color=FLOOR_COLOR,
               scale=(ROOM_W, 0.05, ROOM_L),
               position=(0,-0.025,0),
               collider='box', name='floor',
               texture='white_cube')
    f.texture_scale = (ROOM_W/2, ROOM_L/2)
    entities.append(f)

    # Ceiling
    c = Entity(model='cube', color=CEILING_COLOR,
               scale=(ROOM_W,0.05,ROOM_L),
               position=(0, ROOM_H+0.025, 0),
               name='ceiling')
    entities.append(c)

    # Walls: (pos, rot, sx, sz)
    walls_cfg = [
        ((0, hh, -hl), (0,0,0),   ROOM_W, ROOM_H),   # back
        ((0, hh,  hl), (0,180,0), ROOM_W, ROOM_H),   # front
        ((-hw, hh, 0), (0,90,0),  ROOM_L, ROOM_H),   # left
        (( hw, hh, 0), (0,-90,0), ROOM_L, ROOM_H),   # right
    ]
    for pos, rot, sx, sz in walls_cfg:
        w = Entity(model='cube', color=WALL_COLOR,
                   scale=(sx, sz, 0.05),
                   position=pos, rotation=rot,
                   name='wall')
        entities.append(w)

    # Baseboard trim (thin strip at bottom of walls)
    for pos, rot, sx, _ in walls_cfg:
        trim_pos = (pos[0], 0.06, pos[2])
        t = Entity(model='cube', color=color.rgb(220,215,205),
                   scale=(sx if rot[1] in (0,180) else 0.05,
                          0.12,
                          0.05 if rot[1] in (0,180) else ROOM_L),
                   position=trim_pos, rotation=rot, name='trim')
        entities.append(t)

    # Ceiling light
    light_panel = Entity(model='cube', color=color.rgb(255,252,230),
                         scale=(0.6,0.04,0.6),
                         position=(0, ROOM_H-0.02, 0),
                         name='light_panel')
    entities.append(light_panel)

    return entities


# ─────────────────────────────────────────────
#  FURNITURE ENTITY
# ─────────────────────────────────────────────
class FurnitureItem(Entity):
    def __init__(self, ftype, pos=(0,0,0), rot=(0,0,0), scl=None, **kwargs):
        cfg = FURNITURE_CATALOG[ftype]
        sx, sy, sz = scl if scl else cfg['scale']
        super().__init__(
            model='cube',
            color=cfg['color'],
            scale=(sx, sy, sz),
            position=Vec3(*pos) + Vec3(0, sy/2, 0),
            rotation=Vec3(*rot),
            collider='box',
            **kwargs
        )
        self.ftype = ftype
        self.base_color = cfg['color']
        # Add a subtle edge highlight mesh
        self._highlight = Entity(model='wireframe_cube',
                                  parent=self,
                                  color=color.clear,
                                  scale=(1.02,1.02,1.02))

    def select(self):
        self._highlight.color = color.rgb(99,255,150)

    def deselect(self):
        self._highlight.color = color.clear

    def to_dict(self):
        return {
            'type': self.ftype,
            'pos':  [round(self.x,3), round(self.y - self.scale_y/2, 3), round(self.z,3)],
            'rot':  [round(self.rotation_x,2), round(self.rotation_y,2), round(self.rotation_z,2)],
            'scl':  [round(self.scale_x,3), round(self.scale_y,3), round(self.scale_z,3)],
        }


# ─────────────────────────────────────────────
#  GIZMO (BUILD MODE)
# ─────────────────────────────────────────────
class Gizmo:
    """Visual 3-axis gizmo drawn with Line entities."""
    def __init__(self):
        self.target   = None
        self.mode     = 'move'   # move | rotate | scale
        self.active   = False
        self._drag_axis  = None
        self._drag_start = None
        self._orig_val   = None

        L = 0.8
        self.x_arrow = Entity(model='arrow', color=color.red,   scale=L, rotation=(0,0,-90), enabled=False)
        self.y_arrow = Entity(model='arrow', color=color.green, scale=L, rotation=(0,0,0),   enabled=False)
        self.z_arrow = Entity(model='arrow', color=color.azure, scale=L, rotation=(90,0,0),  enabled=False)
        self._arrows = [self.x_arrow, self.y_arrow, self.z_arrow]

    def attach(self, entity):
        self.target = entity
        for a in self._arrows:
            a.enabled = True
        self._sync()

    def detach(self):
        self.target = None
        for a in self._arrows:
            a.enabled = False

    def _sync(self):
        if not self.target: return
        p = self.target.world_position
        for a in self._arrows:
            a.position = p

    def update(self):
        self._sync()

    def hide(self):
        for a in self._arrows: a.enabled = False

    def show(self):
        if self.target:
            for a in self._arrows: a.enabled = True


# ─────────────────────────────────────────────
#  UI HELPERS
# ─────────────────────────────────────────────
def make_panel(pos, scale, color_val=color.rgba(20,20,30,200)):
    return Entity(parent=camera.ui, model='quad', color=color_val,
                  position=pos, scale=scale, z=-1)

def make_text(txt, pos, scale=1, color_val=color.white, parent=None):
    return Text(txt, position=pos, scale=scale, color=color_val,
                parent=parent or camera.ui)


# ─────────────────────────────────────────────
#  MAIN APPLICATION
# ─────────────────────────────────────────────
class RoomEditor3D:

    def __init__(self):
        # Initialize Ursina app
        self.app = Ursina(
            title='Room Editor 3D',
            vsync=True,
            development_mode=False
        )
        window.color = color.black
        window.fullscreen = False

        # ── Lighting ──────────────────────────────
        AmbientLight(color=color.rgba(200,200,210,128))
        dl = DirectionalLight()
        dl.look_at(Vec3(-1,-2,-1))
        dl.color = color.rgb(255,250,240)

        # ── State ─────────────────────────────────
        self.mode          = 'explore'   # 'explore' | 'build'
        self.furniture     = []
        self.selected      = None
        self.undo_stack    = []
        self.grid_snap     = True
        self.placed_type   = None        # type queued to place in build mode
        self.room_entities = []

        # ── Build camera state ─────────────────────
        self.orbit_yaw   = 30.0
        self.orbit_pitch = 40.0
        self.orbit_dist  = 12.0
        self.orbit_target= Vec3(0, ROOM_H/2, 0)

        # ── Build pivot entity ─────────────────────
        self.build_cam_pivot = Entity()

        # ── Room ──────────────────────────────────
        self.room_entities = build_room()

        # ── Explore player ────────────────────────
        self.player = FirstPersonController(
            position=(0, 1.7, ROOM_L/2 - 0.5),
            speed=4,
            height=1.7,
            camera_pivot_height=0
        )
        self.player.cursor.enabled = False
        mouse.locked = True

        # ── Gizmo ─────────────────────────────────
        # simple representation (actual arrow model may not exist - use cubes)
        self.gizmo_x = Entity(model='cube', color=color.red,
                               scale=(0.8,0.04,0.04), enabled=False, always_on_top=True)
        self.gizmo_y = Entity(model='cube', color=color.lime,
                               scale=(0.04,0.8,0.04), enabled=False, always_on_top=True)
        self.gizmo_z = Entity(model='cube', color=color.cyan,
                               scale=(0.04,0.04,0.8), enabled=False, always_on_top=True)
        self.gizmos  = [self.gizmo_x, self.gizmo_y, self.gizmo_z]
        self.gizmo_mode = 'move'   # move | rotate | scale

        # drag state
        self._drag_axis  = None
        self._drag_start = None
        self._orig_transform = None

        # ── Sky / background ──────────────────────
        Sky(color=color.rgb(180,190,200))

        # ── Build camera ──────────────────────────
        self.build_camera = Entity()   # dummy, we reuse camera

        # ── UI ────────────────────────────────────
        self._build_ui()

        # ── Status ────────────────────────────────
        self._status_timer = 0
        self.status_msg = ''

        # ── Load saved scene if exists ─────────────
        if os.path.exists(SAVE_FILE):
            self.load_scene()

        # Start in explore
        self._enter_explore()

        # ── Register event handlers ────────────────
        # Ursina will call these automatically
        base.update = self.update
        base.input = self.input

    # ═══════════════════════════════════════════
    #  UI CONSTRUCTION
    # ═══════════════════════════════════════════
    def _build_ui(self):
        # ── Top bar ──────────────────────────────
        self.topbar = Entity(parent=camera.ui,
                              model='quad',
                              color=color.rgba(15,15,25,230),
                              scale=(2,0.06), position=(0,0.47), z=-2)

        self.lbl_mode = Text('EXPLORE MODE', position=(-0.85, 0.455),
                              scale=1.2, color=color.rgb(99,255,150),
                              parent=camera.ui, font='VeraMono.ttf')

        self.lbl_hint = Text('', position=(0, 0.455),
                              scale=0.85, color=color.rgb(180,180,200),
                              parent=camera.ui, font='VeraMono.ttf',
                              origin=(0,0))

        self.lbl_status = Text('', position=(0, 0.42),
                                scale=0.75, color=color.yellow,
                                parent=camera.ui, font='VeraMono.ttf',
                                origin=(0,0))

        # ── Right sidebar (build mode) ────────────
        self.sidebar_bg = Entity(parent=camera.ui,
                                  model='quad',
                                  color=color.rgba(15,15,25,235),
                                  scale=(0.22, 1.0),
                                  position=(0.89, 0), z=-2,
                                  enabled=False)

        self.sidebar_title = Text('FURNITURE', position=(0.78, 0.44),
                                   scale=0.9, color=color.rgb(99,200,255),
                                   parent=camera.ui, font='VeraMono.ttf',
                                   enabled=False)

        self.sidebar_btns  = []
        self._build_sidebar()

        # ── Bottom panel (build mode) ─────────────
        self.bottom_bg = Entity(parent=camera.ui,
                                 model='quad',
                                 color=color.rgba(15,15,25,210),
                                 scale=(2, 0.07),
                                 position=(0,-0.465), z=-2,
                                 enabled=False)

        self.lbl_transform = Text('', position=(-0.6,-0.46),
                                   scale=0.75, color=color.rgb(220,220,240),
                                   parent=camera.ui, font='VeraMono.ttf',
                                   enabled=False)

        # Gizmo mode buttons
        self.btn_move   = self._mk_btn('MOVE [W]',   (-0.18,-0.455), self._gizmo_move)
        self.btn_rotate = self._mk_btn('ROTATE [E]', ( 0.04,-0.455), self._gizmo_rotate)
        self.btn_scale  = self._mk_btn('SCALE [R]',  ( 0.26,-0.455), self._gizmo_scale)
        self.btn_delete = self._mk_btn('DELETE [Del]',( 0.5,-0.455), self._delete_selected,
                                        col=color.rgb(200,60,60))
        self.btn_save   = self._mk_btn('SAVE [S]',   ( 0.7,-0.455), self.save_scene,
                                        col=color.rgb(60,180,100))

        for b in [self.btn_move, self.btn_rotate, self.btn_scale, self.btn_delete, self.btn_save]:
            b.enabled = False

        # snap toggle
        self.btn_snap = self._mk_btn('SNAP: ON [G]', (0.75, 0.455), self._toggle_snap,
                                      col=color.rgb(80,160,220), scale=(0.15,0.045))
        self.btn_snap.enabled = False

        # ── Tab button always visible ─────────────
        self._mk_btn('[TAB] Switch Mode', (-0.6, 0.455),
                     self._toggle_mode,
                     col=color.rgb(130,100,220),
                     scale=(0.18,0.045))

    def _mk_btn(self, label, pos, callback, col=None, scale=(0.14,0.045)):
        col = col or color.rgb(40,50,80)
        btn = Button(text=label, scale=scale, position=pos,
                     color=col, highlight_color=col.tint(0.2),
                     text_size=0.6, parent=camera.ui)
        btn.on_click = callback
        return btn

    def _build_sidebar(self):
        y = 0.38
        for i, (name, cfg) in enumerate(FURNITURE_CATALOG.items()):
            btn = Button(
                text=f"{cfg['emoji']} {name}",
                scale=(0.2, 0.042),
                position=(0.89, y),
                color=color.rgba(30,35,60,220),
                highlight_color=color.rgba(99,102,241,230),
                text_size=0.55,
                parent=camera.ui,
                enabled=False
            )
            btn._ftype = name
            btn.on_click = Func(self._queue_place, name)
            self.sidebar_btns.append(btn)
            y -= 0.048
            if y < -0.44:
                break  # Don't overflow screen

    # ═══════════════════════════════════════════
    #  MODE SWITCHING
    # ═══════════════════════════════════════════
    def _toggle_mode(self):
        if self.mode == 'explore':
            self._enter_build()
        else:
            self._enter_explore()

    def _enter_explore(self):
        self.mode = 'explore'

        # Show player
        self.player.enabled = True
        self.player.visible = True
        mouse.locked = True

        # Reset camera
        camera.parent = self.player.camera_pivot
        camera.position = (0,0,0)
        camera.rotation = (0,0,0)

        # Hide gizmos
        for g in self.gizmos: g.enabled = False
        self._deselect()

        # Hide build UI
        self.sidebar_bg.enabled = False
        self.sidebar_title.enabled = False
        for b in self.sidebar_btns: b.enabled = False
        self.bottom_bg.enabled = False
        self.lbl_transform.enabled = False
        for b in [self.btn_move,self.btn_rotate,self.btn_scale,self.btn_delete,self.btn_save]:
            b.enabled = False
        self.btn_snap.enabled = False

        # Update labels
        self.lbl_mode.text  = 'EXPLORE MODE'
        self.lbl_mode.color = color.rgb(99,255,150)
        self.lbl_hint.text  = 'WASD: Move | Mouse: Look | TAB: Build Mode | F: Interact'
        self._status('Entered EXPLORE mode — House Flipper style!')

    def _enter_build(self):
        self.mode = 'build'

        # Hide / disable player
        self.player.enabled  = False
        self.player.visible  = False
        mouse.locked = False

        # Detach camera from player
        camera.parent    = scene
        camera.position  = self._orbit_pos()
        camera.look_at(self.orbit_target)

        # Show build UI
        self.sidebar_bg.enabled    = True
        self.sidebar_title.enabled = True
        for b in self.sidebar_btns: b.enabled = True
        self.bottom_bg.enabled     = True
        self.lbl_transform.enabled = True
        for b in [self.btn_move,self.btn_rotate,self.btn_scale,self.btn_delete,self.btn_save]:
            b.enabled = True
        self.btn_snap.enabled = True

        self.lbl_mode.text  = 'BUILD MODE'
        self.lbl_mode.color = color.rgb(255,180,80)
        self.lbl_hint.text  = 'Click: Select | W/E/R: Gizmo | Del: Delete | RMB: Orbit | Scroll: Zoom | F: Focus'
        self._status('Entered BUILD mode — Unity Editor style!')

    # ═══════════════════════════════════════════
    #  ORBIT CAMERA (BUILD MODE)
    # ═══════════════════════════════════════════
    def _orbit_pos(self):
        pitch = clamp(self.orbit_pitch, 5, 85)
        yaw   = self.orbit_yaw
        r     = self.orbit_dist
        x = self.orbit_target.x + r * math.cos(math.radians(pitch)) * math.sin(math.radians(yaw))
        y = self.orbit_target.y + r * math.sin(math.radians(pitch))
        z = self.orbit_target.z + r * math.cos(math.radians(pitch)) * math.cos(math.radians(yaw))
        return Vec3(x, y, z)

    def _update_orbit_camera(self):
        camera.position = self._orbit_pos()
        camera.look_at(self.orbit_target)

    # ═══════════════════════════════════════════
    #  GIZMO CONTROLS
    # ═══════════════════════════════════════════
    def _sync_gizmos(self):
        if not self.selected: return
        p = self.selected.world_position
        for g in self.gizmos:
            g.position = p
            g.enabled  = (self.mode == 'build')

    def _gizmo_move(self):   self.gizmo_mode = 'move';   self._status('Gizmo: MOVE')
    def _gizmo_rotate(self): self.gizmo_mode = 'rotate'; self._status('Gizmo: ROTATE')
    def _gizmo_scale(self):  self.gizmo_mode = 'scale';  self._status('Gizmo: SCALE')

    def _toggle_snap(self):
        self.grid_snap = not self.grid_snap
        self.btn_snap.text = f"SNAP: {'ON' if self.grid_snap else 'OFF'} [G]"
        self._status(f"Grid snap {'enabled' if self.grid_snap else 'disabled'}")

    # ═══════════════════════════════════════════
    #  SELECTION
    # ═══════════════════════════════════════════
    def _select(self, ent):
        self._deselect()
        self.selected = ent
        ent.select()
        self._sync_gizmos()
        for g in self.gizmos: g.enabled = True

    def _deselect(self):
        if self.selected:
            self.selected.deselect()
        self.selected = None
        for g in self.gizmos: g.enabled = False

    # ═══════════════════════════════════════════
    #  FURNITURE PLACEMENT
    # ═══════════════════════════════════════════
    def _queue_place(self, ftype):
        self.placed_type = ftype
        self._status(f'Click on floor to place: {ftype}')

    def _place_at(self, hit_pos):
        if not self.placed_type: return
        px, pz = hit_pos.x, hit_pos.z
        if self.grid_snap:
            px = round(px / GRID_SNAP) * GRID_SNAP
            pz = round(pz / GRID_SNAP) * GRID_SNAP
        # Clamp within room
        hw, hl = ROOM_W/2 - 0.5, ROOM_L/2 - 0.5
        px = clamp(px, -hw, hw)
        pz = clamp(pz, -hl, hl)

        item = FurnitureItem(self.placed_type, pos=(px,0,pz))
        self.furniture.append(item)
        self._push_undo('add', item)
        self._select(item)
        self._status(f'Placed {self.placed_type}')
        self.placed_type = None

    # ═══════════════════════════════════════════
    #  DELETE
    # ═══════════════════════════════════════════
    def _delete_selected(self):
        if not self.selected: return
        self._push_undo('delete', self.selected)
        self.furniture.remove(self.selected)
        destroy(self.selected)
        self.selected = None
        for g in self.gizmos: g.enabled = False
        self._status('Object deleted')

    # ═══════════════════════════════════════════
    #  UNDO
    # ═══════════════════════════════════════════
    def _push_undo(self, action, entity):
        self.undo_stack.append({'action': action, 'data': entity.to_dict(), 'ref': entity})

    def _undo(self):
        if not self.undo_stack: return
        record = self.undo_stack.pop()
        if record['action'] == 'add':
            ent = record['ref']
            if ent in self.furniture:
                self.furniture.remove(ent)
                destroy(ent)
            self._status('Undo: removed last placed object')
        # Could extend with more undo types

    # ═══════════════════════════════════════════
    #  SAVE / LOAD
    # ═══════════════════════════════════════════
    def save_scene(self):
        data = [f.to_dict() for f in self.furniture]
        with open(SAVE_FILE, 'w') as fp:
            json.dump(data, fp, indent=2)
        self._status(f'Scene saved → {SAVE_FILE}  ({len(data)} objects)')

    def load_scene(self):
        try:
            with open(SAVE_FILE) as fp:
                data = json.load(fp)
            for d in data:
                item = FurnitureItem(d['type'],
                                     pos=d['pos'],
                                     rot=d['rot'],
                                     scl=d['scl'])
                # position already has y offset baked in to_dict, re-apply
                item.y = d['pos'][1] + item.scale_y/2
                self.furniture.append(item)
            self._status(f'Loaded {len(data)} objects from {SAVE_FILE}')
        except Exception as e:
            self._status(f'Load failed: {e}')

    # ═══════════════════════════════════════════
    #  STATUS MESSAGE
    # ═══════════════════════════════════════════
    def _status(self, msg, duration=3):
        self.lbl_status.text = msg
        self._status_timer = duration

    # ═══════════════════════════════════════════
    #  FOCUS CAMERA ON SELECTED
    # ═══════════════════════════════════════════
    def _focus_selected(self):
        if self.selected and self.mode == 'build':
            self.orbit_target = self.selected.world_position
            self.orbit_dist   = 5
            self._update_orbit_camera()
            self._status(f'Focused on {self.selected.ftype}')

    # ═══════════════════════════════════════════
    #  UPDATE LOOP
    # ═══════════════════════════════════════════
    def update(self):
        dt = time.dt

        # Status fade
        if self._status_timer > 0:
            self._status_timer -= dt
            if self._status_timer <= 0:
                self.lbl_status.text = ''

        if self.mode == 'explore':
            self._update_explore(dt)
        else:
            self._update_build(dt)

        # Gizmo sync
        self._sync_gizmos()

        # Transform info
        if self.selected and self.mode == 'build':
            p = self.selected.position
            r = self.selected.rotation
            s = self.selected.scale
            self.lbl_transform.text = (
                f"{self.selected.ftype}  |  "
                f"P({p.x:.2f},{p.y:.2f},{p.z:.2f})  "
                f"R({r.y:.1f}°)  "
                f"S({s.x:.2f},{s.y:.2f},{s.z:.2f})"
            )
        elif self.mode == 'build':
            self.lbl_transform.text = 'No selection'

    def _update_explore(self, dt):
        # Player already handled by FirstPersonController
        # Interact hint
        pass

    def _update_build(self, dt):
        # ── Orbit camera: right mouse drag ─────────
        if mouse.right:
            self.orbit_yaw   -= mouse.velocity[0] * 120
            self.orbit_pitch += mouse.velocity[1] * 120
            self.orbit_pitch  = clamp(self.orbit_pitch, 5, 85)
            self._update_orbit_camera()

        # ── Pan: middle mouse drag ─────────────────
        if mouse.middle:
            right   = camera.right   * (-mouse.velocity[0] * self.orbit_dist * 0.8)
            up_vec  = camera.up      * ( mouse.velocity[1] * self.orbit_dist * 0.8)
            self.orbit_target += right + up_vec
            self._update_orbit_camera()

        # ── Zoom: scroll ───────────────────────────
        scroll = mouse.wheel
        if scroll:
            self.orbit_dist = clamp(self.orbit_dist - scroll * 0.8, 2, 40)
            self._update_orbit_camera()

        # ── Drag selected object ───────────────────
        if self.selected and mouse.left and held_keys['left mouse']:
            self._drag_object()

    def _drag_object(self):
        """Move selected object by raycasting onto floor plane."""
        if not self.selected: return
        ray = raycast(camera.world_position,
                      camera.forward,
                      distance=100,
                      ignore=[self.selected] + self.gizmos)
        if ray.hit and ray.entity.name == 'floor':
            px, pz = ray.world_point.x, ray.world_point.z
            if self.grid_snap:
                px = round(px / GRID_SNAP) * GRID_SNAP
                pz = round(pz / GRID_SNAP) * GRID_SNAP
            hw, hl = ROOM_W/2 - 0.5, ROOM_L/2 - 0.5
            px = clamp(px, -hw, hw)
            pz = clamp(pz, -hl, hl)
            self.selected.x = px
            self.selected.z = pz

    # ═══════════════════════════════════════════
    #  INPUT
    # ═══════════════════════════════════════════
    def input(self, key):
        # ── TAB: switch mode ──────────────────────
        if key == 'tab':
            self._toggle_mode()
            return

        # ── BUILD MODE keys ───────────────────────
        if self.mode == 'build':
            if key == 'w': self._gizmo_move()
            if key == 'e': self._gizmo_rotate()
            if key == 'r': self._gizmo_scale()
            if key == 'g': self._toggle_snap()
            if key == 'f': self._focus_selected()
            if key == 'delete' or key == 'backspace':
                self._delete_selected()
            if key == 'control+z': self._undo()
            if key == 'control+s': self.save_scene()
            if key == 'escape':
                self._deselect()
                self.placed_type = None

            # ── Left click: select or place ───────
            if key == 'left mouse down':
                # Check if clicking on UI button area
                if mouse.x > 0.78: return   # sidebar

                if self.placed_type:
                    # Place on floor
                    ray = raycast(camera.world_position,
                                  camera.forward,
                                  distance=100,
                                  ignore=self.gizmos)
                    if ray.hit and ray.entity.name == 'floor':
                        self._place_at(ray.world_point)
                else:
                    # Select furniture
                    ray = raycast(camera.world_position,
                                  camera.forward,
                                  distance=100,
                                  ignore=self.gizmos + self.room_entities)
                    if ray.hit and isinstance(ray.entity, FurnitureItem):
                        self._select(ray.entity)
                    elif ray.hit:
                        self._deselect()

            # Rotate selected with Q/Z
            if key == 'q' and self.selected:
                self.selected.rotation_y += 45
                if self.grid_snap: self.selected.rotation_y = round(self.selected.rotation_y/45)*45
            if key == 'z' and self.selected:
                self.selected.rotation_y -= 45
                if self.grid_snap: self.selected.rotation_y = round(self.selected.rotation_y/45)*45

        # ── EXPLORE MODE keys ─────────────────────
        if self.mode == 'explore':
            if key == 'escape':
                mouse.locked = not mouse.locked
            if key == 'f':
                # Interact: look for nearby furniture
                ray = raycast(camera.world_position,
                              camera.forward,
                              distance=2.5,
                              ignore=[self.player])
                if ray.hit and isinstance(ray.entity, FurnitureItem):
                    self._status(f'[{ray.entity.ftype}] — Press TAB to enter Build Mode to move this')


# ─────────────────────────────────────────────
#  ENTRY POINT
# ─────────────────────────────────────────────
if __name__ == '__main__':
    editor = RoomEditor3D()
    # Ursina app.run() is called automatically when the window is created
    # The editor's update() and input() methods are registered in __init__


