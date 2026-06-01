<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    exit();
}

$staff_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT *
    FROM staff_leaves
    WHERE staff_id = ?
    ORDER BY leave_id DESC
");

$stmt->execute([$staff_id]);

$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Leaves</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">

<h3 class="mb-4">
📋 My Leave Requests
</h3>

<div class="card shadow">

<div class="table-responsive">

<table class="table table-bordered">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Type</th>
<th>Start</th>
<th>End</th>
<th>Reason</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php foreach($leaves as $row): ?>

<tr>

<td><?= $row['leave_id'] ?></td>

<td><?= ucfirst($row['leave_type']) ?></td>

<td><?= $row['start_date'] ?></td>

<td><?= $row['end_date'] ?></td>

<td><?= htmlspecialchars($row['reason']) ?></td>

<td>

<?php
$status = $row['status'];

if($status=='approved'){
    echo '<span class="badge bg-success">Approved</span>';
}
elseif($status=='rejected'){
    echo '<span class="badge bg-danger">Rejected</span>';
}
else{
    echo '<span class="badge bg-warning text-dark">Pending</span>';
}
?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>