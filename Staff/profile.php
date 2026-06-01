<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "staff") {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------
| FETCH USER
|--------------------------------------
*/
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------
| LICENSE EXPIRY CHECK
|--------------------------------------
*/
$canUploadLicense = false;

if (!empty($user['license_expiry'])) {
    $today = new DateTime();
    $expiry = new DateTime($user['license_expiry']);
    $diff = $today->diff($expiry)->days;

    if ($expiry >= $today && $diff <= 3) {
        $canUploadLicense = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include("layout.php"); ?>

<div class="content p-4">


<div class="main-content">
<div class="container">

<h3 class="mb-4">My Profile</h3>

<div class="card p-4 shadow-sm">

<div class="row">

<div class="col-md-4 text-center">
    <img src="<?= $user['profile_image'] ?: '../assets/default.png' ?>" width="120" height="120" class="rounded-circle">
    <h5 class="mt-2"><?= $user['name'] ?></h5>
</div>

<div class="col-md-8">

<p><b>Email:</b> <?= $user['email'] ?></p>
<p><b>Phone:</b> <?= $user['phone'] ?></p>
<p><b>Address:</b> <?= $user['address'] ?></p>
<p><b>License No:</b> <?= $user['license_number'] ?></p>
<p><b>Expiry:</b> <?= $user['license_expiry'] ?></p>

<?php if ($canUploadLicense): ?>
<hr>
<h6 class="text-danger">License Expiring Soon - Upload New</h6>

<form action="upload_license.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">

    <input type="text" name="license_number" class="form-control mb-2" placeholder="New License Number" required>
    <input type="date" name="license_expiry" class="form-control mb-2" required>
    <input type="file" name="license_image" class="form-control mb-2" required>

    <button class="btn btn-primary">Update License</button>
</form>

<?php endif; ?>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");

    if (btn && sidebar) {
        btn.addEventListener("click", function () {
            sidebar.classList.toggle("active");
        });
    }
});
</script>
</body>
</html>