<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Safe ID check
$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: logbook_list.php");
    exit();
}

/*
Optional:
Delete se pehle record fetch kar lo
taake notification meaningful ho
*/
$get = $conn->prepare("
    SELECT log_id, vehicle_id
    FROM log_book
    WHERE log_id = :id
");
$get->execute([':id' => $id]);
$log = $get->fetch(PDO::FETCH_ASSOC);

if (!$log) {
    header("Location: logbook_list.php");
    exit();
}

// Delete query
$stmt = $conn->prepare("
    DELETE FROM log_book
    WHERE log_id = :id
");

if ($stmt->execute([':id' => $id])) {

    // Notification after delete
    createNotification(
        $conn,
        $_SESSION['user_id'],
        "LogBook Deleted",
        "Logbook record deleted successfully.",
        "danger",
        "log_book",
        $id
    );
}

// Redirect back
header("Location: logbook_list.php");
exit();
?>