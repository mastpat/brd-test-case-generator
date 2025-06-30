<?php
// Simple router for the BRD & UAT Generator application
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = ltrim($path, '/');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route handling
if (empty($path) || $path === 'index.html') {
    // Serve the main HTML interface
    header('Content-Type: text/html; charset=utf-8');
    readfile('index.html');
    exit();
}

// API routes
if (strpos($path, 'api/') === 0) {
    // Include database config for all API routes
    require_once 'backend/config/database.php';
    
    switch ($path) {
        case 'api/requirements':
        case 'api/requirements.php':
            require_once 'backend/api/requirements.php';
            break;
            
        case 'api/generate_brd':
        case 'api/generate_brd.php':
            require_once 'backend/api/generate_brd.php';
            break;
            
        case 'api/generate_uat':
        case 'api/generate_uat.php':
            require_once 'backend/api/generate_uat.php';
            break;
            
        case 'api/download':
        case 'api/download.php':
            require_once 'backend/api/download.php';
            break;
            
        default:
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'API endpoint not found',
                'path' => $path
            ]);
            break;
    }
    exit();
}

// 404 for other paths
http_response_code(404);
header('Content-Type: text/html');
echo '<h1>404 - Page Not Found</h1>';
?>