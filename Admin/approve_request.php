<?php
session_start();
include('../db.php');
include('includes/notification_helper.php');
require_once 'includes/duty_helper.php';

if (!isset($_GET['id'])) {
    header('Location: manage_request.php');
    exit();
}

$request_id = (int)$_GET['id'];

$stmt = $conn->prepare('SELECT * FROM vehicle_requests WHERE id=?');
$stmt->execute([$request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header('Location: manage_request.php');
    exit();
}

$user_id = (int)$request['user_id'];
$vehicle_id = (int)$request['vehicle_id'];

try {
    $conn->beginTransaction();

    $conn->prepare("UPDATE vehicle_requests SET status='approved' WHERE id=?")
        ->execute([$request_id]);

    createVehicleAssignment(
        $conn,
        $vehicle_id,
        $user_id,
        (int)($_SESSION['user_id'] ?? 1),
        'Approved from vehicle request #' . $request_id
    );

    createNotification(
        $conn,
        $user_id,
        'Vehicle Request Approved',
        'Your request was approved. Start duty when you begin work.',
        'success',
        'vehicle_requests',
        $request_id
    );

    $conn->commit();

    header('Location: manage_request.php');
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    die('Error: ' . htmlspecialchars($e->getMessage()));
}
