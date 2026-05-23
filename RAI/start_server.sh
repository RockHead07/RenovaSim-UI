#!/bin/bash
# Start Python Editor API Server
# ==============================

echo ""
echo "╔════════════════════════════════════════════════╗"
echo "║  Starting Python Room Editor API Server...    ║"
echo "╚════════════════════════════════════════════════╝"
echo ""

cd "$(dirname "$0")"

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 tidak ditemukan!"
    echo "📥 Install dengan: sudo apt-get install python3"
    exit 1
fi

# Install dependencies
echo "📦 Checking dependencies..."
pip3 install -q flask flask-cors flask-restful 2>/dev/null

if [ $? -ne 0 ]; then
    echo "⚠️ Failed to install dependencies automatically"
    echo "Please run manually:"
    echo "  pip3 install flask flask-cors flask-restful"
fi

# Start the server
echo ""
echo "🚀 Starting server..."
echo "📍 URL: http://localhost:5000"
echo "💡 Press Ctrl+C to stop"
echo ""

python3 app_server.py
