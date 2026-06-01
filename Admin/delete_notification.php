<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: notifications.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("
    DELETE FROM notifications
    WHERE notification_id = ?
");

$stmt->execute([$id]);

header("Location: notifications.php");
exit();
?>