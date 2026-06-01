<?php
session_start();
include("../db.php");
include("../Admin/includes/notification_helper.php");

if (!isset($_POST['submit_req'])) {
    header("Location: request_vehicle.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$vehicle_id = $_POST['vehicle_id'];
$comments   = $_POST['comments'];

/*
|--------------------------------------
| INSERT VEHICLE REQUEST (FIXED)
|--------------------------------------
*/
$stmt = $conn->prepare("
    INSERT INTO vehicle_requests (user_id, vehicle_id, comments, status)
    VALUES (?, ?, ?, 'pending')
");

if ($stmt->execute([$user_id, $vehicle_id, $comments])) {

    $request_id = $conn->lastInsertId();

    /*
    |--------------------------------------
    | GET ADMIN ID DYNAMICALLY (FIXED)
    |--------------------------------------
    */
    $adminStmt = $conn->prepare("
        SELECT id 
        FROM users 
        WHERE role = 'admin' 
        LIMIT 1
    ");
    $adminStmt->execute();
    $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

    $admin_id = $admin['id'] ?? 1;

    /*
    |--------------------------------------
    | SEND NOTIFICATION
    |--------------------------------------
    */
    createNotification(
        $conn,
        $admin_id,
        "New Vehicle Request",
        "A staff member submitted a vehicle request.",
        "info",
        "vehicle_requests",
        $request_id
    );

    header("Location: request_vehicle.php?success=1");
    exit();

} else {
    header("Location: request_vehicle.php?error=1");
    exit();
}
?>