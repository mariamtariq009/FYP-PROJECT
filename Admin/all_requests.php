<?php
session_start();
include("../db.php");

// security (admin only recommended)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/*
|--------------------------------------
| FETCH ALL REQUESTS (HISTORY)
|--------------------------------------
*/
$stmt = $conn->prepare("
    SELECT vr.*, 
           u.name AS staff_name, 
           v.vehicle_number, 
           v.vehicle_name
    FROM vehicle_requests vr
    LEFT JOIN users u ON vr.user_id = u.id
    LEFT JOIN vehicles v ON vr.bus_id = v.vehicle_id
    ORDER BY vr.id DESC
");

$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Vehicle Requests</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">
<div class="container-fluid">

<div class="card shadow-sm p-3">

<h3 class="mb-3">Vehicle Requests History</h3>

<table class="table table-hover align-middle">

<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Staff</th>
    <th>Vehicle</th>
    <th>Comments</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php if (!empty($requests)): ?>
    <?php foreach ($requests as $row): ?>

        <tr>

            <td><?= $row['id'] ?></td>

            <td><?= htmlspecialchars($row['staff_name'] ?? 'Unknown') ?></td>

            <td>
                <?= htmlspecialchars($row['vehicle_number'] ?? '-') ?>
                <br>
                <small class="text-muted">
                    <?= htmlspecialchars($row['vehicle_name'] ?? '-') ?>
                </small>
            </td>

            <td><?= htmlspecialchars($row['comments'] ?? 'N/A') ?></td>

            <!-- STATUS -->
            <td>
                <?php if ($row['status'] == 'pending'): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php elseif ($row['status'] == 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php elseif ($row['status'] == 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
            </td>

            <td>
                <?= date('d M Y', strtotime($row['request_date'] ?? $row['created_at'])) ?>
            </td>

            <!-- ACTION -->
            <td>

                <?php if ($row['status'] == 'pending'): ?>

                    <a href="approve_request.php?id=<?= $row['id'] ?>&bus_id=<?= $row['bus_id'] ?>&user_id=<?= $row['user_id'] ?>"
                       class="btn btn-success btn-sm">
                        Accept
                    </a>

                    <a href="reject_request.php?id=<?= $row['id'] ?>"
                       class="btn btn-danger btn-sm">
                        Reject
                    </a>

                <?php else: ?>

                    <span class="text-muted">No Action</span>

                <?php endif; ?>

            </td>

        </tr>

    <?php endforeach; ?>
<?php else: ?>

<tr>
    <td colspan="7" class="text-center text-muted">
        No requests found
    </td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>