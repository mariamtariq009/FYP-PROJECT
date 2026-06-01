<?php
session_start();
require '../db.php';
require_once 'includes/status_sync.php';
require_once 'includes/duty_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare('SELECT * FROM duties WHERE id=?');
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    $vehicle_id = (int)$data['vehicle_id'];
    $user_id = (int)$data['user_id'];

    $conn->prepare("UPDATE duties SET status='Completed' WHERE id=?")->execute([$id]);

    $conn->prepare("
        UPDATE vehicle_assignments
        SET duty_status='completed', end_time=NOW()
        WHERE vehicle_id=? AND staff_id=? AND duty_status IN ('assigned','on_duty')
    ")->execute([$vehicle_id, $user_id]);

    $sess = $conn->prepare("
        SELECT session_id FROM duty_sessions
        WHERE staff_id = ? AND ended_at IS NULL ORDER BY session_id DESC LIMIT 1
    ");
    $sess->execute([$user_id]);
    $sid = $sess->fetchColumn();
    if ($sid) {
        $conn->prepare("UPDATE duty_sessions SET ended_at = NOW() WHERE session_id = ?")
            ->execute([$sid]);
    }

    $conn->prepare("UPDATE users SET availability_status='available' WHERE id=?")
        ->execute([$user_id]);

    syncAllStatuses($conn);
}

header('Location: manage_duties.php');
exit();
