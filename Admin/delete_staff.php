<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    // Get staff details first
    $stmt = $conn->prepare("
        SELECT profile_image, license_image, username 
        FROM users 
        WHERE id=:id AND role='staff'
    ");
    $stmt->execute([':id' => $id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff) {

        $username = $staff['username'];

        // Delete profile image
        if (!empty($staff['profile_image']) && file_exists($staff['profile_image'])) {
            unlink($staff['profile_image']);
        }

        // Delete license image
        if (!empty($staff['license_image']) && file_exists($staff['license_image'])) {
            unlink($staff['license_image']);
        }

        // Delete DB record
        $stmt = $conn->prepare("
            DELETE FROM users 
            WHERE id=:id AND role='staff'
        ");

        if ($stmt->execute([':id' => $id])) {

            // 🔔 Notification
            createNotification(
                $conn,
                $_SESSION['user_id'],
                "Staff Deleted",
                "Staff $username deleted successfully.",
                "danger",
                "users",
                $id
            );
        }
    }
}

header("Location: staff_list.php");
exit();
?>