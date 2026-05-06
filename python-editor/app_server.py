"""
RenovaSim Python Editor Server v2.0
====================================
Flask-based API server for 3D Room Editor
- Image upload & simulated 3D generation
- Room template recommendations
- Furniture catalog & asset management
- Room data persistence

Run: python app_server.py
"""

from flask import Flask, request, jsonify, send_from_directory
from flask_cors import CORS
from flask_restful import Api, Resource
import json
import os
import uuid
import base64
from datetime import datetime

app = Flask(__name__)
CORS(app)
api = Api(app)

# ─────────────────────────────────────────────
# CONSTANTS
# ─────────────────────────────────────────────
DATA_DIR = os.path.join(os.path.dirname(__file__), 'data')
UPLOAD_DIR = os.path.join(DATA_DIR, 'uploads')
os.makedirs(DATA_DIR, exist_ok=True)
os.makedirs(UPLOAD_DIR, exist_ok=True)

# ─────────────────────────────────────────────
# FURNITURE CATALOG (extended)
# ─────────────────────────────────────────────
FURNITURE_CATALOG = {
    # Living Room
    "sofa": {"name": "Sofa", "category": "living", "color": "#6b5b4f", "scale": [2.0, 0.8, 0.9], "icon": "🛋"},
    "armchair": {"name": "Armchair", "category": "living", "color": "#7a6955", "scale": [0.9, 0.85, 0.9], "icon": "💺"},
    "coffee_table": {"name": "Coffee Table", "category": "living", "color": "#a0826d", "scale": [1.0, 0.4, 0.6], "icon": "☕"},
    "tv_stand": {"name": "TV Stand", "category": "living", "color": "#323232", "scale": [1.4, 0.5, 0.4], "icon": "📺"},
    "bookshelf": {"name": "Bookshelf", "category": "living", "color": "#654321", "scale": [0.8, 1.8, 0.35], "icon": "📚"},
    "rug": {"name": "Rug", "category": "living", "color": "#a05050", "scale": [2.5, 0.02, 3.5], "icon": "🟥"},
    # Bedroom
    "bed_single": {"name": "Single Bed", "category": "bedroom", "color": "#8b7355", "scale": [1.0, 0.5, 2.0], "icon": "🛏"},
    "bed_double": {"name": "Double Bed", "category": "bedroom", "color": "#8b7355", "scale": [1.6, 0.5, 2.1], "icon": "🛏"},
    "wardrobe": {"name": "Wardrobe", "category": "bedroom", "color": "#503c28", "scale": [1.2, 2.0, 0.6], "icon": "🚪"},
    "nightstand": {"name": "Nightstand", "category": "bedroom", "color": "#8b7355", "scale": [0.5, 0.55, 0.45], "icon": "🪑"},
    "dresser": {"name": "Dresser", "category": "bedroom", "color": "#654321", "scale": [1.0, 0.85, 0.5], "icon": "🗄"},
    # Kitchen
    "fridge": {"name": "Refrigerator", "category": "kitchen", "color": "#dcdce1", "scale": [0.7, 1.8, 0.7], "icon": "🧊"},
    "oven": {"name": "Oven", "category": "kitchen", "color": "#bebec3", "scale": [0.7, 0.85, 0.7], "icon": "♨"},
    "kitchen_counter": {"name": "Kitchen Counter", "category": "kitchen", "color": "#d4c5b2", "scale": [1.5, 0.9, 0.6], "icon": "🍽"},
    "kitchen_sink": {"name": "Kitchen Sink", "category": "kitchen", "color": "#c8d2d7", "scale": [0.8, 0.9, 0.6], "icon": "🚰"},
    "dining_table": {"name": "Dining Table", "category": "kitchen", "color": "#a0826d", "scale": [1.4, 0.78, 0.9], "icon": "🍽"},
    "dining_chair": {"name": "Dining Chair", "category": "kitchen", "color": "#654321", "scale": [0.45, 0.9, 0.45], "icon": "🪑"},
    # Bathroom
    "bathtub": {"name": "Bathtub", "category": "bathroom", "color": "#e8edf0", "scale": [1.8, 0.6, 0.8], "icon": "🛁"},
    "toilet": {"name": "Toilet", "category": "bathroom", "color": "#e6e6e6", "scale": [0.45, 0.7, 0.65], "icon": "🚽"},
    "bathroom_sink": {"name": "Bathroom Sink", "category": "bathroom", "color": "#e0e5e8", "scale": [0.6, 0.85, 0.5], "icon": "🚰"},
    "mirror": {"name": "Wall Mirror", "category": "bathroom", "color": "#b4d2e6", "scale": [0.6, 0.8, 0.05], "icon": "🪞"},
    # Decorative
    "lamp_floor": {"name": "Floor Lamp", "category": "decor", "color": "#ffdc50", "scale": [0.3, 1.5, 0.3], "icon": "💡"},
    "lamp_table": {"name": "Table Lamp", "category": "decor", "color": "#ffe080", "scale": [0.2, 0.45, 0.2], "icon": "💡"},
    "plant_small": {"name": "Small Plant", "category": "decor", "color": "#228b22", "scale": [0.3, 0.45, 0.3], "icon": "🪴"},
    "plant_large": {"name": "Large Plant", "category": "decor", "color": "#1a7a1a", "scale": [0.5, 1.2, 0.5], "icon": "🌿"},
    "painting": {"name": "Wall Painting", "category": "decor", "color": "#b4643c", "scale": [0.8, 0.6, 0.05], "icon": "🖼"},
    "clock": {"name": "Wall Clock", "category": "decor", "color": "#2c2c2c", "scale": [0.4, 0.4, 0.05], "icon": "🕐"},
    "curtain": {"name": "Curtain", "category": "decor", "color": "#d4a574", "scale": [1.2, 2.2, 0.08], "icon": "🪟"},
}

# ─────────────────────────────────────────────
# ROOM TEMPLATES
# ─────────────────────────────────────────────
ROOM_TEMPLATES = {
    "modern_living": {
        "name": "Modern Living Room",
        "description": "Minimalist living room with clean lines and neutral tones",
        "category": "living",
        "thumbnail": "🛋",
        "wall_color": "#f5f0eb",
        "floor_color": "#c4a882",
        "objects": [
            {"type": "sofa", "position": [0, 0.4, -2], "rotation": [0, 0, 0]},
            {"type": "coffee_table", "position": [0, 0.2, -0.5], "rotation": [0, 0, 0]},
            {"type": "tv_stand", "position": [0, 0.25, 2.5], "rotation": [0, 3.14, 0]},
            {"type": "rug", "position": [0, 0.01, -0.5], "rotation": [0, 0, 0]},
            {"type": "lamp_floor", "position": [-2.5, 0.75, -2], "rotation": [0, 0, 0]},
            {"type": "plant_large", "position": [2.5, 0.6, -2.5], "rotation": [0, 0.5, 0]},
            {"type": "painting", "position": [0, 1.8, -3.45], "rotation": [0, 0, 0]},
        ]
    },
    "cozy_bedroom": {
        "name": "Cozy Bedroom",
        "description": "Warm and comfortable bedroom with essential furnishings",
        "category": "bedroom",
        "thumbnail": "🛏",
        "wall_color": "#e8e0d8",
        "floor_color": "#b89a7d",
        "objects": [
            {"type": "bed_double", "position": [0, 0.25, -1.5], "rotation": [0, 0, 0]},
            {"type": "nightstand", "position": [-1.3, 0.275, -1.5], "rotation": [0, 0, 0]},
            {"type": "nightstand", "position": [1.3, 0.275, -1.5], "rotation": [0, 0, 0]},
            {"type": "wardrobe", "position": [2.5, 1.0, 0.5], "rotation": [0, -1.57, 0]},
            {"type": "dresser", "position": [-2.5, 0.425, 1], "rotation": [0, 1.57, 0]},
            {"type": "lamp_table", "position": [-1.3, 0.7, -1.5], "rotation": [0, 0, 0]},
            {"type": "mirror", "position": [-3.45, 1.5, 0], "rotation": [0, 1.57, 0]},
        ]
    },
    "modern_kitchen": {
        "name": "Modern Kitchen",
        "description": "Functional kitchen with dining area",
        "category": "kitchen",
        "thumbnail": "🍽",
        "wall_color": "#f0ede8",
        "floor_color": "#d4cec5",
        "objects": [
            {"type": "kitchen_counter", "position": [0, 0.45, -2.5], "rotation": [0, 0, 0]},
            {"type": "kitchen_sink", "position": [-1.5, 0.45, -2.5], "rotation": [0, 0, 0]},
            {"type": "fridge", "position": [2.8, 0.9, -2.5], "rotation": [0, 0, 0]},
            {"type": "oven", "position": [1.2, 0.425, -2.5], "rotation": [0, 0, 0]},
            {"type": "dining_table", "position": [0, 0.39, 1], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [-0.6, 0.45, 0.2], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [0.6, 0.45, 0.2], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [-0.6, 0.45, 1.8], "rotation": [0, 3.14, 0]},
            {"type": "dining_chair", "position": [0.6, 0.45, 1.8], "rotation": [0, 3.14, 0]},
        ]
    },
    "spa_bathroom": {
        "name": "Spa Bathroom",
        "description": "Relaxing bathroom with modern fixtures",
        "category": "bathroom",
        "thumbnail": "🛁",
        "wall_color": "#e5e9ec",
        "floor_color": "#c8cdd0",
        "objects": [
            {"type": "bathtub", "position": [-1.2, 0.3, -1.5], "rotation": [0, 0, 0]},
            {"type": "toilet", "position": [1.5, 0.35, -1.5], "rotation": [0, 0, 0]},
            {"type": "bathroom_sink", "position": [0, 0.425, 2], "rotation": [0, 3.14, 0]},
            {"type": "mirror", "position": [0, 1.5, 2.45], "rotation": [0, 3.14, 0]},
            {"type": "plant_small", "position": [-2, 0.225, 2], "rotation": [0, 0.3, 0]},
        ]
    },
    "home_office": {
        "name": "Home Office",
        "description": "Productive workspace with comfortable setup",
        "category": "living",
        "thumbnail": "🖥",
        "wall_color": "#eae6e0",
        "floor_color": "#b89a7d",
        "objects": [
            {"type": "coffee_table", "position": [0, 0.2, -2], "rotation": [0, 0, 0]},
            {"type": "armchair", "position": [0, 0.425, -0.8], "rotation": [0, 0, 0]},
            {"type": "bookshelf", "position": [2.8, 0.9, 0], "rotation": [0, -1.57, 0]},
            {"type": "bookshelf", "position": [2.8, 0.9, -1.5], "rotation": [0, -1.57, 0]},
            {"type": "lamp_floor", "position": [-2.5, 0.75, -2], "rotation": [0, 0, 0]},
            {"type": "plant_small", "position": [2, 0.225, 2.5], "rotation": [0, 0, 0]},
            {"type": "clock", "position": [0, 2.0, -3.45], "rotation": [0, 0, 0]},
        ]
    },
}

# ─────────────────────────────────────────────
# WALL PAINT COLORS
# ─────────────────────────────────────────────
PAINT_COLORS = [
    {"name": "Cloud White", "hex": "#f5f0eb"},
    {"name": "Warm Ivory", "hex": "#f0e6d8"},
    {"name": "Soft Gray", "hex": "#e0ddd8"},
    {"name": "Cool Blue", "hex": "#d0dbe5"},
    {"name": "Sage Green", "hex": "#cdd8c8"},
    {"name": "Blush Pink", "hex": "#e8d5d0"},
    {"name": "Dusty Rose", "hex": "#d4a0a0"},
    {"name": "Navy", "hex": "#2c3e50"},
    {"name": "Charcoal", "hex": "#3a3a3a"},
    {"name": "Terracotta", "hex": "#c47a5a"},
    {"name": "Olive", "hex": "#6b7a4a"},
    {"name": "Slate Blue", "hex": "#6a7f8a"},
]


# ─────────────────────────────────────────────
# UTILITY FUNCTIONS
# ─────────────────────────────────────────────
def get_room_file(room_id):
    return os.path.join(DATA_DIR, f'room_{room_id}.json')


def load_room(room_id):
    room_file = get_room_file(room_id)
    if os.path.exists(room_file):
        try:
            with open(room_file, 'r') as f:
                return json.load(f)
        except:
            pass
    return None


def save_room(room_id, data):
    room_file = get_room_file(room_id)
    with open(room_file, 'w') as f:
        json.dump(data, f, indent=2)


def detect_objects_from_image(filename):
    """
    Simulated object detection from room photos.
    In production, this would use a CV model (YOLO, etc.)
    Returns a list of detected furniture items.
    """
    import random
    import hashlib
    
    # Make generation deterministic for the same photo
    seed_str = filename
    seed_num = int(hashlib.md5(seed_str.encode()).hexdigest(), 16)
    rng = random.Random(seed_num)

    all_items = list(FURNITURE_CATALOG.keys())
    lower_name = filename.lower()
    
    # Simple heuristic to make it feel more "accurate" based on typical image uploads
    if 'bedroom' in lower_name or 'bed' in lower_name:
        base_items = ['bed_double', 'wardrobe', 'rug_large', 'plant_monstera']
    elif 'kitchen' in lower_name:
        base_items = ['dining_table', 'chair_wood', 'cabinet_base']
    elif 'bath' in lower_name:
        base_items = ['toilet', 'sink_vanity', 'mirror_round']
    else: # Default to Living Room or generic
        base_items = ['sofa_3seater', 'tv_stand', 'coffee_table', 'rug_large']

    # Ensure base items exist in catalog
    base_items = [i for i in base_items if i in all_items]
    
    count = rng.randint(4, 7)
    detected = base_items.copy()
    remaining = [i for i in all_items if i not in detected]
    
    extra_count = max(0, count - len(detected))
    if remaining:
        detected.extend(rng.sample(remaining, min(extra_count, len(remaining))))
        
    results = []
    for item_key in detected:
        item = FURNITURE_CATALOG[item_key]
        results.append({
            "type": item_key,
            "name": item["name"],
            "confidence": round(rng.uniform(0.7, 0.98), 2),
            "category": item["category"],
        })
    return results


def generate_room_from_images(images_info, room_id):
    """
    Simulated 3D room generation from uploaded images.
    Returns room data with detected objects placed in the scene.
    """
    import random
    # Estimate room dimensions from number of photos
    num_photos = len(images_info)
    width = random.uniform(5, 8)
    length = random.uniform(6, 10)
    height = random.uniform(2.8, 3.5)

    # Detect objects from all images
    all_detected = []
    seen_types = set()
    for img in images_info:
        detected = detect_objects_from_image(img["filename"])
        for obj in detected:
            if obj["type"] not in seen_types:
                seen_types.add(obj["type"])
                all_detected.append(obj)

    # Place detected objects in the room
    placed_objects = []
    for i, obj in enumerate(all_detected):
        catalog_item = FURNITURE_CATALOG[obj["type"]]
        scale = catalog_item["scale"]
        # Generate semi-random positions within room bounds
        px = random.uniform(-width/2 + scale[0]/2, width/2 - scale[0]/2)
        pz = random.uniform(-length/2 + scale[2]/2, length/2 - scale[2]/2)
        py = scale[1] / 2

        placed_objects.append({
            "id": str(uuid.uuid4())[:8],
            "type": obj["type"],
            "name": catalog_item["name"],
            "category": catalog_item["category"],
            "position": [round(px, 2), round(py, 2), round(pz, 2)],
            "rotation": [0, round(random.uniform(0, 6.28), 2), 0],
            "scale": scale,
            "color": catalog_item["color"],
            "detected": True,
            "confidence": obj["confidence"],
        })

    # Determine room type recommendation
    category_counts = {}
    for obj in all_detected:
        cat = obj["category"]
        category_counts[cat] = category_counts.get(cat, 0) + 1
    recommended_type = max(category_counts, key=category_counts.get) if category_counts else "living"

    # Find matching templates
    matching_templates = [
        {"id": tid, **tdata}
        for tid, tdata in ROOM_TEMPLATES.items()
        if tdata["category"] == recommended_type
    ]

    room_data = {
        "id": room_id,
        "name": f"Room {room_id[:6]}",
        "width": round(width, 1),
        "length": round(length, 1),
        "height": round(height, 1),
        "wall_color": "#f5f0eb",
        "floor_color": "#c4a882",
        "objects": placed_objects,
        "detected_assets": all_detected,
        "recommended_type": recommended_type,
        "recommended_templates": matching_templates,
        "images": [img["filename"] for img in images_info],
        "created_at": datetime.now().isoformat(),
        "status": "generated",
    }

    save_room(room_id, room_data)
    return room_data


# ─────────────────────────────────────────────
# API RESOURCES
# ─────────────────────────────────────────────
class Room(Resource):
    def get(self, room_id):
        room_data = load_room(room_id)
        if room_data:
            return room_data, 200
        return {"error": "Room not found"}, 404

    def post(self, room_id):
        data = request.get_json()
        save_room(room_id, data)
        return {"status": "success", "message": f"Room {room_id} saved"}, 201


class RoomObjects(Resource):
    def get(self, room_id):
        room_data = load_room(room_id)
        if room_data:
            return {"objects": room_data.get("objects", [])}, 200
        return {"error": "Room not found"}, 404

    def post(self, room_id):
        data = request.get_json()
        room_data = load_room(room_id) or {
            "id": room_id, "objects": [],
            "created_at": datetime.now().isoformat(),
        }
        room_data["objects"] = data.get("objects", [])
        room_data["updated_at"] = datetime.now().isoformat()
        save_room(room_id, room_data)
        return {"status": "success", "objects_count": len(room_data["objects"])}, 200


class FurnitureLibrary(Resource):
    def get(self):
        return {"catalog": FURNITURE_CATALOG}, 200


class TemplateLibrary(Resource):
    def get(self):
        return {"templates": ROOM_TEMPLATES}, 200


class PaintColors(Resource):
    def get(self):
        return {"colors": PAINT_COLORS}, 200


class EditorStatus(Resource):
    def get(self):
        return {
            "status": "online",
            "version": "2.0.0",
            "editor": "RenovaSim 3D Editor API",
            "timestamp": datetime.now().isoformat(),
        }, 200


# ─────────────────────────────────────────────
# ROUTES
# ─────────────────────────────────────────────
api.add_resource(Room, '/api/rooms/<room_id>')
api.add_resource(RoomObjects, '/api/rooms/<room_id>/objects')
api.add_resource(FurnitureLibrary, '/api/furniture')
api.add_resource(TemplateLibrary, '/api/templates')
api.add_resource(PaintColors, '/api/paint-colors')
api.add_resource(EditorStatus, '/api/status')


@app.route('/api/upload-images', methods=['POST'])
def upload_images():
    """Upload room photos and generate 3D room"""
    room_id = str(uuid.uuid4())[:12]
    images_info = []

    if 'images' in request.files:
        files = request.files.getlist('images')
        for f in files:
            ext = f.filename.rsplit('.', 1)[-1].lower() if '.' in f.filename else 'jpg'
            fname = f"{room_id}_{uuid.uuid4().hex[:6]}.{ext}"
            fpath = os.path.join(UPLOAD_DIR, fname)
            f.save(fpath)
            images_info.append({"filename": fname, "original": f.filename})
    elif request.is_json:
        data = request.get_json()
        for i, img_data in enumerate(data.get("images", [])):
            fname = f"{room_id}_{i}.jpg"
            images_info.append({"filename": fname, "original": f"photo_{i}.jpg"})

    if not images_info:
        return jsonify({"error": "No images provided"}), 400

    room_data = generate_room_from_images(images_info, room_id)
    return jsonify({
        "status": "success",
        "room_id": room_id,
        "room": room_data,
        "message": f"Generated 3D room with {len(room_data['objects'])} objects detected"
    }), 201


@app.route('/api/rooms/<room_id>/apply-template', methods=['POST'])
def apply_template(room_id):
    """Apply a template to a room"""
    data = request.get_json()
    template_id = data.get("template_id")

    if template_id not in ROOM_TEMPLATES:
        return jsonify({"error": "Template not found"}), 404

    room_data = load_room(room_id)
    if not room_data:
        return jsonify({"error": "Room not found"}), 404

    template = ROOM_TEMPLATES[template_id]

    # Apply template objects
    template_objects = []
    for obj_def in template["objects"]:
        catalog_item = FURNITURE_CATALOG.get(obj_def["type"])
        if catalog_item:
            template_objects.append({
                "id": str(uuid.uuid4())[:8],
                "type": obj_def["type"],
                "name": catalog_item["name"],
                "category": catalog_item["category"],
                "position": obj_def["position"],
                "rotation": obj_def["rotation"],
                "scale": catalog_item["scale"],
                "color": catalog_item["color"],
                "detected": False,
                "template": template_id,
            })

    room_data["objects"] = template_objects
    room_data["wall_color"] = template.get("wall_color", room_data.get("wall_color", "#f5f0eb"))
    room_data["floor_color"] = template.get("floor_color", room_data.get("floor_color", "#c4a882"))
    room_data["applied_template"] = template_id
    room_data["updated_at"] = datetime.now().isoformat()

    save_room(room_id, room_data)
    return jsonify({"status": "success", "room": room_data}), 200


@app.route('/api/rooms/<room_id>/update-wall', methods=['POST'])
def update_wall_color(room_id):
    """Update wall color (paint)"""
    data = request.get_json()
    room_data = load_room(room_id)
    if not room_data:
        return jsonify({"error": "Room not found"}), 404

    wall_id = data.get("wall_id", "all")
    color = data.get("color", "#f5f0eb")

    if wall_id == "all":
        room_data["wall_color"] = color
    else:
        if "wall_colors" not in room_data:
            room_data["wall_colors"] = {}
        room_data["wall_colors"][wall_id] = color

    room_data["updated_at"] = datetime.now().isoformat()
    save_room(room_id, room_data)
    return jsonify({"status": "success", "room": room_data}), 200


@app.route('/api/rooms/<room_id>/save', methods=['POST'])
def save_room_route(room_id):
    """Save room data"""
    data = request.get_json()
    room_data = load_room(room_id) or {
        "id": room_id,
        "name": data.get("name", f"Room {room_id}"),
        "width": data.get("width", 8),
        "length": data.get("length", 10),
        "height": data.get("height", 3.2),
        "objects": [],
        "created_at": datetime.now().isoformat(),
    }
    room_data.update(data)
    room_data["updated_at"] = datetime.now().isoformat()
    save_room(room_id, room_data)
    return jsonify({"status": "success", "data": room_data}), 200


@app.route('/uploads/<filename>')
def serve_upload(filename):
    return send_from_directory(UPLOAD_DIR, filename)


# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
if __name__ == '__main__':
    print("")
    print("  ==========================================")
    print("   RenovaSim 3D Editor API Server v2.0")
    print("              STARTING...")
    print("  ==========================================")
    print("")
    print(f"  Data directory: {DATA_DIR}")
    print(f"  Upload directory: {UPLOAD_DIR}")
    print("  Server running at: http://localhost:5000")
    print("")
    print("  API Endpoints:")
    print("   GET  /api/status              - Server status")
    print("   GET  /api/furniture            - Furniture catalog")
    print("   GET  /api/templates            - Room templates")
    print("   GET  /api/paint-colors         - Paint color palette")
    print("   POST /api/upload-images        - Upload & generate 3D")
    print("   GET  /api/rooms/<id>           - Get room data")
    print("   POST /api/rooms/<id>/save      - Save room")
    print("   POST /api/rooms/<id>/apply-template - Apply template")
    print("   POST /api/rooms/<id>/update-wall    - Update wall color")
    print("")
    print("  CORS enabled")
    print("")

    app.run(host='localhost', port=5000, debug=True)
