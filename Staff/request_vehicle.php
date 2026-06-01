<?php 
session_start();
include("../db.php");
include("../Admin/includes/notification_helper.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "staff"){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------
| GET ONLY AVAILABLE VEHICLES
|--------------------------------------
| correct column = current_status
*/
$stmt = $conn->prepare("
    SELECT vehicle_id, vehicle_number, vehicle_name 
    FROM vehicles 
    WHERE current_status = 'available'
");
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$myRequests = $conn->prepare("
    SELECT vr.*, v.vehicle_number, v.vehicle_name
    FROM vehicle_requests vr
    LEFT JOIN vehicles v ON v.vehicle_id = vr.vehicle_id
    WHERE vr.user_id = ?
    ORDER BY vr.request_date DESC
");
$myRequests->execute([$user_id]);
$requestHistory = $myRequests->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------
| HANDLE REQUEST SUBMISSION
|--------------------------------------
*/
if(isset($_POST['submit_req'])){

    $vehicle_id = $_POST['vehicle_id'];
    $comments   = $_POST['comments'];

    // 1. INSERT REQUEST (FIXED vehicle_id column)
    $insert = $conn->prepare("
        INSERT INTO vehicle_requests (user_id, vehicle_id, comments, status)
        VALUES (:user_id, :vehicle_id, :comments, 'pending')
    ");

    $insert->bindParam(':user_id', $user_id);
    $insert->bindParam(':vehicle_id', $vehicle_id);
    $insert->bindParam(':comments', $comments);

    $insert->execute();

    $request_id = $conn->lastInsertId();

    // 2. GET ADMIN
    $adminQuery = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $adminQuery->execute();
    $admin = $adminQuery->fetch(PDO::FETCH_ASSOC);
    $admin_id = $admin['id'] ?? 1;

    // 3. NOTIFICATION
    createNotification(
        $conn,
        $admin_id,
        "New Vehicle Request",
        "Staff requested a vehicle.",
        "info",
        "vehicle_requests",
        $request_id
    );

    echo "<script>
        alert('Request Submitted Successfully');
        window.location='request_vehicle.php';
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vehicle Request</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="light">

<?php include 'layout.php'; ?>

<div class="content p-4">
<div class="main-content">
<div class="container-fluid">

<h3 class="fw-bold mb-2">🚗 Request Vehicle</h3>
<p class="text-muted mb-4">Only available vehicles can be requested.</p>

<div class="row justify-content-center">
<div class="col-md-7">

<div class="card shadow-sm border-0 p-4" style="border-radius:15px;">

<form method="POST">

<!-- VEHICLE -->
<div class="mb-3">
<label class="form-label fw-bold">Select Available Vehicle</label>

<select name="vehicle_id" class="form-select" required>
    <option disabled selected>Choose vehicle</option>

    <?php foreach($vehicles as $v): ?>
        <option value="<?= $v['vehicle_id'] ?>">
            <?= htmlspecialchars($v['vehicle_number']) ?> - 
            <?= htmlspecialchars($v['vehicle_name']) ?>
        </option>
    <?php endforeach; ?>

</select>
</div>

<!-- COMMENTS -->
<div class="mb-3">
<label class="form-label fw-bold">Purpose</label>
<textarea name="comments" class="form-control" rows="4" required></textarea>
</div>

<button type="submit" name="submit_req" class="btn btn-dark w-100">
    Submit Request
</button>

</form>

</div>

<div class="card shadow-sm border-0 p-4 mt-4">
    <h5>My request history</h5>
    <?php if (empty($requestHistory)): ?>
        <p class="text-muted mb-0">No requests yet.</p>
    <?php else: ?>
    <table class="table table-sm">
        <thead><tr><th>Date</th><th>Vehicle</th><th>Status</th><th>Purpose</th></tr></thead>
        <tbody>
        <?php foreach ($requestHistory as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['request_date']) ?></td>
            <td><?= htmlspecialchars(($r['vehicle_number'] ?? '') . ' ' . ($r['vehicle_name'] ?? '')) ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span></td>
            <td><?= htmlspecialchars($r['comments'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</div>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>