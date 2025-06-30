<?php
class Requirement {
    private $conn;
    private $table_name = "requirements";
    
    public $id;
    public $project_title;
    public $requirement_description;
    public $change_request;
    public $priority;
    public $delivery_date;
    public $supporting_files;
    public $brd_docx_file;
    public $brd_pdf_file;
    public $uat_xlsx_file;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (project_title, requirement_description, change_request, priority, delivery_date, supporting_files, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'Draft')";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $project_title = htmlspecialchars(strip_tags($data['project_title']));
        $requirement_description = htmlspecialchars(strip_tags($data['requirement_description']));
        $change_request = htmlspecialchars(strip_tags($data['change_request'] ?? ''));
        $priority = htmlspecialchars(strip_tags($data['priority']));
        $delivery_date = $data['delivery_date'] ? date('Y-m-d', strtotime($data['delivery_date'])) : null;
        $supporting_files = $data['supporting_files'];

        $stmt->bindParam(1, $project_title);
        $stmt->bindParam(2, $requirement_description);
        $stmt->bindParam(3, $change_request);
        $stmt->bindParam(4, $priority);
        $stmt->bindParam(5, $delivery_date);
        $stmt->bindParam(6, $supporting_files);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $requirements = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['supporting_files'] = json_decode($row['supporting_files'], true);
            $requirements[] = $row;
        }

        return $requirements;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $row['supporting_files'] = json_decode($row['supporting_files'], true);
            return $row;
        }

        return false;
    }

    public function updateFiles($id, $files) {
        $setParts = [];
        $params = [];
        
        if (isset($files['brd_docx'])) {
            $setParts[] = "brd_docx_file = ?";
            $params[] = $files['brd_docx'];
        }
        
        if (isset($files['brd_pdf'])) {
            $setParts[] = "brd_pdf_file = ?";
            $params[] = $files['brd_pdf'];
        }
        
        if (isset($files['uat_xlsx'])) {
            $setParts[] = "uat_xlsx_file = ?";
            $params[] = $files['uat_xlsx'];
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $setParts[] = "status = 'Generated'";
        $params[] = $id;
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->bindParam(2, $id);

        return $stmt->execute();
    }
}
?>
