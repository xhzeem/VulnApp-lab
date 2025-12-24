#!/bin/bash

echo "🛑 Stopping Penetration Testing Lab..."
echo ""

docker compose down

echo ""
echo "✅ Lab stopped successfully!"
echo ""
echo "To completely remove all data (reset lab):"
echo "  docker compose down -v"
echo ""
