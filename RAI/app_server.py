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

# Initialize YOLO model
try:
    from ultralytics import YOLO
    import cv2
    # Download and load yolov8 nano model for fast detection
    yolo_model = YOLO('yolov8n.pt')
    print("YOLOv8 model loaded successfully.")
except Exception as e:
    print("YOLO could not be loaded:", e)
    yolo_model = None

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
        "width": 8.0, "length": 10.0, "height": 3.2,
        "wall_color": "#f5f0eb",
        "floor_color": "#c4a882",
        "objects": [
            {"type": "sofa", "position": [0, 0.4, -1.8], "rotation": [0, 0, 0]},
            {"type": "coffee_table", "position": [0, 0.2, -0.3], "rotation": [0, 0, 0]},
            {"type": "tv_stand", "position": [0, 0.25, 3.0], "rotation": [0, 3.14, 0]},
            {"type": "rug", "position": [0, 0.01, -0.5], "rotation": [0, 0, 0]},
            {"type": "lamp_floor", "position": [-2.8, 0.75, -2.2], "rotation": [0, 0, 0]},
            {"type": "plant_large", "position": [2.8, 0.6, -2.8], "rotation": [0, 0.5, 0]},
            {"type": "painting", "position": [0, 1.8, -3.47], "rotation": [0, 0, 0]},
            {"type": "armchair", "position": [-2.2, 0.425, 0], "rotation": [0, 1.2, 0]},
            {"type": "bookshelf", "position": [3.2, 0.9, 0], "rotation": [0, -1.57, 0]},
            {"type": "curtain", "position": [-3.47, 1.4, -1.5], "rotation": [0, 1.57, 0]},
        ]
    },
    "cozy_bedroom": {
        "name": "Cozy Bedroom",
        "description": "Warm bedroom with essential furnishings and cozy atmosphere",
        "category": "bedroom",
        "thumbnail": "🛏",
        "width": 7.0, "length": 7.0, "height": 3.0,
        "wall_color": "#e8e0d8",
        "floor_color": "#b89a7d",
        "objects": [
            {"type": "bed_double", "position": [0, 0.25, -1.8], "rotation": [0, 0, 0]},
            {"type": "nightstand", "position": [-1.3, 0.275, -1.8], "rotation": [0, 0, 0]},
            {"type": "nightstand", "position": [1.3, 0.275, -1.8], "rotation": [0, 0, 0]},
            {"type": "lamp_table", "position": [-1.3, 0.7, -1.8], "rotation": [0, 0, 0]},
            {"type": "lamp_table", "position": [1.3, 0.7, -1.8], "rotation": [0, 0, 0]},
            {"type": "wardrobe", "position": [2.8, 1.0, 0.5], "rotation": [0, -1.57, 0]},
            {"type": "dresser", "position": [-2.8, 0.425, 1], "rotation": [0, 1.57, 0]},
            {"type": "mirror", "position": [-3.47, 1.5, 1], "rotation": [0, 1.57, 0]},
            {"type": "rug", "position": [0, 0.01, -0.5], "rotation": [0, 0, 0]},
            {"type": "plant_small", "position": [2.8, 0.225, 2.5], "rotation": [0, 0, 0]},
            {"type": "curtain", "position": [0, 1.4, -3.47], "rotation": [0, 0, 0]},
        ]
    },
    "modern_kitchen": {
        "name": "Modern Kitchen",
        "description": "Functional kitchen with dining area",
        "category": "kitchen",
        "thumbnail": "🍽",
        "width": 6.0, "length": 6.0, "height": 3.0,
        "wall_color": "#f0ede8",
        "floor_color": "#d4cec5",
        "objects": [
            {"type": "kitchen_counter", "position": [0, 0.45, -3.0], "rotation": [0, 0, 0]},
            {"type": "kitchen_sink", "position": [-1.5, 0.45, -3.0], "rotation": [0, 0, 0]},
            {"type": "fridge", "position": [3.0, 0.9, -3.0], "rotation": [0, 0, 0]},
            {"type": "oven", "position": [1.5, 0.425, -3.0], "rotation": [0, 0, 0]},
            {"type": "dining_table", "position": [0, 0.39, 1.5], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [-0.6, 0.45, 0.7], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [0.6, 0.45, 0.7], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [-0.6, 0.45, 2.3], "rotation": [0, 3.14, 0]},
            {"type": "dining_chair", "position": [0.6, 0.45, 2.3], "rotation": [0, 3.14, 0]},
            {"type": "plant_small", "position": [-3.0, 0.225, 3.0], "rotation": [0, 0.3, 0]},
        ]
    },
    "spa_bathroom": {
        "name": "Spa Bathroom",
        "description": "Relaxing bathroom with modern fixtures",
        "category": "bathroom",
        "thumbnail": "🛁",
        "width": 5.0, "length": 5.0, "height": 2.8,
        "wall_color": "#e5e9ec",
        "floor_color": "#c8cdd0",
        "objects": [
            {"type": "bathtub", "position": [-1.0, 0.3, -1.8], "rotation": [0, 0, 0]},
            {"type": "toilet", "position": [1.5, 0.35, -1.8], "rotation": [0, 0, 0]},
            {"type": "bathroom_sink", "position": [0, 0.425, 2.0], "rotation": [0, 3.14, 0]},
            {"type": "mirror", "position": [0, 1.5, 2.47], "rotation": [0, 3.14, 0]},
            {"type": "plant_small", "position": [-2.0, 0.225, 2.0], "rotation": [0, 0.3, 0]},
        ]
    },
    "home_office": {
        "name": "Home Office",
        "description": "Productive workspace with comfortable setup",
        "category": "living",
        "thumbnail": "🖥",
        "width": 6.0, "length": 7.0, "height": 3.0,
        "wall_color": "#eae6e0",
        "floor_color": "#b89a7d",
        "objects": [
            {"type": "coffee_table", "position": [0, 0.2, -2.5], "rotation": [0, 0, 0]},
            {"type": "armchair", "position": [0, 0.425, -1.2], "rotation": [0, 0, 0]},
            {"type": "bookshelf", "position": [3.0, 0.9, 0], "rotation": [0, -1.57, 0]},
            {"type": "bookshelf", "position": [3.0, 0.9, -1.5], "rotation": [0, -1.57, 0]},
            {"type": "lamp_floor", "position": [-2.8, 0.75, -2.5], "rotation": [0, 0, 0]},
            {"type": "plant_small", "position": [2.5, 0.225, 3.0], "rotation": [0, 0, 0]},
            {"type": "clock", "position": [0, 2.0, -3.47], "rotation": [0, 0, 0]},
            {"type": "rug", "position": [0, 0.01, -1.5], "rotation": [0, 0, 0]},
        ]
    },
    "luxury_living": {
        "name": "Luxury Living Room",
        "description": "Spacious luxury living with premium furnishings",
        "category": "living",
        "thumbnail": "✨",
        "width": 9.0, "length": 11.0, "height": 3.4,
        "wall_color": "#f0ece5",
        "floor_color": "#a08060",
        "objects": [
            {"type": "sofa", "position": [0, 0.4, -2.0], "rotation": [0, 0, 0]},
            {"type": "sofa", "position": [-2.5, 0.4, 0.5], "rotation": [0, 1.57, 0]},
            {"type": "coffee_table", "position": [-0.8, 0.2, 0], "rotation": [0, 0, 0]},
            {"type": "tv_stand", "position": [3.2, 0.25, 0], "rotation": [0, -1.57, 0]},
            {"type": "rug", "position": [-0.5, 0.01, -0.5], "rotation": [0, 0, 0]},
            {"type": "lamp_floor", "position": [1.5, 0.75, -2.5], "rotation": [0, 0, 0]},
            {"type": "lamp_floor", "position": [-3.5, 0.75, -2.5], "rotation": [0, 0, 0]},
            {"type": "plant_large", "position": [3.5, 0.6, -3.2], "rotation": [0, 0, 0]},
            {"type": "painting", "position": [0, 1.8, -3.97], "rotation": [0, 0, 0]},
            {"type": "curtain", "position": [-3.97, 1.4, 0], "rotation": [0, 1.57, 0]},
            {"type": "armchair", "position": [2, 0.425, -2], "rotation": [0, -0.8, 0]},
            {"type": "bookshelf", "position": [3.5, 0.9, 3], "rotation": [0, -1.57, 0]},
        ]
    },
    "kids_bedroom": {
        "name": "Kids Bedroom",
        "description": "Fun and colorful children's bedroom",
        "category": "bedroom",
        "thumbnail": "🧸",
        "width": 6.0, "length": 6.0, "height": 3.0,
        "wall_color": "#e8f0f5",
        "floor_color": "#c4b8a0",
        "objects": [
            {"type": "bed_single", "position": [-1.5, 0.25, -2.0], "rotation": [0, 0, 0]},
            {"type": "nightstand", "position": [-0.5, 0.275, -2.0], "rotation": [0, 0, 0]},
            {"type": "lamp_table", "position": [-0.5, 0.7, -2.0], "rotation": [0, 0, 0]},
            {"type": "wardrobe", "position": [2.5, 1.0, -2.0], "rotation": [0, 0, 0]},
            {"type": "rug", "position": [0, 0.01, 0.5], "rotation": [0, 0, 0]},
            {"type": "bookshelf", "position": [-2.8, 0.9, 1], "rotation": [0, 1.57, 0]},
            {"type": "plant_small", "position": [2.5, 0.225, 2.5], "rotation": [0, 0, 0]},
        ]
    },
    "studio_apartment": {
        "name": "Studio Apartment",
        "description": "Compact multi-functional living space",
        "category": "living",
        "thumbnail": "🏠",
        "width": 7.0, "length": 7.0, "height": 3.0,
        "wall_color": "#f5f2ed",
        "floor_color": "#c0a880",
        "objects": [
            {"type": "bed_single", "position": [-2.5, 0.25, -2.5], "rotation": [0, 0, 0]},
            {"type": "sofa", "position": [1.5, 0.4, -1.0], "rotation": [0, -1.57, 0]},
            {"type": "coffee_table", "position": [0, 0.2, 0], "rotation": [0, 0, 0]},
            {"type": "tv_stand", "position": [-3.0, 0.25, 0], "rotation": [0, 1.57, 0]},
            {"type": "dining_table", "position": [0, 0.39, 2.5], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [-0.5, 0.45, 1.8], "rotation": [0, 0, 0]},
            {"type": "dining_chair", "position": [0.5, 0.45, 3.2], "rotation": [0, 3.14, 0]},
            {"type": "lamp_floor", "position": [2.8, 0.75, -2.5], "rotation": [0, 0, 0]},
            {"type": "rug", "position": [0, 0.01, 0], "rotation": [0, 0, 0]},
            {"type": "plant_small", "position": [-3.0, 0.225, 3.0], "rotation": [0, 0, 0]},
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
    Object detection using YOLOv8.
    Extracts bounding boxes to map to 3D room coordinates.
    """
    filepath = os.path.join(UPLOAD_DIR, filename)
    
    # Fallback if YOLO is not available or file missing
    if not yolo_model or not os.path.exists(filepath):
        print("Using fallback deterministic detection")
        # Keep old fallback logic here for safety
        return []

    # Map COCO classes to our Furniture Catalog
    COCO_MAPPING = {
        'couch': 'sofa',
        'chair': 'dining_chair', # fallback to armchair or dining chair
        'bed': 'bed_double',
        'dining table': 'dining_table',
        'toilet': 'toilet',
        'tv': 'tv_stand',
        'refrigerator': 'fridge',
        'oven': 'oven',
        'sink': 'kitchen_sink',
        'potted plant': 'plant_large',
        'clock': 'clock',
        'vase': 'plant_small',
        'book': 'bookshelf',
    }

    try:
        img = cv2.imread(filepath)
        if img is None: return []
        
        height, width, _ = img.shape
        results = yolo_model(img, verbose=False)[0]
        
        detected = []
        for box in results.boxes:
            cls_name = results.names[int(box.cls[0])]
            conf = float(box.conf[0])
            
            if conf > 0.3 and cls_name in COCO_MAPPING:
                catalog_id = COCO_MAPPING[cls_name]
                
                # Get bounding box center
                x1, y1, x2, y2 = box.xyxy[0].tolist()
                cx = (x1 + x2) / 2
                cy = (y1 + y2) / 2
                
                # Calculate normalized positions (0 to 1)
                x_ratio = cx / width
                y_ratio = cy / height  # Use Y as Z-depth in 3D (lower in image = closer to camera)
                
                detected.append({
                    "type": catalog_id,
                    "name": FURNITURE_CATALOG[catalog_id]["name"],
                    "confidence": round(conf, 2),
                    "category": FURNITURE_CATALOG[catalog_id]["category"],
                    "x_ratio": x_ratio,
                    "y_ratio": y_ratio
                })
        return detected
    except Exception as e:
        print("YOLO detection error:", e)
        return []


def generate_room_from_images(images_info, room_id):
    """
    3D room generation using YOLO scanner coordinates.
    Maps 2D bounding boxes to 3D floor coordinates.
    """
    width = 8.0
    length = 10.0
    height = 3.0

    all_detected = []
    for img in images_info:
        detected = detect_objects_from_image(img["filename"])
        all_detected.extend(detected)

    placed_objects = []
    hw, hl = width / 2, length / 2

    for obj in all_detected:
        catalog_item = FURNITURE_CATALOG[obj["type"]]
        scale = catalog_item["scale"]
        
        # Map YOLO coordinates (0.0 to 1.0) to 3D room coordinates
        # x_ratio: left to right -> -hw to hw
        # y_ratio: top to bottom -> far wall (-hl) to close camera (hl)
        px = (obj.get("x_ratio", 0.5) - 0.5) * width
        pz = (obj.get("y_ratio", 0.5) - 0.5) * length
        
        py = scale[1] / 2
        rot_y = 0
        
        # Adjust wall-mounted items and rotations logically
        WALL_ITEMS = {'wardrobe', 'dresser', 'bookshelf', 'tv_stand', 'fridge', 'oven', 'kitchen_counter', 'kitchen_sink', 'bathroom_sink', 'mirror', 'painting', 'clock', 'curtain'}
        
        if obj["type"] in WALL_ITEMS:
            # Snap to nearest wall based on px, pz
            dist_left = abs(px - (-hw))
            dist_right = abs(px - hw)
            dist_back = abs(pz - (-hl))
            
            min_dist = min(dist_left, dist_right, dist_back)
            if min_dist == dist_back:
                pz = -hl + scale[2]/2 + 0.05
                rot_y = 0
            elif min_dist == dist_left:
                px = -hw + scale[2]/2 + 0.05
                rot_y = 1.57
            else:
                px = hw - scale[2]/2 - 0.05
                rot_y = -1.57
                
            if obj["type"] in ('mirror', 'painting', 'clock'):
                py = 1.6

        # Clamp within room bounds
        px = max(-hw + scale[0]/2 + 0.05, min(hw - scale[0]/2 - 0.05, px))
        pz = max(-hl + scale[2]/2 + 0.05, min(hl - scale[2]/2 - 0.05, pz))

        placed_objects.append({
            "id": str(uuid.uuid4())[:8],
            "type": obj["type"],
            "name": catalog_item["name"],
            "category": catalog_item["category"],
            "position": [round(px, 2), round(py, 2), round(pz, 2)],
            "rotation": [0, round(rot_y, 2), 0],
            "scale": scale,
            "color": catalog_item["color"],
            "detected": True,
            "confidence": obj["confidence"],
        })

    # Determine room type
    category_counts = {}
    for obj in all_detected:
        cat = obj["category"]
        category_counts[cat] = category_counts.get(cat, 0) + 1
    recommended_type = max(category_counts, key=category_counts.get) if category_counts else "living"

    matching_templates = [
        {"id": tid, **tdata}
        for tid, tdata in ROOM_TEMPLATES.items()
        if tdata["category"] == recommended_type
    ]

    room_data = {
        "id": room_id,
        "name": f"Room {room_id[:6]}",
        "width": width,
        "length": length,
        "height": height,
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

    def delete(self, room_id):
        room_file = get_room_file(room_id)
        if os.path.exists(room_file):
            os.remove(room_file)
            return {"status": "success", "message": "Room deleted"}, 200
        return {"error": "Room not found"}, 404


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


@app.route('/api/projects', methods=['GET'])
def list_projects():
    """List all saved room projects"""
    projects = []
    for fname in os.listdir(DATA_DIR):
        if fname.startswith('room_') and fname.endswith('.json'):
            try:
                with open(os.path.join(DATA_DIR, fname), 'r') as f:
                    data = json.load(f)
                projects.append({
                    "id": data.get("id", fname.replace('room_', '').replace('.json', '')),
                    "name": data.get("name", "Untitled Room"),
                    "width": data.get("width", 8),
                    "length": data.get("length", 10),
                    "height": data.get("height", 3.2),
                    "object_count": len(data.get("objects", [])),
                    "recommended_type": data.get("recommended_type", "living"),
                    "applied_template": data.get("applied_template", None),
                    "wall_color": data.get("wall_color", "#f5f0eb"),
                    "floor_color": data.get("floor_color", "#c4a882"),
                    "created_at": data.get("created_at", ""),
                    "updated_at": data.get("updated_at", ""),
                    "status": data.get("status", "saved"),
                    "thumbnail": data.get("thumbnail", None),
                })
            except:
                pass
    projects.sort(key=lambda x: x.get("updated_at") or x.get("created_at") or "", reverse=True)
    return jsonify({"projects": projects, "count": len(projects)}), 200


@app.route('/api/rooms/<room_id>/thumbnail', methods=['POST'])
def save_thumbnail(room_id):
    """Save a thumbnail image for a room"""
    data = request.get_json()
    room_data = load_room(room_id)
    if not room_data:
        return jsonify({"error": "Room not found"}), 404
    room_data["thumbnail"] = data.get("thumbnail", "")
    room_data["updated_at"] = datetime.now().isoformat()
    save_room(room_id, room_data)
    return jsonify({"status": "success"}), 200


@app.route('/api/rooms/<room_id>/rename', methods=['POST'])
def rename_room(room_id):
    """Rename a room project"""
    data = request.get_json()
    room_data = load_room(room_id)
    if not room_data:
        return jsonify({"error": "Room not found"}), 404
    room_data["name"] = data.get("name", room_data.get("name", "Untitled"))
    room_data["updated_at"] = datetime.now().isoformat()
    save_room(room_id, room_data)
    return jsonify({"status": "success", "name": room_data["name"]}), 200


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
    room_data["width"] = template.get("width", room_data.get("width", 8.0))
    room_data["length"] = template.get("length", room_data.get("length", 10.0))
    room_data["height"] = template.get("height", room_data.get("height", 3.2))
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
