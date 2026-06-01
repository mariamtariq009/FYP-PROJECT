<?php
session_start();
include("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password']; // Form mein password ki field lazmi honi chahiye

    // Agar password ki field bhari hui hai, toh password bhi update karo
    if(!empty($password)) {
        $update_query = "UPDATE users SET name='$name', email='$email', phone='$phone', password='$password' WHERE id='$user_id'";
    } else {
        // Agar password khali hai, toh sirf baqi cheezain update karo (password ko mat chhero)
        $update_query = "UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id='$user_id'";
    }
    
    if(mysqli_query($conn, $update_query)) {
        echo "<script>alert('Profile Updated Successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>