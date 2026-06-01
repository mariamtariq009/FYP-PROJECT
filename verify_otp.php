<?php
session_start();
require 'db.php';

$error = "";

if(!isset($_SESSION['otp'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['verify'])){

    $otp = $_POST['otp'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if($otp != $_SESSION['otp']){
        $error = "Invalid OTP!";
    }
    elseif($new_pass != $confirm_pass){
        $error = "Passwords do not match!";
    }
    else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = :pass WHERE email = :email");
        $stmt->bindParam(':pass', $hashed);
        $stmt->bindParam(':email', $_SESSION['otp_email']);
        $stmt->execute();

        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);

        header("Location: login.php?reset=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

<div class="card p-4" style="width:400px;">
    <h4>Verify OTP</h4>

    <?php if($error) echo "<p class='text-danger'>$error</p>"; ?>

    <form method="POST">
        <input type="text" name="otp" class="form-control mb-2" placeholder="Enter OTP">

        <input type="password" name="new_password" class="form-control mb-2" placeholder="New Password">

        <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirm Password">

        <button class="btn btn-success w-100" name="verify">Reset Password</button>
    </form>
</div>

</body>
</html>