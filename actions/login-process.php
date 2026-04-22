<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill all fields.';
        $_SESSION['old_email'] = $email;
        header('Location: ../index.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(TRIM(email)) = ? LIMIT 1");

    if (!$stmt) {
        $_SESSION['login_error'] = 'Login error. Please try again.';
        $_SESSION['old_email'] = $email;
        header('Location: ../index.php');
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    if ($user) {
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            unset($_SESSION['login_error'], $_SESSION['old_email']);

            switch ($user['role']) {
                case 'super admin':
                    header('Location: ../select-dashboard.php');
                    exit;
                case 'admin':
                    header('Location: ../admin-dashboard.php');
                    exit;
                default:
                    header('Location: ../user-dashboard.php');
                    exit;
            }
        } else {
            $_SESSION['login_error'] = 'Invalid password.';
        }
    } else {
        $_SESSION['login_error'] = 'User not found.';
    }

    $_SESSION['old_email'] = $email;
    $stmt->close();
    header('Location: ../index.php');
    exit;
}

header('Location: ../index.php');
exit;
