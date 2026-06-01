<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Admin/includes/notification_helper.php';
require_once __DIR__ . '/../Admin/includes/status_sync.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'staff') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_POST['submit_emergency'])) {
    header('Location: dashboard.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');
$vehicle_id = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

if ($message === '') {
    header('Location: dashboard.php');
    exit();
}

if ($vehicle_id) {
    $check = $conn->prepare("
        SELECT vehicle_id FROM vehicle_assignments
        WHERE staff_id = ? AND vehicle_id = ? AND duty_status IN ('assigned','on_duty')
    ");
    $check->execute([$user_id, $vehicle_id]);
    if (!$check->fetch()) {
        $vehicle_id = null;
    }
}

if (!$vehicle_id) {
    $stmt = $conn->prepare("
        SELECT vehicle_id FROM vehicle_assignments
        WHERE staff_id = ? AND duty_status IN ('assigned','on_duty')
        ORDER BY assignment_id DESC LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $vehicle_id = $row['vehicle_id'] ?? null;
}

$insert = $conn->prepare("
    INSERT INTO emergency_cases (vehicle_id, staff_id, emergency_type, message, latitude, longitude, status)
    VALUES (?, ?, 'staff_alert', ?, ?, ?, 'active')
");
$insert->execute([$vehicle_id, $user_id, $message, $latitude, $longitude]);
$emergency_id = (int)$conn->lastInsertId();

if ($vehicle_id) {
    $conn->prepare("UPDATE vehicles SET current_status = 'emergency' WHERE vehicle_id = ?")
        ->execute([$vehicle_id]);
}

$admins = $conn->query("SELECT id FROM users WHERE role = 'admin'");
while ($admin = $admins->fetch(PDO::FETCH_ASSOC)) {
    createNotification(
        $conn,
        (int)$admin['id'],
        'Emergency alert',
        'Staff emergency: ' . substr($message, 0, 120),
        'danger',
        'emergency_cases',
        $emergency_id
    );
}

syncAllStatuses($conn);

$_SESSION['flash'] = 'Emergency alert sent. Admin has been notified.';
header('Location: dashboard.php');
exit();
