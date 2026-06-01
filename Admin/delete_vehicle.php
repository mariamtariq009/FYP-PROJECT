<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if($id){

    /* Get vehicle info */
    $stmt = $conn->prepare("
        SELECT vehicle_name, vehicle_number
        FROM vehicles
        WHERE vehicle_id = ?
    ");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if($vehicle){

        /* Delete vehicle */
        $del = $conn->prepare("
            DELETE FROM vehicles
            WHERE vehicle_id = ?
        ");

        if($del->execute([$id])){

            createNotification(
                $conn,
                $_SESSION['user_id'],
                "Vehicle Deleted",
                "Vehicle {$vehicle['vehicle_name']} ({$vehicle['vehicle_number']}) deleted successfully.",
                "danger",
                "vehicles",
                $id
            );
        }
    }
}

header("Location: vehicle_list.php");
exit();
?>