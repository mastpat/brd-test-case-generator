-- Database: brd_uat_generator

-- Create database
CREATE DATABASE IF NOT EXISTS brd_uat_generator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE brd_uat_generator;

-- Table structure for requirements
CREATE TABLE IF NOT EXISTS requirements (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_title VARCHAR(255) NOT NULL,
    requirement_description TEXT NOT NULL,
    change_request TEXT,
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    delivery_date DATE,
    supporting_files JSON,
    brd_docx_file VARCHAR(255),
    brd_pdf_file VARCHAR(255),
    uat_xlsx_file VARCHAR(255),
    status ENUM('Draft', 'Generated', 'Completed') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for test cases
CREATE TABLE IF NOT EXISTS test_cases (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    requirement_id INT(11) NOT NULL,
    test_scenario TEXT NOT NULL,
    test_type ENUM('Positive', 'Negative') NOT NULL,
    expected_result TEXT NOT NULL,
    priority ENUM('High', 'Medium', 'Low') DEFAULT 'Medium',
    category VARCHAR(100),
    status ENUM('Not Executed', 'Pass', 'Fail', 'Blocked') DEFAULT 'Not Executed',
    actual_result TEXT,
    comments TEXT,
    tester_name VARCHAR(100),
    tested_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for document templates
CREATE TABLE IF NOT EXISTS document_templates (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('BRD', 'UAT') NOT NULL,
    template_content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default BRD template
INSERT INTO document_templates (template_name, template_type, template_content, is_active) VALUES 
('Default BRD Template', 'BRD', '1. Executive Summary\n2. Project Overview\n3. Business Requirements\n4. Functional Requirements\n5. Non-Functional Requirements\n6. Assumptions and Constraints\n7. Acceptance Criteria\n8. Approval and Sign-off', TRUE);

-- Insert default UAT template  
INSERT INTO document_templates (template_name, template_type, template_content, is_active) VALUES 
('Default UAT Template', 'UAT', 'Test Case ID | Test Scenario | Test Type | Steps | Expected Result | Priority | Category | Status | Actual Result | Comments', TRUE);

-- Create indexes for better performance
CREATE INDEX idx_requirements_status ON requirements(status);
CREATE INDEX idx_requirements_priority ON requirements(priority);
CREATE INDEX idx_requirements_created_at ON requirements(created_at);
CREATE INDEX idx_test_cases_requirement_id ON test_cases(requirement_id);
CREATE INDEX idx_test_cases_type ON test_cases(test_type);
CREATE INDEX idx_test_cases_priority ON test_cases(priority);

-- Create a view for requirement summary
CREATE VIEW requirement_summary AS
SELECT 
    r.id,
    r.project_title,
    r.priority,
    r.status,
    r.created_at,
    COUNT(tc.id) as total_test_cases,
    COUNT(CASE WHEN tc.test_type = 'Positive' THEN 1 END) as positive_cases,
    COUNT(CASE WHEN tc.test_type = 'Negative' THEN 1 END) as negative_cases,
    COUNT(CASE WHEN tc.status = 'Pass' THEN 1 END) as passed_cases,
    COUNT(CASE WHEN tc.status = 'Fail' THEN 1 END) as failed_cases
FROM requirements r
LEFT JOIN test_cases tc ON r.id = tc.requirement_id
GROUP BY r.id, r.project_title, r.priority, r.status, r.created_at;
