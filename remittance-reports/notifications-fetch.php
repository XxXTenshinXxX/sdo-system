<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/user-activity.php';

$role = $_SESSION['role'] ?? 'staff';
$notifications = userActivityFetchNotifications($role, 15);

echo json_encode([
    'status' => 'success',
    'notifications' => $notifications,
    'count' => count($notifications)
]);
