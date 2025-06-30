<?php
require_once '../config/database.php';
require_once '../models/Requirement.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Only GET method allowed');
    }
    
    $id = $_GET['id'] ?? null;
    $type = $_GET['type'] ?? null;
    $format = $_GET['format'] ?? null;
    
    if (!$id || !$type || !$format) {
        throw new Exception('Missing required parameters: id, type, format');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $requirement = new Requirement($db);
    
    // Get requirement data
    $requirementData = $requirement->getById($id);
    
    if (!$requirementData) {
        throw new Exception('Requirement not found');
    }
    
    // Determine file path based on type and format
    $fileName = null;
    $filePath = null;
    $contentType = 'application/octet-stream';
    
    switch ($type) {
        case 'brd':
            if ($format === 'docx') {
                $fileName = $requirementData['brd_docx_file'];
                $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            } elseif ($format === 'pdf') {
                $fileName = $requirementData['brd_pdf_file'];
                $contentType = 'application/pdf';
            }
            break;
            
        case 'uat':
            if ($format === 'xlsx') {
                $fileName = $requirementData['uat_xlsx_file'];
                $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }
            break;
    }
    
    if (!$fileName) {
        throw new Exception('File not found for the specified type and format');
    }
    
    $filePath = __DIR__ . '/../exports/' . $fileName;
    
    if (!file_exists($filePath)) {
        throw new Exception('File does not exist on server');
    }
    
    // Set headers for file download
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Output file content
    readfile($filePath);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>