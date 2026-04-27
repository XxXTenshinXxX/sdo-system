<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../admin/includes/pdf-remittance-service.php';
require_once __DIR__ . '/../../includes/user-activity.php';

$section = trim((string) ($_GET['section'] ?? ''));
$philHealthNo = trim((string) ($_GET['philhealth_no'] ?? ''));

if (!in_array($section, ['qes'], true) || $philHealthNo === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $history = remittanceFetchContributionHistory($section, $philHealthNo);
    userActivityLogEvent('view_contribution_history', 'Viewed contribution history', [
        'section' => $section,
        'philhealth_no' => $philHealthNo,
    ]);
    echo json_encode(['history' => $history], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load contribution history right now.']);
}
