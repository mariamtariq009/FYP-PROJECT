<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| AVAILABLE STAFF
|--------------------------------------------------------------------------
*/
$staff = $conn->prepare("
    SELECT id, name
    FROM users
    WHERE role = 'staff'
    AND availability_status = 'available'
    ORDER BY name ASC
");
$staff->execute();
$staffList = $staff->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| AVAILABLE VEHICLES
|--------------------------------------------------------------------------
*/
$vehicles = $conn->prepare("
    SELECT vehicle_id, vehicle_name, vehicle_number
    FROM vehicles
    WHERE current_status = 'available'
    ORDER BY vehicle_name ASC
");
$vehicles->execute();
$vehicleList = $vehicles->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ASSIGN DUTY
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id    = $_POST['user_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $route      = $_POST['route_name'];
    $location   = $_POST['location'];
    $duty_date  = $_POST['duty_date'];
    $start      = $_POST['start_time'];
    $end        = $_POST['end_time'];

    // Time validation
    if (strtotime($start) >= strtotime($end)) {
        echo "<script>
                alert('End Time must be greater than Start Time');
                window.history.back();
              </script>";
        exit();
    }

    /*
    ===================================================
    STAFF CONFLICT CHECK
    ===================================================
    */
    $staffCheck = $conn->prepare("
        SELECT id
        FROM duties
        WHERE user_id = ?
        AND duty_date = ?
        AND (
            start_time < ?
            AND end_time > ?
        )
        LIMIT 1
    ");

    $staffCheck->execute([
        $user_id,
        $duty_date,
        $end,
        $start
    ]);

    $staffConflict = $staffCheck->fetch(PDO::FETCH_ASSOC);

    if ($staffConflict) {
        echo "<script>
                alert('This staff member already has a duty during the selected time.');
                window.history.back();
              </script>";
        exit();
    }

    /*
    ===================================================
    VEHICLE CONFLICT CHECK
    ===================================================
    */
    $vehicleCheck = $conn->prepare("
        SELECT id
        FROM duties
        WHERE vehicle_id = ?
        AND duty_date = ?
        AND (
            start_time < ?
            AND end_time > ?
        )
        LIMIT 1
    ");

    $vehicleCheck->execute([
        $vehicle_id,
        $duty_date,
        $end,
        $start
    ]);

    $vehicleConflict = $vehicleCheck->fetch(PDO::FETCH_ASSOC);

    if ($vehicleConflict) {
        echo "<script>
                alert('This vehicle is already assigned during the selected time.');
                window.history.back();
              </script>";
        exit();
    }

    /*
    ===================================================
    INSERT DUTY
    ===================================================
    */
    $insert = $conn->prepare("
        INSERT INTO duties
        (
            user_id,
            vehicle_id,
            route_name,
            location,
            start_time,
            end_time,
            duty_date,
            status
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, 'Active'
        )
    ");

    $insert->execute([
        $user_id,
        $vehicle_id,
        $route,
        $location,
        $start,
        $end,
        $duty_date
    ]);

    // Vehicle status update
    require_once 'includes/duty_helper.php';
    createVehicleAssignment(
        $conn,
        (int)$vehicle_id,
        (int)$user_id,
        (int)$_SESSION['user_id'],
        "Scheduled duty: $route on $duty_date"
    );

    echo "<script>
            alert('Duty Assigned Successfully');
            window.location='manage_duties.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Assign Duty</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="bg-light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<div class="container mt-4">

<div class="card shadow border-0">

<div class="card-header bg-primary text-white">
    <h4 class="mb-0">Assign Staff Duty</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="row">

<!-- STAFF -->
<div class="col-md-6 mb-3">
<label class="form-label">Staff Member</label>
<select name="user_id" class="form-select" required>
<option value="">Select Staff</option>

<?php foreach($staffList as $s): ?>
<option value="<?= $s['id']; ?>">
    <?= htmlspecialchars($s['name']); ?>
</option>
<?php endforeach; ?>

</select>
</div>

<!-- VEHICLE -->
<div class="col-md-6 mb-3">
<label class="form-label">Vehicle</label>
<select name="vehicle_id" class="form-select" required>
<option value="">Select Vehicle</option>

<?php foreach($vehicleList as $v): ?>
<option value="<?= $v['vehicle_id']; ?>">
    <?= htmlspecialchars($v['vehicle_name']); ?>
    (<?= htmlspecialchars($v['vehicle_number']); ?>)
</option>
<?php endforeach; ?>

</select>
</div>

<!-- ROUTE -->
<div class="col-md-6 mb-3">
<label class="form-label">Route</label>
<select name="route_name" class="form-select" required>
<option value="">Select Route</option>
<option>Main Campus → Pars Campus</option>
<option>Pars Campus → Main Campus</option>
<option>Faisalabad → Lahore</option>
<option>Faisalabad → Islamabad</option>
<option>City Duty</option>
<option>Main Gate Duty</option>
</select>
</div>

<!-- LOCATION -->
<div class="col-md-6 mb-3">
<label class="form-label">Location</label>
<select name="location" class="form-select" required>
<option value="">Select Location</option>
<option>Main Campus</option>
<option>Pars Campus</option>
<option>GP Gate</option>
<option>Main Gate</option>
<option>City Trip</option>
</select>
</div>

<!-- DATE -->
<div class="col-md-4 mb-3">
<label class="form-label">Duty Date</label>
<input
    type="date"
    name="duty_date"
    class="form-control"
    min="<?= date('Y-m-d'); ?>"
    required
>
</div>

<!-- START TIME -->
<div class="col-md-4 mb-3">
<label class="form-label">Start Time</label>
<input
    type="time"
    name="start_time"
    class="form-control"
    required
>
</div>

<!-- END TIME -->
<div class="col-md-4 mb-3">
<label class="form-label">End Time</label>
<input
    type="time"
    name="end_time"
    class="form-control"
    required
>
</div>

</div>

<button type="submit" class="btn btn-primary w-100">
    Assign Duty
</button>

</form>

</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>