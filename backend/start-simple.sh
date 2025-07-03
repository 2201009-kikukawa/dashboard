#!/bin/bash
set -e

echo "Starting PHP built-in server for Render deployment..."
echo "PORT environment variable: ${PORT:-not set}"

# Use PORT from environment or default to 8000
PORT=${PORT:-8000}

echo "Starting PHP server on 0.0.0.0:$PORT with router"
echo "Document root: /app"

# Start PHP built-in server with router for proper routing
exec php -S 0.0.0.0:$PORT -t /app /app/router.php
