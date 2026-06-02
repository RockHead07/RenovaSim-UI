"""
One-time script to assign user_id to existing rooms that have empty user_id.
Run: python fix_room_user_ids.py
"""
import json
import os
import glob

# Change this to the correct user ID for existing rooms
DEFAULT_USER_ID = "1"

DATA_DIR = os.path.join(os.path.dirname(__file__), 'data')

updated = 0
skipped = 0

for filepath in glob.glob(os.path.join(DATA_DIR, 'room_*.json')):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            data = json.load(f)

        if not data.get('user_id'):
            data['user_id'] = DEFAULT_USER_ID
            with open(filepath, 'w', encoding='utf-8') as f:
                json.dump(data, f, indent=2, ensure_ascii=False)
            print(f"Updated: {os.path.basename(filepath)} → user_id={DEFAULT_USER_ID}")
            updated += 1
        else:
            print(f"Skipped: {os.path.basename(filepath)} (already has user_id={data['user_id']})")
            skipped += 1

    except Exception as e:
        print(f"Error processing {filepath}: {e}")

print(f"\nDone. Updated: {updated}, Skipped: {skipped}")
