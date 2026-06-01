<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOTP($toEmail, $otp){

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔴 YOUR GMAIL INFO
        $mail->Username = 'manoeman1015@gmail.com';
        $mail->Password = 'ckfw hepk gxgd fpbt'; // Gmail App Password (NOT normal password)

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('manoeman1015@gmail.com', 'Vehicle System');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = "Your OTP Code";
        $mail->Body = "<h3>Your OTP is: <b>$otp</b></h3>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>