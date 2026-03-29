<?php
require_once __DIR__ . '/../../config/db.php';

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $studentId, $email, $password) {
        $stmt = $this->conn->prepare("INSERT INTO users (name, student_id, email, password, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->bind_param('ssss', $name, $studentId, $email, $password);
        return $stmt->execute();
    }
}
?>
