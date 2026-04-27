<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/user-activity.php';

userActivityMarkCurrentUser();

echo json_encode(['ok' => true], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
