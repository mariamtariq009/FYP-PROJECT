<?php
require 'db.php';

$username = "admin";
$email = "admin@example.com";
$password = password_hash("Admin@123", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (username,email,password,role) VALUES (:username,:email,:password,:role)");
$stmt->execute([':username'=>$username, ':email'=>$email, ':password'=>$password, ':role'=>$role]);

echo "Admin created successfully!";
?>