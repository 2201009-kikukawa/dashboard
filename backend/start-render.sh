#!/bin/bash
set -e

# Debug: Show environment
echo "Starting Apache for Render deployment..."
echo "PORT environment variable: ${PORT:-not set}"

# Set default port if not provided by Render
PORT=${PORT:-80}

echo "Configuring Apache to listen on port $PORT"

# Update Apache ports configuration
echo "Listen 0.0.0.0:$PORT" > /etc/apache2/ports.conf

# Update virtual host configuration
cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:$PORT>
    DocumentRoot /var/www/html
    ServerName localhost

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

echo "Apache configuration updated. Starting Apache..."

# Start Apache in foreground
exec apache2-foreground
