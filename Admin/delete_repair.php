<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT repair_id FROM repair_history WHERE repair_id=?");
$stmt->execute([$id]);

if($stmt->fetch()){

    $del = $conn->prepare("DELETE FROM repair_history WHERE repair_id=?");
    $del->execute([$id]);

    createNotification(
        $conn,
        $_SESSION['user_id'],
        "Repair Deleted",
        "Repair record deleted successfully.",
        "danger",
        "repair_history",
        $id
    );
}

header("Location: repair_history.php");
exit();
?>