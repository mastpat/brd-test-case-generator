<?php
class DocumentGenerator {
    private $exportDir;
    
    public function __construct() {
        $this->exportDir = __DIR__ . '/../exports/';
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0755, true);
        }
    }
    
    public function generateBRD($requirementData, $requirementId) {
        $files = [];
        
        // Generate Word document
        $files['docx'] = $this->generateWordBRD($requirementData, $requirementId);
        
        // Generate PDF document
        $files['pdf'] = $this->generatePDFBRD($requirementData, $requirementId);
        
        return $files;
    }
    
    private function generateWordBRD($data, $id) {
        // Since we can't install PhpWord without composer, we'll create a simple HTML-based document
        // In a real implementation, you would use PhpWord library
        
        $fileName = 'BRD_' . $id . '_' . time() . '.html';
        $filePath = $this->exportDir . $fileName;
        
        $html = $this->getBRDTemplate($data);
        
        file_put_contents($filePath, $html);
        
        return $fileName;
    }
    
    private function generatePDFBRD($data, $id) {
        // Since we can't install DOMPDF without composer, we'll create a simple HTML document
        // In a real implementation, you would use DOMPDF library
        
        $fileName = 'BRD_' . $id . '_' . time() . '.pdf.html';
        $filePath = $this->exportDir . $fileName;
        
        $html = $this->getBRDTemplate($data, true);
        
        file_put_contents($filePath, $html);
        
        return $fileName;
    }
    
    private function getBRDTemplate($data, $isPDF = false) {
        $currentDate = date('F j, Y');
        $deliveryDate = $data['delivery_date'] ? date('F j, Y', strtotime($data['delivery_date'])) : 'Not specified';
        $supportingFiles = json_decode($data['supporting_files'] ?? '[]', true);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Business Requirements Document</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    margin: 40px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #2c3e50;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .header h1 {
                    color: #2c3e50;
                    font-size: 28px;
                    margin-bottom: 10px;
                }
                .header p {
                    color: #7f8c8d;
                    font-size: 16px;
                }
                .section {
                    margin-bottom: 30px;
                }
                .section h2 {
                    color: #34495e;
                    border-left: 4px solid #3498db;
                    padding-left: 15px;
                    font-size: 20px;
                }
                .section h3 {
                    color: #2c3e50;
                    font-size: 16px;
                    margin-top: 20px;
                }
                .info-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                .info-table th, .info-table td {
                    border: 1px solid #bdc3c7;
                    padding: 12px;
                    text-align: left;
                }
                .info-table th {
                    background-color: #ecf0f1;
                    font-weight: bold;
                    color: #2c3e50;
                }
                .requirements-list {
                    background-color: #f8f9fa;
                    border-left: 4px solid #28a745;
                    padding: 20px;
                    margin: 15px 0;
                }
                .change-request {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 20px;
                    margin: 15px 0;
                }
                .footer {
                    margin-top: 50px;
                    padding-top: 20px;
                    border-top: 2px solid #ecf0f1;
                    text-align: center;
                    color: #7f8c8d;
                }
                .priority-" . strtolower($data['priority']) . " {
                    color: " . $this->getPriorityColor($data['priority']) . ";
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Business Requirements Document</h1>
                <p>" . htmlspecialchars($data['project_title']) . "</p>
                <p>Generated on: " . $currentDate . "</p>
            </div>
            
            <div class='section'>
                <h2>1. Executive Summary</h2>
                <p>This Business Requirements Document (BRD) outlines the detailed requirements for the <strong>" . htmlspecialchars($data['project_title']) . "</strong> project. This document serves as a comprehensive guide for all stakeholders involved in the project development and implementation process.</p>
            </div>
            
            <div class='section'>
                <h2>2. Project Information</h2>
                <table class='info-table'>
                    <tr>
                        <th>Project Title</th>
                        <td>" . htmlspecialchars($data['project_title']) . "</td>
                    </tr>
                    <tr>
                        <th>Priority Level</th>
                        <td><span class='priority-" . strtolower($data['priority']) . "'>" . htmlspecialchars($data['priority']) . "</span></td>
                    </tr>
                    <tr>
                        <th>Expected Delivery Date</th>
                        <td>" . $deliveryDate . "</td>
                    </tr>
                    <tr>
                        <th>Document Version</th>
                        <td>1.0</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>Draft</td>
                    </tr>
                </table>
            </div>
            
            <div class='section'>
                <h2>3. Business Requirements</h2>
                <div class='requirements-list'>
                    <h3>Primary Requirements</h3>
                    <p>" . nl2br(htmlspecialchars($data['requirement_description'])) . "</p>
                </div>
            </div>";
            
        if (!empty($data['change_request'])) {
            $html .= "
            <div class='section'>
                <h2>4. Change Requests</h2>
                <div class='change-request'>
                    <h3>Requested Changes</h3>
                    <p>" . nl2br(htmlspecialchars($data['change_request'])) . "</p>
                </div>
            </div>";
        }
        
        $html .= "
            <div class='section'>
                <h2>5. Functional Requirements</h2>
                <h3>5.1 Core Functionality</h3>
                <ul>
                    <li>System shall meet all specified business requirements</li>
                    <li>User interface shall be intuitive and user-friendly</li>
                    <li>System shall provide appropriate error handling and validation</li>
                    <li>All user inputs shall be validated for security and data integrity</li>
                </ul>
                
                <h3>5.2 Performance Requirements</h3>
                <ul>
                    <li>System response time shall not exceed 3 seconds for standard operations</li>
                    <li>System shall support concurrent users based on priority level</li>
                    <li>System shall maintain 99.9% uptime during business hours</li>
                </ul>
            </div>
            
            <div class='section'>
                <h2>6. Non-Functional Requirements</h2>
                <h3>6.1 Security Requirements</h3>
                <ul>
                    <li>All data transmissions shall be encrypted</li>
                    <li>User authentication and authorization shall be implemented</li>
                    <li>System shall log all user activities for audit purposes</li>
                </ul>
                
                <h3>6.2 Compatibility Requirements</h3>
                <ul>
                    <li>System shall be compatible with modern web browsers</li>
                    <li>Mobile responsiveness shall be implemented for all interfaces</li>
                    <li>System shall integrate with existing infrastructure</li>
                </ul>
            </div>
            
            <div class='section'>
                <h2>7. Assumptions and Constraints</h2>
                <h3>7.1 Assumptions</h3>
                <ul>
                    <li>All required resources will be available as planned</li>
                    <li>Stakeholders will provide timely feedback and approvals</li>
                    <li>Third-party integrations will be available and functional</li>
                </ul>
                
                <h3>7.2 Constraints</h3>
                <ul>
                    <li>Project must be completed within the specified timeframe</li>
                    <li>Solution must work within existing technology stack</li>
                    <li>Budget constraints must be adhered to</li>
                </ul>
            </div>
            
            <div class='section'>
                <h2>8. Acceptance Criteria</h2>
                <ul>
                    <li>All functional requirements must be fully implemented and tested</li>
                    <li>System must pass all UAT test cases</li>
                    <li>Performance benchmarks must be met</li>
                    <li>Security requirements must be validated</li>
                    <li>User documentation must be complete and approved</li>
                </ul>
            </div>";
            
        if (!empty($supportingFiles)) {
            $html .= "
            <div class='section'>
                <h2>9. Supporting Documents</h2>
                <ul>";
            foreach ($supportingFiles as $file) {
                $html .= "<li>" . htmlspecialchars($file) . "</li>";
            }
            $html .= "</ul>
            </div>";
        }
        
        $html .= "
            <div class='section'>
                <h2>10. Approval and Sign-off</h2>
                <table class='info-table'>
                    <tr>
                        <th>Role</th>
                        <th>Name</th>
                        <th>Signature</th>
                        <th>Date</th>
                    </tr>
                    <tr>
                        <td>Business Analyst</td>
                        <td>_________________</td>
                        <td>_________________</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <td>Project Manager</td>
                        <td>_________________</td>
                        <td>_________________</td>
                        <td>_________________</td>
                    </tr>
                    <tr>
                        <td>Stakeholder</td>
                        <td>_________________</td>
                        <td>_________________</td>
                        <td>_________________</td>
                    </tr>
                </table>
            </div>
            
            <div class='footer'>
                <p>This document is confidential and proprietary. Distribution is restricted to authorized personnel only.</p>
                <p>Generated by BRD & UAT Generator System on " . $currentDate . "</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    private function getPriorityColor($priority) {
        switch (strtolower($priority)) {
            case 'high':
                return '#e74c3c';
            case 'medium':
                return '#f39c12';
            case 'low':
                return '#27ae60';
            default:
                return '#95a5a6';
        }
    }
}
?>
