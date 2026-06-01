<?php
session_start();
require '../db.php';

include 'includes/license_expiry_check.php';
checkLicenseExpiry($conn);

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

/* =========================
   COUNTS (FIXED)
========================= */

$totalStaff = $conn->query("
    SELECT COUNT(*) FROM users WHERE role='staff'
")->fetchColumn();

$totalVehicles = $conn->query("
    SELECT COUNT(*) FROM vehicles
")->fetchColumn();

$totalRepairs = $conn->query("
    SELECT COUNT(*) FROM repair_history
")->fetchColumn();

$totalLogs = $conn->query("
    SELECT COUNT(*) FROM log_book
")->fetchColumn();

$totalFuel = $conn->query("
    SELECT COUNT(*) FROM pol_records
")->fetchColumn();

/* 🔥 FIXED: status -> current_status */
$activeVehicles = $conn->query("
    SELECT COUNT(*) 
    FROM vehicles 
    WHERE current_status IN ('available','assigned','on_trip')
")->fetchColumn();

$onDutyStaff = $conn->query("
    SELECT COUNT(*) FROM users WHERE role='staff' AND availability_status='on_duty'
")->fetchColumn();

$pendingBookings = $conn->query("
    SELECT COUNT(*) FROM bookings WHERE status='pending'
")->fetchColumn();

$activeEmergencies = $conn->query("
    SELECT COUNT(*) FROM emergency_cases WHERE status='active'
")->fetchColumn();

$inactiveVehicles = $conn->query("
    SELECT COUNT(*) FROM vehicles WHERE current_status='inactive'
")->fetchColumn();

$pendingLeaves = $conn->query("
    SELECT COUNT(*) FROM staff_leaves WHERE status='pending'
")->fetchColumn();

/* =========================
   RECENT DATA (UNCHANGED)
========================= */

$recentLogs = $conn->query("
    SELECT l.*, v.vehicle_number 
    FROM log_book l
    JOIN vehicles v ON l.vehicle_id = v.vehicle_id
    ORDER BY l.log_date DESC 
    LIMIT 5
");

$recentRepairs = $conn->query("
    SELECT * FROM repair_history 
    ORDER BY repair_date DESC 
    LIMIT 5
");

$recentPol = $conn->query("
    SELECT p.*, v.vehicle_number 
    FROM pol_records p
    JOIN vehicles v ON p.vehicle_id = v.vehicle_id
    ORDER BY p.fuel_date DESC 
    LIMIT 5
");

/* =========================
   TOTALS
========================= */

$totalRepairAmount = $conn->query("
    SELECT SUM(amount) FROM repair_history
")->fetchColumn() ?? 0;

$totalPolAmount = $conn->query("
    SELECT SUM(total_amount) FROM pol_records
")->fetchColumn() ?? 0;

$totalLogDistance = $conn->query("
    SELECT SUM(distance) FROM log_book
")->fetchColumn() ?? 0;

/* =========================
   LICENSE ALERTS (FIXED COLUMN)
========================= */

$licenseAlerts = $conn->query("
    SELECT id, name, license_expiry,
    DATEDIFF(license_expiry, CURDATE()) AS days_left
    FROM users
    WHERE role='staff'
    AND license_expiry IS NOT NULL
    HAVING days_left <= 15
    ORDER BY days_left ASC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="light">


<!-- Navbar & sidebar -->
<?php include 'includes/layout.php'; ?>


<!-- CONTENT -->
<div class="content p-4">
<div class="container-fluid">

<h3 class="mb-4">📊 Dashboard Overview</h3>

<!-- CARDS -->
<div class="row g-3 mb-4">

    <div class="col-md">
        <div class="card bg-primary text-white p-3 shadow text-center">
            <h6>Total Staff</h6>
            <h2><?= $totalStaff ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-success text-white p-3 shadow text-center">
            <h6>Vehicles</h6>
            <h2><?= $totalVehicles ?></h2>
        </div>
    </div>
    <div class="col-md">
        <div class="card bg-secondary text-white p-3 shadow text-center">
            <h6>Active Vehicles</h6>
            <h2><?= $activeVehicles ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-warning text-dark p-3 shadow text-center">
            <h6>Repairs</h6>
            <h2><?= $totalRepairs ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-info text-white p-3 shadow text-center">
            <h6>Log Entries</h6>
            <h2><?= $totalLogs ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-dark text-white p-3 shadow text-center">
            <h6>Fuel Entries</h6>
            <h2><?= $totalFuel ?></h2>
        </div>
    </div>

</div>    
<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="card bg-success text-white p-3 shadow text-center">
            <h6>On Duty Staff</h6>
            <h2><?= $onDutyStaff ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-warning text-dark p-3 shadow text-center">
            <h6>Pending Bookings</h6>
            <h2><?= $pendingBookings ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-danger text-white p-3 shadow text-center">
            <h6>Active Emergencies</h6>
            <h2><?= $activeEmergencies ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-secondary text-white p-3 shadow text-center">
            <h6>Inactive Vehicles</h6>
            <h2><?= $inactiveVehicles ?></h2>
        </div>
    </div>

    <div class="col-md">
        <div class="card bg-info text-white p-3 shadow text-center">
            <h6>Pending Leaves</h6>
            <h2><?= $pendingLeaves ?></h2>
        </div>
    </div>

</div>

<?php if(count($licenseAlerts) > 0): ?>

<div class="card shadow p-3 mb-4 border-danger">

    <h5 class="text-danger">⚠ License Expiry Alerts</h5>

    <div class="table-responsive">
    <table class="table table-sm table-bordered">

        <thead class="table-danger">
            <tr>
                <th>Name</th>
                <th>Expiry Date</th>
                <th>Days Left</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach($licenseAlerts as $row): ?>

            <tr>

                <td><?= htmlspecialchars($row['name']) ?></td>

                <td><?= $row['license_expiry'] ?></td>

                <td>
                    <b><?= $row['days_left'] ?></b>
                </td>

                <td>
                    <?php if($row['days_left'] < 0): ?>
                        <span class="badge bg-danger mt-3">Expired</span>

                    <?php elseif($row['days_left'] == 0): ?>
                        <span class="badge bg-warning text-dark mt-3">Today</span>

                    <?php elseif($row['days_left'] <= 7): ?>
                        <span class="badge bg-danger mt-3">Critical</span>

                    <?php else: ?>
                        <span class="badge bg-warning mt-3">Warning</span>
                    <?php endif; ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>
    </div>

</div>

<?php endif; ?>

<div class="row g-4">

<div class="col-md-6">

<div class="card shadow p-3 mb-3">
<h5>🛠 Total Repair Amount</h5>
<table class="table table-bordered">
<tr><th>Total Amount</th><td>PKR <?= number_format($totalRepairAmount,2) ?></td></tr>
</table>
</div>

<div class="card shadow p-3 mb-3">
<h5>⛽ Total POL Amount</h5>
<table class="table table-bordered">
<tr><th>Total Amount</th><td>PKR <?= number_format($totalPolAmount,2) ?></td></tr>
</table>
</div>

<div class="card shadow p-3 mb-3">
<h5>📈 Trends (Last 12 Months)</h5>
<div id="distanceTrendChart" style="height:260px;"></div>
</div>

<div class="card shadow p-3 mb-3">
<h5>📘 Recent Logbook Entries</h5>
<table class="table table-sm">
<tr><th>Date</th><th>Vehicle</th><th>Distance</th></tr>
<?php while($row = $recentLogs->fetch()){ ?>
<tr>
<td><?= $row['log_date'] ?? '-' ?></td>
<td><?= $row['vehicle_number'] ?? '-' ?></td>
<td><?= $row['distance'] ?? '-' ?></td>
</tr>
<?php } ?>
</table>
</div>

</div>

<div class="col-md-6">

<div class="card shadow p-3 mb-3">
<h5>🛠 Recent Repairs Entries</h5>
<table class="table table-sm">
<tr><th>Date</th><th>Detail</th><th>Amount</th></tr>
<?php while($row = $recentRepairs->fetch()){ ?>
<tr>
<td><?= $row['repair_date'] ?? '-' ?></td>
<td><?= $row['details'] ?? '-' ?></td>
<td>PKR <?= number_format($row['amount'] ?? 0,2) ?></td>
</tr>
<?php } ?>
</table>
</div>

<div class="card shadow p-3 mb-3">
<h5>⛽ Recent POL Records Entries</h5>
<table class="table table-sm">
<tr><th>Date</th><th>Vehicle</th><th>Amount</th></tr>
<?php while($row = $recentPol->fetch()){ ?>
<tr>
<td><?= $row['fuel_date'] ?? '-' ?></td>
<td><?= $row['vehicle_number'] ?? '-' ?></td>
<td>PKR <?= number_format($row['total_amount'] ?? 0,2) ?></td>
</tr>
<?php } ?>
</table>
</div>

<div class="card shadow p-3 mb-3">
<h5>📊 Splits</h5>
<div class="row g-3">
    <div class="col-md-6">
        <div class="border rounded p-2">
            <div class="small text-muted mb-2">Vehicle Types</div>
            <div id="vehicleTypeChart" style="height:220px;"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="border rounded p-2">
            <div class="small text-muted mb-2">Fuel Types</div>
            <div id="fuelTypeChart" style="height:220px;"></div>
        </div>
    </div>
</div>
</div>

</div>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
async function loadDashboardAnalytics() {
  const res = await fetch('api/analytics.php', { credentials: 'same-origin' });
  if (!res.ok) return;
  const data = await res.json();
  if (!data || !data.totals) return;

  // Keep existing PHP cards/tables; charts are dynamic.
  const months = (data.trends?.distance ?? []).map(x => x.ym);
  const distance = (data.trends?.distance ?? []).map(x => Number(x.total_distance || 0));

  new ApexCharts(document.querySelector('#distanceTrendChart'), {
    chart: { type: 'area', height: 260, toolbar: { show: false } },
    series: [{ name: 'Distance', data: distance }],
    xaxis: { categories: months },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } }
  }).render();

  const vehicleTypeLabels = (data.splits?.vehicle_types ?? []).map(x => x.label || 'Unknown');
  const vehicleTypeValues = (data.splits?.vehicle_types ?? []).map(x => Number(x.value || 0));
  new ApexCharts(document.querySelector('#vehicleTypeChart'), {
    chart: { type: 'donut', height: 220 },
    labels: vehicleTypeLabels,
    series: vehicleTypeValues,
    legend: { position: 'bottom' }
  }).render();

  const fuelTypeLabels = (data.splits?.fuel_types ?? []).map(x => x.label || 'Unknown');
  const fuelTypeValues = (data.splits?.fuel_types ?? []).map(x => Number(x.value || 0));
  new ApexCharts(document.querySelector('#fuelTypeChart'), {
    chart: { type: 'donut', height: 220 },
    labels: fuelTypeLabels,
    series: fuelTypeValues,
    legend: { position: 'bottom' }
  }).render();
}

window.addEventListener('DOMContentLoaded', loadDashboardAnalytics);
</script>

</body>
</html>