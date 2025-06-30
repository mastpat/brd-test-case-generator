<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session for debugging
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once 'config/database.php';

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = '/';
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// Route handling
$path = ltrim($path, '/');
switch ($path) {
    case '':
    case 'index.php':
        // Serve the HTML interface with proper headers
        header('Content-Type: text/html; charset=utf-8');
        readfile('../index.html');
        break;
        
    case 'api/requirements':
    case 'api/requirements.php':
        require_once 'api/requirements.php';
        break;
        
    case 'api/generate_brd':
    case 'api/generate_brd.php':
        require_once 'api/generate_brd.php';
        break;
        
    case 'api/generate_uat':
    case 'api/generate_uat.php':
        require_once 'api/generate_uat.php';
        break;
        
    case 'api/download':
    case 'api/download.php':
        require_once 'api/download.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'path' => $path,
            'request_uri' => $_SERVER['REQUEST_URI']
        ]);
        break;
}
?>
