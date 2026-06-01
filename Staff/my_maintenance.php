<?php
session_start();
include("../db.php");

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT mr.*, v.vehicle_number, v.vehicle_name
    FROM maintenance_requests mr
    JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
    WHERE mr.staff_id = ?
    ORDER BY mr.request_id DESC
");

$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Requests</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">
<div class="container-fluid">

<h3>My Maintenance Requests</h3>

<table class="table table-bordered table-hover mt-3">
<thead>
<tr>
    <th>Vehicle</th>
    <th>Issue</th>
    <th>Priority</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php foreach($requests as $r): ?>

<tr>
    <td><?= $r['vehicle_number'] ?></td>
    <td><?= $r['issue_type'] ?></td>
    <td><?= $r['priority'] ?></td>
    <td>
        <span class="badge bg-info">
            <?= $r['status'] ?>
        </span>
    </td>
    <td><?= $r['requested_at'] ?></td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

</div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>