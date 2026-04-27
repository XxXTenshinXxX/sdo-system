<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/user-activity.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || !userActivityIsAdminViewer((string) $_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

userActivityMarkCurrentUser();

echo json_encode(['users' => userActivityFetchUsersSnapshot()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
