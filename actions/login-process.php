<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo "Please fill all fields.";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            switch($user['role']){
                case 'super admin':
                    header("Location: ../select-dashboard.php");
                    exit;
                case 'admin':
                    header("Location: ../admin-dashboard.php");
                    exit;
                default:
                    header("Location: ../user-dashboard.php");
                    exit;
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
}