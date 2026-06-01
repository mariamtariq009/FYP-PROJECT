<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include("layout.php"); ?>

<div class="content p-4">

<div class="main-content">

<div class="container">

<h3 class="mb-4">Settings</h3>

<form action="update_settings.php" method="POST" enctype="multipart/form-data" class="card p-4">

<input type="hidden" name="user_id" value="<?= $user_id ?>">

<label>Phone</label>
<input type="text" name="phone" value="<?= $user['phone'] ?>" class="form-control mb-2">

<label>Address</label>
<textarea name="address" class="form-control mb-2"><?= $user['address'] ?></textarea>

<label>Email</label>
<input type="email" name="email" value="<?= $user['email'] ?>" class="form-control mb-2">

<label>Profile Image</label>
<input type="file" name="profile_image" class="form-control mb-3">

<button class="btn btn-success">Update Settings</button>

</form>

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