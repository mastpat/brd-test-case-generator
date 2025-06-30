<?php
require_once '../config/database.php';
require_once '../models/Requirement.php';
require_once '../utils/DocumentGenerator.php';
require_once '../utils/UATGenerator.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $requirement = new Requirement($db);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle file uploads
        $uploadedFiles = [];
        if (isset($_FILES['supportingDocs']) && !empty($_FILES['supportingDocs']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            for ($i = 0; $i < count($_FILES['supportingDocs']['name']); $i++) {
                if ($_FILES['supportingDocs']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . $_FILES['supportingDocs']['name'][$i];
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['supportingDocs']['tmp_name'][$i], $filePath)) {
                        $uploadedFiles[] = $fileName;
                    }
                }
            }
        }
        
        // Prepare requirement data
        $requirementData = [
            'project_title' => $_POST['projectTitle'] ?? '',
            'requirement_description' => $_POST['requirementDesc'] ?? '',
            'change_request' => $_POST['changeRequest'] ?? '',
            'priority' => $_POST['priority'] ?? 'Medium',
            'delivery_date' => $_POST['deliveryDate'] ?? null,
            'supporting_files' => json_encode($uploadedFiles)
        ];
        
        // Validate required fields
        if (empty($requirementData['project_title']) || empty($requirementData['requirement_description'])) {
            throw new Exception('Project title and requirement description are required');
        }
        
        // Save requirement to database
        $requirementId = $requirement->create($requirementData);
        
        if ($requirementId) {
            // Generate BRD
            $documentGenerator = new DocumentGenerator();
            $brdFiles = $documentGenerator->generateBRD($requirementData, $requirementId);
            
            // Generate UAT
            $uatGenerator = new UATGenerator();
            $uatFile = $uatGenerator->generateUAT($requirementData, $requirementId);
            
            // Update requirement with generated file paths
            $requirement->updateFiles($requirementId, [
                'brd_docx' => $brdFiles['docx'] ?? null,
                'brd_pdf' => $brdFiles['pdf'] ?? null,
                'uat_xlsx' => $uatFile ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Documents generated successfully',
                'requirement_id' => $requirementId,
                'documents' => [
                    'brd_docx' => $brdFiles['docx'] ?? null,
                    'brd_pdf' => $brdFiles['pdf'] ?? null,
                    'uat_xlsx' => $uatFile ?? null
                ],
                'project_title' => $requirementData['project_title'],
                'brd_pages' => 5, // Mock value - would be calculated from actual document
                'uat_cases' => 12 // Mock value - would be calculated from actual test cases
            ]);
        } else {
            throw new Exception('Failed to save requirement');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all requirements
        $requirements = $requirement->getAll();
        
        echo json_encode([
            'success' => true,
            'documents' => $requirements
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
