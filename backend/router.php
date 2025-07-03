<?php
// Router for PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle static files (uploads)
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico)$/', $uri)) {
    return false; // Serve static files as-is
}

// Route all API requests to index.php
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Let the server handle existing files
}

// All other requests go to index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
include __DIR__ . '/index.php';
?>
