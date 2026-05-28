# Snapping Assist System (Canva Snap) Documentation

This document explains the design, coordinate math, snapping logic, and visualization of the Canva-like Snapping Assist system in the Build Mode of the 3D Room Editor.

---

## 1. Snap Logic & Math
Snapping is calculated dynamically along the horizontal X and Z plane whenever an item is actively dragged via direct mouse drag or `TransformControls` gizmo handles.

### Snapping Threshold
We define a threshold distance (in meters) within which the item is pulled exactly to the target axis:
```javascript
const snapThreshold = 0.15; // 15 cm threshold
```

### Snap 1: Center of the Room Snapping
Snaps to the exact room center axes ($X = 0$ or $Z = 0$):
* If $|obj.position.x| < 0.15$ $\implies obj.position.x = 0$
* If $|obj.position.z| < 0.15$ $\implies obj.position.z = 0$

### Snap 2: Object-to-Object Center Alignment
Snaps to align exactly with the center of another object currently in the scene (excluding walls):
* If $|obj.position.x - other.position.x| < 0.15$ $\implies obj.position.x = other.position.x$
* If $|obj.position.z - other.position.z| < 0.15$ $\implies obj.position.z = other.position.z$

---

## 2. Visualization via Glowing Guidelines
When a snap is active, the system draws dashed colored lines in 3D to show the alignment:

1. **Center Snaps**: Rendered as a **Canva Magenta** dashed line (`0xd946ef`) across the room boundary.
2. **Object Snaps**: Rendered as a **Neon Cyan** dashed line (`0x00f0ff`) directly linking the two aligned items.

Guidelines are rendered using `THREE.Line` with a custom `THREE.LineDashedMaterial` and `depthTest = false` (rendering it on top of all furniture so it is always visible to the user).

---

## 3. UI Toggle & Event Flow
* The system is fully toggleable in the **Assets Tab** using the switch:
  ```html
  <input type="checkbox" id="assist-snap-toggle" checked onchange="RenovaEditor.toggleAlignmentAssist(this.checked)">
  ```
* Snaps are actively triggered during `TransformControls` translation `change` event or mouse `doDrag`.
* Snapping guide lines are immediately destroyed on mouse-up / dragging-change end event:
  ```javascript
  clearGuides(engine.scene);
  ```
