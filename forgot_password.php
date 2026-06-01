<?php
session_start();
require 'db.php';
require 'mail.php'; // 👈 ADD THIS

$message = "";

if(isset($_POST['send_otp'])){

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if($stmt->rowCount() == 1){

        $otp = rand(100000, 999999);

        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;

        // ✔ REAL EMAIL SEND
        if(sendOTP($email, $otp)){
            header("Location: verify_otp.php");
            exit();
        } else {
            $message = "Email sending failed!";
        }

    } else {
        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

<div class="card p-4" style="width:400px;">
    <h4>Forgot Password</h4>

    <?php if($message) echo "<p class='text-danger'>$message</p>"; ?>

    <form method="POST">
        <input type="email" name="email" class="form-control mb-3" placeholder="Enter email" required>
        <button class="btn btn-dark w-100" name="send_otp">Send OTP</button>
    </form>
</div>

</body>
</html>