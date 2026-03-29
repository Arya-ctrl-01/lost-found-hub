<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['status' => false, 'message' => 'Invalid credentials'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = htmlspecialchars($user['name']);
        $_SESSION['role'] = $user['role'];

        return ['status' => true, 'role' => $user['role']];
    }

    public function register($name, $studentId, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->userModel->create($name, $studentId, $email, $hashedPassword);
    }
}
?>
