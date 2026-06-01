<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Admin/includes/duty_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'staff') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit();
}

$staffId = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'start') {
    echo json_encode(startStaffDuty($conn, $staffId));
    exit();
}

if ($action === 'end') {
    echo json_encode(endStaffDuty($conn, $staffId));
    exit();
}

http_response_code(400);
echo json_encode(['ok' => false, 'message' => 'Invalid action']);
