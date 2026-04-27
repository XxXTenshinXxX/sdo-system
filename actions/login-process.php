<?php
session_start();
require_once '../config/db.php';
require_once '../includes/user-activity.php';

function resolveDashboardByRole(string $role): string
{
    $normalizedRole = strtolower(trim($role));

    return match ($normalizedRole) {
        'super admin' => '../select-dashboard.php',
        'admin', 'user3', 'admin backup', 'backup admin' => '../remittance-reports/user3/dashboard.php',
        'user1' => '../remittance-reports/user1/dashboard.php',
        'user2' => '../remittance-reports/user2/dashboard.php',
        default => '../index.php',
    };
}

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
            $userRole = trim((string) ($user['role'] ?? ''));
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $userRole;
            unset($_SESSION['login_error'], $_SESSION['old_email']);
            userActivityMarkCurrentUser(true);
            userActivityLogEvent('login', 'Logged in successfully', ['redirect' => resolveDashboardByRole($userRole)]);

            $redirectPath = resolveDashboardByRole($userRole);
            if ($redirectPath === '../index.php') {
                unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role']);
                $_SESSION['login_error'] = 'Your account role has no assigned dashboard yet.';
                $_SESSION['old_email'] = $email;
                header('Location: ../index.php');
                exit;
            }

            header('Location: ' . $redirectPath);
            exit;
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
