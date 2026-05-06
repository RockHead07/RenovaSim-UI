/**
 * Python Editor API Client
 * ========================
 * JavaScript client yang mengintegrasikan dengan Python API server
 * Menggantikan editor.js, editor-advanced.js, dan loader.js
 */

class PythonEditorClient {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || 'http://localhost:5000/api';
        this.roomId = options.roomId || null;
        this.roomData = null;
        this.objects = [];
        this.furniture = {};
        this.mode = 'explore'; // explore atau build
        this.selectedObject = null;
        this.isConnected = false;
        
        console.log('🔧 PythonEditorClient constructor called with options:', options);
        this.init();
    }

    async init() {
        console.log('🚀 Initializing Python Editor Client...');
        console.log(`📍 API URL: ${this.apiUrl}`);
        console.log(`🏠 Room ID: ${this.roomId}`);
        
        try {
            // Check server status
            await this.checkServerStatus();
            
            // Load furniture catalog
            await this.loadFurniture();
            
            // If roomId provided, load room data
            if (this.roomId) {
                await this.loadRoom();
            } else {
                console.warn('⚠️ No roomId provided');
            }
            
            this.isConnected = true;
            console.log('✅ Python Editor Client initialized successfully');
            console.log('📊 Status:', {
                connected: this.isConnected,
                apiUrl: this.apiUrl,
                roomId: this.roomId,
                furnitureCount: Object.keys(this.furniture).length,
                objectsCount: this.objects.length
            });
            this.dispatchEvent('initialized');
        } catch (error) {
            console.error('❌ Initialization failed:', error);
            this.isConnected = false;
            this.dispatchEvent('error', { message: 'Failed to initialize editor: ' + error.message });
        }
    }

    async checkServerStatus() {
        try {
            const response = await fetch(`${this.apiUrl}/status`);
            if (!response.ok) throw new Error('Server not responding');
            
            const data = await response.json();
            console.log('🟢 Server Status:', data);
            return data;
        } catch (error) {
            console.error('❌ Server connection failed:', error);
            throw error;
        }
    }

    async loadFurniture() {
        try {
            const response = await fetch(`${this.apiUrl}/furniture`);
            if (!response.ok) throw new Error('Failed to load furniture');
            
            const data = await response.json();
            this.furniture = data.catalog;
            console.log('🪑 Furniture loaded:', Object.keys(this.furniture).length, 'items');
            return data;
        } catch (error) {
            console.error('❌ Failed to load furniture:', error);
            throw error;
        }
    }

    async loadRoom() {
        if (!this.roomId) {
            console.warn('⚠️ No roomId set');
            return null;
        }

        try {
            const response = await fetch(`${this.apiUrl}/rooms/${this.roomId}`);
            
            if (response.status === 404) {
                console.log(`📝 Room ${this.roomId} not found, creating new...`);
                return this.createNewRoom();
            }
            
            if (!response.ok) throw new Error('Failed to load room');
            
            this.roomData = await response.json();
            this.objects = this.roomData.objects || [];
            console.log(`✅ Room loaded: ${this.roomData.name}`, `Objects: ${this.objects.length}`);
            this.dispatchEvent('roomLoaded', this.roomData);
            return this.roomData;
        } catch (error) {
            console.error('❌ Failed to load room:', error);
            throw error;
        }
    }

    createNewRoom() {
        this.roomData = {
            id: this.roomId,
            name: `Room ${this.roomId}`,
            width: 8,
            length: 10,
            height: 3.2,
            objects: [],
            created_at: new Date().toISOString(),
        };
        this.objects = [];
        this.dispatchEvent('roomCreated', this.roomData);
        return this.roomData;
    }

    async saveRoom(roomData = null) {
        if (!this.roomId) {
            console.warn('⚠️ No roomId set');
            return null;
        }

        try {
            const dataToSave = roomData || {
                ...this.roomData,
                objects: this.objects,
                updated_at: new Date().toISOString(),
            };

            const response = await fetch(`${this.apiUrl}/rooms/${this.roomId}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dataToSave),
            });

            if (!response.ok) throw new Error('Failed to save room');
            
            const result = await response.json();
            console.log('💾 Room saved successfully:', result);
            this.dispatchEvent('roomSaved', result);
            return result;
        } catch (error) {
            console.error('❌ Failed to save room:', error);
            this.dispatchEvent('error', { message: 'Failed to save room' });
            throw error;
        }
    }

    async saveObjects(objects = null) {
        if (!this.roomId) {
            console.warn('⚠️ No roomId set');
            return null;
        }

        try {
            const objectsToSave = objects || this.objects;
            
            const response = await fetch(`${this.apiUrl}/rooms/${this.roomId}/objects`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ objects: objectsToSave }),
            });

            if (!response.ok) throw new Error('Failed to save objects');
            
            const result = await response.json();
            console.log('💾 Objects saved:', result);
            this.dispatchEvent('objectsSaved', result);
            return result;
        } catch (error) {
            console.error('❌ Failed to save objects:', error);
            throw error;
        }
    }

    async getObjects() {
        if (!this.roomId) {
            console.warn('⚠️ No roomId set');
            return [];
        }

        try {
            const response = await fetch(`${this.apiUrl}/rooms/${this.roomId}/objects`);
            if (!response.ok) throw new Error('Failed to get objects');
            
            const data = await response.json();
            this.objects = data.objects || [];
            console.log('📦 Objects loaded:', this.objects.length);
            return this.objects;
        } catch (error) {
            console.error('❌ Failed to get objects:', error);
            throw error;
        }
    }

    addObject(type, position, rotation = [0, 0, 0], scale = null) {
        const furniture = this.furniture[type];
        if (!furniture) {
            console.warn(`⚠️ Unknown furniture type: ${type}`);
            return null;
        }

        const newObject = {
            id: Date.now(),
            type: type,
            position: position,
            rotation: rotation,
            scale: scale || furniture.scale,
            color: furniture.color,
            emoji: furniture.emoji,
            created_at: new Date().toISOString(),
        };

        this.objects.push(newObject);
        console.log('✅ Object added:', type);
        this.dispatchEvent('objectAdded', newObject);
        return newObject;
    }

    updateObject(objectId, updates) {
        const object = this.objects.find(o => o.id === objectId);
        if (!object) {
            console.warn(`⚠️ Object not found: ${objectId}`);
            return null;
        }

        Object.assign(object, updates);
        console.log('✅ Object updated:', objectId);
        this.dispatchEvent('objectUpdated', object);
        return object;
    }

    deleteObject(objectId) {
        const index = this.objects.findIndex(o => o.id === objectId);
        if (index === -1) {
            console.warn(`⚠️ Object not found: ${objectId}`);
            return false;
        }

        const deleted = this.objects.splice(index, 1)[0];
        console.log('✅ Object deleted:', objectId);
        this.dispatchEvent('objectDeleted', deleted);
        return true;
    }

    setMode(mode) {
        if (['explore', 'build'].includes(mode)) {
            this.mode = mode;
            console.log(`🔄 Mode changed to: ${mode.toUpperCase()}`);
            this.dispatchEvent('modeChanged', { mode });
        }
    }

    toggleMode() {
        this.setMode(this.mode === 'explore' ? 'build' : 'explore');
    }

    selectObject(objectId) {
        this.selectedObject = objectId;
        const object = this.objects.find(o => o.id === objectId);
        console.log('👆 Object selected:', object);
        this.dispatchEvent('objectSelected', object);
        return object;
    }

    deselectObject() {
        this.selectedObject = null;
        console.log('👆 Object deselected');
        this.dispatchEvent('objectDeselected');
    }

    clear() {
        this.objects = [];
        this.selectedObject = null;
        console.log('🗑️ Scene cleared');
        this.dispatchEvent('sceneCleared');
    }

    // Event system
    listeners = {};

    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    off(event, callback) {
        if (this.listeners[event]) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        }
    }

    dispatchEvent(event, data = {}) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in event listener for ${event}:`, error);
                }
            });
        }
    }

    // Utility methods
    getRoomDimensions() {
        return {
            width: this.roomData?.width || 8,
            length: this.roomData?.length || 10,
            height: this.roomData?.height || 3.2,
        };
    }

    getObjectCount() {
        return this.objects.length;
    }

    isOnline() {
        return this.isConnected;
    }

    getFurnitureInfo(type) {
        return this.furniture[type] || null;
    }

    getAllFurniture() {
        return this.furniture;
    }
}

// Global instance
window.pyEditor = null;

// Initialize function
function initPythonEditor(roomId, options = {}) {
    const config = {
        roomId: roomId,
        apiUrl: options.apiUrl || 'http://localhost:5000/api',
        ...options
    };

    window.pyEditor = new PythonEditorClient(config);
    return window.pyEditor;
}

console.log('✅ Python Editor Client loaded');
