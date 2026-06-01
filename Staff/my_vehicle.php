<?php 
session_start();
include("../db.php");

// SECURITY CHECK
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "staff"){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------
| FETCH ASSIGNED VEHICLES (FIXED QUERY)
|--------------------------------------
*/
$stmt = $conn->prepare("
    SELECT 
        v.vehicle_id,
        v.vehicle_number,
        v.vehicle_name,
        v.fuel_type,
        v.model_year,
        v.current_status
    FROM vehicle_assignments va
    INNER JOIN vehicles v ON va.vehicle_id = v.vehicle_id
    WHERE va.staff_id = :uid
    ORDER BY va.assignment_id DESC
");

$stmt->bindParam(':uid', $user_id);
$stmt->execute();

$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalVehicles = count($vehicles);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Vehicles</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
body{
    background:#f4f6f9;
}

.content{
    margin-left:30px;
    padding:20px;
}

.card-box{
    border-radius:15px;
}
</style>
</head>

<body>

<?php include 'layout.php'; ?>

<div class="content p-4">
<div class="main-content">
<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">🚗 My Assigned Vehicles</h3>

        <span class="badge bg-dark px-3 py-2">
            Total: <?= $totalVehicles ?>
        </span>
    </div>

    <?php if($totalVehicles > 0): ?>

        <div class="card card-box shadow-sm border-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Vehicle Number</th>
                            <th>Vehicle Name</th>
                            <th>Fuel Type</th>
                            <th>Model Year</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $i = 1; foreach($vehicles as $v): ?>

                            <tr>
                                <td><?= $i++ ?></td>

                                <td class="fw-bold">
                                    <?= htmlspecialchars($v['vehicle_number']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($v['vehicle_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($v['fuel_type']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($v['model_year']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= htmlspecialchars($v['current_status']) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php else: ?>

        <div class="alert alert-info shadow-sm border-0">
            No vehicle assigned to you yet.
        </div>

    <?php endif; ?>

    <?php if ($totalVehicles > 0): ?>
        <div class="alert alert-info mt-3 mb-0">
            <strong>GPS:</strong> After you press <em>Start duty</em> on the dashboard, location is sent automatically every second (no button needed).
        </div>
    <?php endif; ?>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>