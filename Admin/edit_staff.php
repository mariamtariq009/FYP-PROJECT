<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute([':id'=>$id]);
$staff = $stmt->fetch();

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $cnic = $_POST['cnic'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $joining_date = $_POST['joining_date'];

    // NEW FIELDS
    $license_number = $_POST['license_number'];
    $license_expiry = $_POST['license_expiry'];

    $targetDir = "../uploads/";

    // ===== PROFILE IMAGE =====
    $profilePath = $staff['profile_image'];

    if(!empty($_FILES['profile_image']['name'])){
        $profileName = $_FILES['profile_image']['name'];
        $profileTmp = $_FILES['profile_image']['tmp_name'];
        $newProfile = $targetDir . time() . "_" . basename($profileName);

        move_uploaded_file($profileTmp, $newProfile);

        // delete old image
        if(file_exists($profilePath)){
            unlink($profilePath);
        }

        $profilePath = $newProfile;
    }

    // ===== LICENSE IMAGE =====
    $licensePath = $staff['license_image'];

    if(!empty($_FILES['license_image']['name'])){
        $licenseName = $_FILES['license_image']['name'];
        $licenseTmp = $_FILES['license_image']['tmp_name'];
        $newLicense = $targetDir . time() . "_" . basename($licenseName);

        move_uploaded_file($licenseTmp, $newLicense);

        // delete old image
        if(file_exists($licensePath)){
            unlink($licensePath);
        }

        $licensePath = $newLicense;
    }

    // UPDATE QUERY
    $stmt = $conn->prepare("UPDATE users SET 
        name=:name,
        username=:username,
        email=:email,
        cnic=:cnic,
        phone=:phone,
        address=:address,
        joining_date=:joining_date,
        profile_image=:profile_image,
        license_number=:license_number,
        license_image=:license_image,
        license_expiry=:license_expiry
        WHERE id=:id");

    $stmt->execute([
        ':name'=>$name,
        ':username'=>$username,
        ':email'=>$email,
        ':cnic'=>$cnic,
        ':phone'=>$phone,
        ':address'=>$address,
        ':joining_date'=>$joining_date,
        ':profile_image'=>$profilePath,
        ':license_number'=>$license_number,
        ':license_image'=>$licensePath,
        ':license_expiry'=>$license_expiry,
        ':id'=>$id
    ]);
    createNotification(
        $conn,
        $_SESSION['user_id'],
        "Staff Updated",
        "Staff $name details updated successfully.",
        "info",
        "users",
        $staff_id
    );

    header("Location: staff_list.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Staff</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container p-4">
<div class="card shadow p-4">

<h3>Edit Staff</h3>

<form method="POST" enctype="multipart/form-data">

<label ><b>Joining date</b></label><br>
<input class="form-control mb-2" type="date" name="joining_date" value="<?= $staff['joining_date'] ?>">
<label ><b>Full Name</b></label><br>
<input class="form-control mb-2" name="name" value="<?= $staff['name'] ?>" placeholder="Full Name">
<label ><b>Username</b></label><br>
<input class="form-control mb-2" name="username" value="<?= $staff['username'] ?>">
<label ><b>Email</b></label><br>
<input class="form-control mb-2" name="email" value="<?= $staff['email'] ?>">
 <label ><b>CNIC</b></label><br>
<input class="form-control mb-2" name="cnic" value="<?= $staff['cnic'] ?>">
 <label ><b>Phone</b></label><br>
<input class="form-control mb-2" name="phone" value="<?= $staff['phone'] ?>">
 <label ><b>Address</b></label><br>
<input class="form-control mb-2" name="address" value="<?= $staff['address'] ?>">


<!-- LICENSE -->
 <label ><b>license Number</b></label><br>
<input class="form-control mb-2" name="license_number" value="<?= $staff['license_number'] ?>" placeholder="License Number">

<label ><b>license expiry date</b></label><br>
<input class="form-control mb-2" type="date" name="license_expiry" value="<?= $staff['license_expiry'] ?>">

<!-- PROFILE IMAGE -->
<label><b>Profile Image</b></label><br>
<?php if(!empty($staff['profile_image'])): ?>
    <img src="<?= $staff['profile_image'] ?>" width="80" class="mb-2"><br>
<?php endif; ?>
<input class="form-control mb-2" type="file" name="profile_image">

<!-- LICENSE IMAGE -->
<label><b>License Image</b></label><br>
<?php if(!empty($staff['license_image'])): ?>
    <img src="<?= $staff['license_image'] ?>" width="80" class="mb-2"><br>
<?php endif; ?>
<input class="form-control mb-2" type="file" name="license_image">

<button class="btn btn-primary" name="update">Update</button>
<a href="staff_list.php" class="btn btn-secondary">Back</a>

</form>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>