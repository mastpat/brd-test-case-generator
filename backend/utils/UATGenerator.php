<?php
class UATGenerator {
    private $exportDir;
    
    public function __construct() {
        $this->exportDir = __DIR__ . '/../exports/';
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0755, true);
        }
    }
    
    public function generateUAT($requirementData, $requirementId) {
        // Since we can't install PhpSpreadsheet without composer, we'll create a CSV file
        // In a real implementation, you would use PhpSpreadsheet library
        
        $fileName = 'UAT_TestCases_' . $requirementId . '_' . time() . '.csv';
        $filePath = $this->exportDir . $fileName;
        
        $testCases = $this->generateTestCases($requirementData);
        $this->createCSVFile($filePath, $testCases);
        
        return $fileName;
    }
    
    private function generateTestCases($data) {
        $testCases = [];
        $projectTitle = $data['project_title'];
        $requirements = $data['requirement_description'];
        $changeRequest = $data['change_request'] ?? '';
        
        // Generate comprehensive test cases based on requirements
        $baseTestCases = [
            // Positive Test Cases
            [
                'test_id' => 'TC001',
                'scenario' => 'Verify system login with valid credentials',
                'type' => 'Positive',
                'steps' => '1. Navigate to login page\n2. Enter valid username\n3. Enter valid password\n4. Click login button',
                'expected_result' => 'User should be successfully logged into the system',
                'priority' => 'High',
                'category' => 'Authentication'
            ],
            [
                'test_id' => 'TC002',
                'scenario' => 'Verify main functionality as per requirements',
                'type' => 'Positive',
                'steps' => '1. Access main feature\n2. Perform primary function\n3. Verify results',
                'expected_result' => 'System should perform the main functionality as described in requirements',
                'priority' => $data['priority'],
                'category' => 'Core Functionality'
            ],
            [
                'test_id' => 'TC003',
                'scenario' => 'Verify data validation for required fields',
                'type' => 'Positive',
                'steps' => '1. Access data entry form\n2. Fill all required fields with valid data\n3. Submit form',
                'expected_result' => 'Form should be successfully submitted and data should be saved',
                'priority' => 'High',
                'category' => 'Data Validation'
            ],
            [
                'test_id' => 'TC004',
                'scenario' => 'Verify system performance under normal load',
                'type' => 'Positive',
                'steps' => '1. Perform normal operations\n2. Monitor response times\n3. Check system stability',
                'expected_result' => 'System should respond within acceptable time limits and remain stable',
                'priority' => 'Medium',
                'category' => 'Performance'
            ],
            [
                'test_id' => 'TC005',
                'scenario' => 'Verify user interface responsiveness',
                'type' => 'Positive',
                'steps' => '1. Access system on different devices\n2. Test UI elements\n3. Verify layout adaptation',
                'expected_result' => 'UI should be responsive and adapt to different screen sizes',
                'priority' => 'Medium',
                'category' => 'UI/UX'
            ],
            
            // Negative Test Cases
            [
                'test_id' => 'TC006',
                'scenario' => 'Verify system behavior with invalid login credentials',
                'type' => 'Negative',
                'steps' => '1. Navigate to login page\n2. Enter invalid username\n3. Enter invalid password\n4. Click login button',
                'expected_result' => 'System should display appropriate error message and deny access',
                'priority' => 'High',
                'category' => 'Security'
            ],
            [
                'test_id' => 'TC007',
                'scenario' => 'Verify system behavior with empty required fields',
                'type' => 'Negative',
                'steps' => '1. Access data entry form\n2. Leave required fields empty\n3. Attempt to submit form',
                'expected_result' => 'System should display validation errors and prevent form submission',
                'priority' => 'High',
                'category' => 'Data Validation'
            ],
            [
                'test_id' => 'TC008',
                'scenario' => 'Verify system behavior with invalid data formats',
                'type' => 'Negative',
                'steps' => '1. Enter data in incorrect format\n2. Attempt to save/submit\n3. Verify error handling',
                'expected_result' => 'System should validate data format and display appropriate error messages',
                'priority' => 'Medium',
                'category' => 'Data Validation'
            ],
            [
                'test_id' => 'TC009',
                'scenario' => 'Verify system behavior under excessive load',
                'type' => 'Negative',
                'steps' => '1. Simulate high user load\n2. Perform multiple operations simultaneously\n3. Monitor system response',
                'expected_result' => 'System should handle load gracefully and maintain functionality or display appropriate messages',
                'priority' => 'Medium',
                'category' => 'Performance'
            ],
            [
                'test_id' => 'TC010',
                'scenario' => 'Verify unauthorized access prevention',
                'type' => 'Negative',
                'steps' => '1. Attempt to access restricted areas without proper authentication\n2. Try to perform unauthorized actions',
                'expected_result' => 'System should prevent unauthorized access and display security warnings',
                'priority' => 'High',
                'category' => 'Security'
            ]
        ];
        
        // Add change request specific test cases if available
        if (!empty($changeRequest)) {
            $changeTestCases = [
                [
                    'test_id' => 'TC011',
                    'scenario' => 'Verify implementation of change request requirements',
                    'type' => 'Positive',
                    'steps' => '1. Access modified functionality\n2. Test change request features\n3. Verify integration with existing system',
                    'expected_result' => 'Change request should be implemented as specified without breaking existing functionality',
                    'priority' => $data['priority'],
                    'category' => 'Change Request'
                ],
                [
                    'test_id' => 'TC012',
                    'scenario' => 'Verify backward compatibility after change implementation',
                    'type' => 'Negative',
                    'steps' => '1. Test existing functionality\n2. Verify no regression issues\n3. Check data integrity',
                    'expected_result' => 'Existing functionality should remain unaffected by the changes',
                    'priority' => 'High',
                    'category' => 'Regression'
                ]
            ];
            
            $baseTestCases = array_merge($baseTestCases, $changeTestCases);
        }
        
        return $baseTestCases;
    }
    
    private function createCSVFile($filePath, $testCases) {
        $file = fopen($filePath, 'w');
        
        // Write header
        $header = [
            'Test ID',
            'Test Scenario',
            'Test Type',
            'Test Steps',
            'Expected Result',
            'Priority',
            'Category',
            'Status',
            'Actual Result',
            'Comments'
        ];
        
        fputcsv($file, $header);
        
        // Write test cases
        foreach ($testCases as $testCase) {
            $row = [
                $testCase['test_id'],
                $testCase['scenario'],
                $testCase['type'],
                $testCase['steps'],
                $testCase['expected_result'],
                $testCase['priority'],
                $testCase['category'],
                'Not Executed', // Default status
                '', // Actual result - to be filled during testing
                '' // Comments - to be filled during testing
            ];
            
            fputcsv($file, $row);
        }
        
        fclose($file);
    }
    
    public function generateDetailedUATReport($requirementData, $requirementId) {
        $fileName = 'UAT_Report_' . $requirementId . '_' . time() . '.html';
        $filePath = $this->exportDir . $fileName;
        
        $testCases = $this->generateTestCases($requirementData);
        $html = $this->createHTMLReport($requirementData, $testCases);
        
        file_put_contents($filePath, $html);
        
        return $fileName;
    }
    
    private function createHTMLReport($data, $testCases) {
        $currentDate = date('F j, Y');
        $positiveCount = count(array_filter($testCases, function($tc) { return $tc['type'] === 'Positive'; }));
        $negativeCount = count(array_filter($testCases, function($tc) { return $tc['type'] === 'Negative'; }));
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>UAT Test Cases Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
                .header { text-align: center; border-bottom: 2px solid #3498db; padding-bottom: 20px; margin-bottom: 30px; }
                .header h1 { color: #2c3e50; }
                .summary { background: #ecf0f1; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
                .test-case { border: 1px solid #bdc3c7; margin-bottom: 20px; border-radius: 8px; overflow: hidden; }
                .test-header { background: #3498db; color: white; padding: 15px; font-weight: bold; }
                .test-header.negative { background: #e74c3c; }
                .test-content { padding: 20px; }
                .test-steps { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
                .priority-high { color: #e74c3c; font-weight: bold; }
                .priority-medium { color: #f39c12; font-weight: bold; }
                .priority-low { color: #27ae60; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #bdc3c7; padding: 12px; text-align: left; }
                th { background: #ecf0f1; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>UAT Test Cases Report</h1>
                <h2>" . htmlspecialchars($data['project_title']) . "</h2>
                <p>Generated on: " . $currentDate . "</p>
            </div>
            
            <div class='summary'>
                <h3>Test Summary</h3>
                <table>
                    <tr><th>Total Test Cases</th><td>" . count($testCases) . "</td></tr>
                    <tr><th>Positive Test Cases</th><td>" . $positiveCount . "</td></tr>
                    <tr><th>Negative Test Cases</th><td>" . $negativeCount . "</td></tr>
                    <tr><th>Priority Level</th><td>" . htmlspecialchars($data['priority']) . "</td></tr>
                </table>
            </div>
            
            <h3>Test Cases Details</h3>";
            
        foreach ($testCases as $testCase) {
            $headerClass = $testCase['type'] === 'Negative' ? 'test-header negative' : 'test-header';
            $priorityClass = 'priority-' . strtolower($testCase['priority']);
            
            $html .= "
            <div class='test-case'>
                <div class='" . $headerClass . "'>
                    " . $testCase['test_id'] . " - " . htmlspecialchars($testCase['scenario']) . "
                    <span style='float: right;'>[" . $testCase['type'] . " Test]</span>
                </div>
                <div class='test-content'>
                    <p><strong>Category:</strong> " . $testCase['category'] . "</p>
                    <p><strong>Priority:</strong> <span class='" . $priorityClass . "'>" . $testCase['priority'] . "</span></p>
                    <div class='test-steps'>
                        <strong>Test Steps:</strong><br>
                        " . nl2br(htmlspecialchars($testCase['steps'])) . "
                    </div>
                    <p><strong>Expected Result:</strong> " . htmlspecialchars($testCase['expected_result']) . "</p>
                    <table>
                        <tr>
                            <th>Status</th>
                            <th>Actual Result</th>
                            <th>Comments</th>
                            <th>Tester</th>
                            <th>Date</th>
                        </tr>
                        <tr>
                            <td>Not Executed</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </div>";
        }
        
        $html .= "
        </body>
        </html>";
        
        return $html;
    }
}
?>
