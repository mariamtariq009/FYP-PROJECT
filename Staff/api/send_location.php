<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../Admin/includes/gps_helper.php';
require_once __DIR__ . '/../../Admin/includes/status_sync.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'staff') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = (int)$_SESSION['user_id'];

$user = $conn->prepare('SELECT availability_status FROM users WHERE id = ?');
$user->execute([$userId]);
$avail = $user->fetchColumn();

if ($avail !== 'on_duty') {
    http_response_code(403);
    echo json_encode(['error' => 'Start duty first to send GPS']);
    exit();
}

$input = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($input)) {
    $input = $_POST;
}

$lat = isset($input['latitude']) ? (float)$input['latitude'] : null;
$lng = isset($input['longitude']) ? (float)$input['longitude'] : null;
$speed = isset($input['speed']) ? (float)$input['speed'] : 0.0;

if ($lat === null || $lng === null) {
    http_response_code(400);
    echo json_encode(['error' => 'latitude and longitude required']);
    exit();
}

$stmt = $conn->prepare("
    SELECT v.vehicle_id, v.gps_device_number, v.vehicle_number
    FROM vehicle_assignments va
    JOIN vehicles v ON v.vehicle_id = va.vehicle_id
    WHERE va.staff_id = ? AND va.duty_status = 'on_duty'
    ORDER BY va.assignment_id DESC LIMIT 1
");
$stmt->execute([$userId]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    http_response_code(404);
    echo json_encode(['error' => 'No active on-duty vehicle']);
    exit();
}

$result = logGpsPoint(
    $conn,
    (int)$vehicle['vehicle_id'],
    $lat,
    $lng,
    $speed,
    $vehicle['gps_device_number'] ?: null,
    true
);

syncVehicleStatuses($conn);

echo json_encode(array_merge(['success' => true], $result));
