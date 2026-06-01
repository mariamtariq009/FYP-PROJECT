<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$vehicles = $conn->query("
    SELECT vehicle_id, vehicle_number, vehicle_name
    FROM vehicles ORDER BY vehicle_name
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">
<div class="container-fluid">

<h3 class="mb-4">Analytics</h3>

<div class="row mb-3 g-3">
    <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" id="from" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" id="to" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Vehicle</label>
        <select id="vehicle" class="form-select">
            <option value="">All Vehicles</option>
            <?php foreach ($vehicles as $v): ?>
            <option value="<?= (int)$v['vehicle_id'] ?>">
                <?= htmlspecialchars($v['vehicle_number'] . ' - ' . $v['vehicle_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <button onclick="loadData()" class="btn btn-primary w-100 mt-4">Apply</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card p-3 shadow"><h6>Total Expense (PKR)</h6><h3 id="total">0</h3></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow"><h6>POL Expense</h6><h3 id="pol">0</h3></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow"><h6>Repair Expense</h6><h3 id="repair">0</h3></div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow"><h6>Log Entries</h6><h3 id="logs">0</h3></div>
    </div>
</div>

<div class="row mt-4 g-3">
    <div class="col-md-8">
        <div class="card p-3 shadow"><h5>Distance (KM)</h5><canvas id="kmChart"></canvas></div>
    </div>
    <div class="col-md-4">
        <div class="card p-3 shadow"><h5>Expense Split</h5><canvas id="pieChart"></canvas></div>
    </div>
</div>

<div class="row mt-4 g-3">
    <div class="col-md-6">
        <div class="card p-3 shadow"><h5>POL Consumption (Liters)</h5><canvas id="polChart"></canvas></div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 shadow"><h5>Vehicle Categories</h5><canvas id="vehicleChart"></canvas></div>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/analytics.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
