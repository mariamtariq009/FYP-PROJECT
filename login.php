<?php
session_start();
require 'db.php';

$error = "";

if(isset($_POST['login_btn'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)){
        $error = "Please fill all fields!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(password_verify($password, $user['password'])){

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['name'];

                if($user['role'] == "admin"){
                    header("Location: Admin/dashboard.php");
                } else {
                    header("Location: Staff/dashboard.php");
                }
                exit();

            } else {
                $error = "Incorrect password!";
            }

        } else {
            $error = "Email not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f7f6;
    height: 100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.login-box{
    background:white;
    padding:40px;
    border-radius:15px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    width:400px;
    text-align:center;
}
.btn-dark-custom{
    /* background:#2d3436; */
    color:#fff;
    width:100%;
    padding:10px;
    border:none;
    border-radius:8px;
}
</style>
</head>
<body>

<div class="login-box">

    <h3>Login</h3>
    <p class="text-muted">Enter email & password</p>

    <?php if($error) echo "<p class='text-danger'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

        <button class="btn-dark-custom btn btn-primary" name="login_btn">Login</button>
    </form>

    <a href="forgot_password.php" class="d-block mt-3 text-decoration-none">
        Forgot Password?
    </a>

</div>

</body>
</html>