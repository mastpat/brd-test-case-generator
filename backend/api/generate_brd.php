<?php
require_once '../config/database.php';
require_once '../models/Requirement.php';
require_once '../utils/DocumentGenerator.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['requirement_id'])) {
        throw new Exception('Requirement ID is required');
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $requirement = new Requirement($db);
    
    // Get requirement data
    $requirementData = $requirement->getById($input['requirement_id']);
    
    if (!$requirementData) {
        throw new Exception('Requirement not found');
    }
    
    // Generate BRD
    $documentGenerator = new DocumentGenerator();
    $brdFiles = $documentGenerator->generateBRD($requirementData, $input['requirement_id']);
    
    // Update requirement with BRD file paths
    $requirement->updateFiles($input['requirement_id'], [
        'brd_docx' => $brdFiles['docx'] ?? null,
        'brd_pdf' => $brdFiles['pdf'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'BRD generated successfully',
        'files' => $brdFiles
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
