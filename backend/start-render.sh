#!/bin/bash

# Render provides the PORT environment variable
PORT=${PORT:-80}

# Update Apache configuration to use the PORT
sed -i "s/Listen 80/Listen 0.0.0.0:$PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
