<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;
    
    public function __construct() {
        // Try PostgreSQL first (Replit's built-in database)
        if (getenv('DATABASE_URL')) {
            $this->setupPostgreSQL();
        } else {
            // Fallback to SQLite for development
            $this->setupSQLite();
        }
    }

    private function setupPostgreSQL() {
        $databaseUrl = getenv('DATABASE_URL');
        $urlParts = parse_url($databaseUrl);
        
        $this->host = $urlParts['host'];
        $this->db_name = ltrim($urlParts['path'], '/');
        $this->username = $urlParts['user'];
        $this->password = $urlParts['pass'];
    }
    
    private function setupSQLite() {
        $this->db_name = __DIR__ . '/../database/brd_uat_generator.db';
        // Ensure database directory exists
        $dbDir = dirname($this->db_name);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            if (getenv('DATABASE_URL')) {
                // PostgreSQL connection
                $this->conn = new PDO("pgsql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            } else {
                // SQLite connection
                $this->conn = new PDO("sqlite:" . $this->db_name);
            }
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }
    
    private function createTables() {
        $isPostgreSQL = getenv('DATABASE_URL') !== false;
        
        if ($isPostgreSQL) {
            $this->createPostgreSQLTables();
        } else {
            $this->createSQLiteTables();
        }
        
        // Insert default templates if they don't exist
        $this->insertDefaultTemplates();
    }
    
    private function createPostgreSQLTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS requirements (
            id SERIAL PRIMARY KEY,
            project_title VARCHAR(255) NOT NULL,
            requirement_description TEXT NOT NULL,
            change_request TEXT,
            priority VARCHAR(10) DEFAULT 'Medium' CHECK (priority IN ('High', 'Medium', 'Low')),
            delivery_date DATE,
            supporting_files JSON,
            brd_docx_file VARCHAR(255),
            brd_pdf_file VARCHAR(255),
            uat_xlsx_file VARCHAR(255),
            status VARCHAR(20) DEFAULT 'Draft' CHECK (status IN ('Draft', 'Generated', 'Completed')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS test_cases (
            id SERIAL PRIMARY KEY,
            requirement_id INTEGER NOT NULL,
            test_scenario TEXT NOT NULL,
            test_type VARCHAR(10) NOT NULL CHECK (test_type IN ('Positive', 'Negative')),
            expected_result TEXT NOT NULL,
            priority VARCHAR(10) DEFAULT 'Medium' CHECK (priority IN ('High', 'Medium', 'Low')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS document_templates (
            id SERIAL PRIMARY KEY,
            template_name VARCHAR(100) NOT NULL,
            template_type VARCHAR(10) NOT NULL CHECK (template_type IN ('BRD', 'UAT')),
            template_content TEXT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $this->conn->exec($sql);
    }
    
    private function createSQLiteTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS requirements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_title VARCHAR(255) NOT NULL,
            requirement_description TEXT NOT NULL,
            change_request TEXT,
            priority VARCHAR(10) DEFAULT 'Medium',
            delivery_date DATE,
            supporting_files TEXT,
            brd_docx_file VARCHAR(255),
            brd_pdf_file VARCHAR(255),
            uat_xlsx_file VARCHAR(255),
            status VARCHAR(20) DEFAULT 'Draft',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS test_cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            requirement_id INTEGER NOT NULL,
            test_scenario TEXT NOT NULL,
            test_type VARCHAR(10) NOT NULL,
            expected_result TEXT NOT NULL,
            priority VARCHAR(10) DEFAULT 'Medium',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requirement_id) REFERENCES requirements(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS document_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            template_name VARCHAR(100) NOT NULL,
            template_type VARCHAR(10) NOT NULL,
            template_content TEXT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $this->conn->exec($sql);
    }
    
    private function insertDefaultTemplates() {
        try {
            // Check if templates exist
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM document_templates WHERE template_type = ?");
            
            // BRD Template
            $stmt->execute(['BRD']);
            if ($stmt->fetchColumn() == 0) {
                $brdTemplate = "1. Executive Summary\n2. Project Overview\n3. Business Requirements\n4. Functional Requirements\n5. Non-Functional Requirements\n6. Assumptions and Constraints\n7. Acceptance Criteria\n8. Approval and Sign-off";
                
                $stmt = $this->conn->prepare("INSERT INTO document_templates (template_name, template_type, template_content) VALUES (?, ?, ?)");
                $stmt->execute(['Default BRD Template', 'BRD', $brdTemplate]);
            }
            
            // UAT Template
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM document_templates WHERE template_type = ?");
            $stmt->execute(['UAT']);
            if ($stmt->fetchColumn() == 0) {
                $uatTemplate = "Test Case ID | Test Scenario | Test Type | Steps | Expected Result | Priority";
                
                $stmt = $this->conn->prepare("INSERT INTO document_templates (template_name, template_type, template_content) VALUES (?, ?, ?)");
                $stmt->execute(['Default UAT Template', 'UAT', $uatTemplate]);
            }
        } catch (Exception $e) {
            error_log("Error inserting default templates: " . $e->getMessage());
        }
    }
}
?>
