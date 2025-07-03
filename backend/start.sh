#!/bin/sh

# Start PHP-FPM in the background
php-fpm81 -D

# Start Nginx in the foreground
nginx -g 'daemon off;'
