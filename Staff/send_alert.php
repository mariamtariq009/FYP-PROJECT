<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_POST['submit_emergency'])) {

    $user_id = $_SESSION['user_id'];
    $message = $_POST['message'];

    /*
    |--------------------------------------
    | GET ASSIGNED VEHICLE (FIXED LOGIC)
    | vehicle_assignments table use hoga
    |--------------------------------------
    */
    $stmt = $conn->prepare("
        SELECT vehicle_id 
        FROM vehicle_assignments 
        WHERE staff_id = :uid 
        AND duty_status IN ('assigned', 'on_duty')
        ORDER BY assignment_id DESC
        LIMIT 1
    ");

    $stmt->bindParam(':uid', $user_id);
    $stmt->execute();

    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    $vehicle_id = $vehicle['vehicle_id'] ?? null;

    /*
    |--------------------------------------
    | INSERT EMERGENCY (FIXED TABLE)
    | emergency_cases (NOT emergency_alerts)
    |--------------------------------------
    */
    $stmt2 = $conn->prepare("
        INSERT INTO emergency_cases 
        (vehicle_id, staff_id, emergency_type, message, status) 
        VALUES (:vehicle_id, :staff_id, 'manual', :message, 'active')
    ");

    $stmt2->bindParam(':vehicle_id', $vehicle_id);
    $stmt2->bindParam(':staff_id', $user_id);
    $stmt2->bindParam(':message', $message);

    $stmt2->execute();

    echo "<script>
        alert('🚨 Emergency sent successfully!');
        window.location.href='dashboard.php';
    </script>";
}
?>