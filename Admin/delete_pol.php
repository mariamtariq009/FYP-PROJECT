<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/* VALIDATE ID */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pol_list.php");
    exit();
}

$id = $_GET['id'];

try {

    /* CHECK RECORD EXISTS */
    $check = $conn->prepare("
        SELECT *
        FROM pol_records
        WHERE pol_id = :id
    ");
    $check->execute([':id' => $id]);

    $pol = $check->fetch(PDO::FETCH_ASSOC);

    if (!$pol) {
        header("Location: pol_list.php?msg=notfound");
        exit();
    }

    /* DELETE RECORD */
    $stmt = $conn->prepare("
        DELETE FROM pol_records
        WHERE pol_id = :id
    ");

    if ($stmt->execute([':id' => $id])) {

        /* Notification after delete */
        createNotification(
            $conn,
            $_SESSION['user_id'],
            "Fuel Record Deleted",
            "Fuel record deleted successfully.",
            "danger",
            "pol_records",
            $id
        );
    }

    header("Location: pol_list.php?msg=deleted");
    exit();

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>