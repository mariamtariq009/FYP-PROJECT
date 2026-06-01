<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require __DIR__ . '/other_reports_data.php';

echo json_encode(['rows' => $GLOBALS['other_report_rows'] ?? []]);
