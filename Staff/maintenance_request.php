<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "staff") {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Vehicles assigned to staff */
$stmt = $conn->prepare("
    SELECT v.vehicle_id, v.vehicle_number, v.vehicle_name
    FROM vehicle_assignments va
    JOIN vehicles v ON va.vehicle_id = v.vehicle_id
    WHERE va.staff_id = ?
");
$stmt->execute([$user_id]);
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Submit request */
if(isset($_POST['submit'])){

    $vehicle_id = $_POST['vehicle_id'];
    $issue_type = $_POST['issue_type'];
    $description = $_POST['description'];
    $priority = $_POST['priority'];

    $stmt = $conn->prepare("
        INSERT INTO maintenance_requests 
        (vehicle_id, staff_id, issue_type, description, priority, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $vehicle_id,
        $user_id,
        $issue_type,
        $description,
        $priority
    ]);


    $stmt->execute([
    $vehicle_id,
    $user_id,
    $issue_type,
    $description,
    $priority
    ]);

    $request_id = $conn->lastInsertId();

    /* ==========================
    NOTIFY ADMIN
    ========================== */
    $admin = $conn->prepare("
        SELECT id FROM users WHERE role = 'admin' LIMIT 1
    ");
    $admin->execute();
    $admin_id = $admin->fetchColumn();

    $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, type, module, reference_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([
        $admin_id,
        "New Maintenance Request",
        "A staff member submitted a vehicle maintenance request.",
        "info",
        "maintenance",
        $request_id
    ]);
    echo "<script>alert('Maintenance request submitted!'); window.location='maintenance_request.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Maintenance Request</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">

<div class="container-fluid">

<h3 class="mb-3">🔧 Maintenance Request</h3>

<div class="card p-4 shadow-sm">

<form method="POST">

<label>Vehicle</label>
<select name="vehicle_id" class="form-control mb-3" required>
    <option value="">Select Vehicle</option>
    <?php foreach($vehicles as $v): ?>
        <option value="<?= $v['vehicle_id'] ?>">
            <?= $v['vehicle_number'] ?> - <?= $v['vehicle_name'] ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Issue Type</label>
<input type="text" name="issue_type" class="form-control mb-3" placeholder="Engine issue, brake issue..." required>

<label>Description</label>
<textarea name="description" class="form-control mb-3" required></textarea>

<label>Priority</label>
<select name="priority" class="form-control mb-3">
    <option value="low">Low</option>
    <option value="medium">Medium</option>
    <option value="high">High</option>
    <option value="critical">Critical</option>
</select>

<button class="btn btn-dark w-100" name="submit">Submit Request</button>

</form>

</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>