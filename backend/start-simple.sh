#!/bin/bash
set -e

echo "Starting PHP built-in server for Render..."
echo "PORT environment variable: ${PORT:-not set}"

# Set default port if not provided
PORT=${PORT:-8000}

echo "Starting PHP server on 0.0.0.0:$PORT"

# Start PHP built-in server
exec php -S 0.0.0.0:$PORT -t /app
