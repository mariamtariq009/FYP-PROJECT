<?php
session_start();
require '../db.php';
include 'includes/notification_helper.php';
include 'includes/update_vehicle_status.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Vehicles
$vstmt = $conn->prepare("SELECT * FROM vehicles");
$vstmt->execute();
$vehicles = $vstmt->fetchAll(PDO::FETCH_ASSOC);

// ADD
if(isset($_POST['add'])){

    $distance = $_POST['meter_end'] - $_POST['meter_start'];
    $avg = $_POST['avg_petrol'] ?: 1;

    $consumed = $distance / $avg;
    $remaining = $_POST['petrol_issued'] - $consumed;

    $stmt = $conn->prepare("INSERT INTO log_book 
    (vehicle_id, log_date, from_location, to_location, departure_time, return_time,
    meter_start, meter_end, distance, avg_petrol, petrol_issued, petrol_consumed,
    remaining_petrol, ac_status)
    VALUES
    (:vehicle_id, :log_date, :from_location, :to_location, :departure_time, :return_time,
    :meter_start, :meter_end, :distance, :avg_petrol, :petrol_issued, :petrol_consumed,
    :remaining_petrol, :ac_status)");

    $stmt->execute([
        ':vehicle_id' => $_POST['vehicle_id'],
        ':log_date' => $_POST['log_date'],
        ':from_location' => $_POST['from_location'],
        ':to_location' => $_POST['to_location'],
        ':departure_time' => $_POST['departure_time'],
        ':return_time' => $_POST['return_time'],
        ':meter_start' => $_POST['meter_start'],
        ':meter_end' => $_POST['meter_end'],
        ':distance' => $distance,
        ':avg_petrol' => $_POST['avg_petrol'],
        ':petrol_issued' => $_POST['petrol_issued'],
        ':petrol_consumed' => $consumed,
        ':remaining_petrol' => $remaining,
        ':ac_status' => $_POST['ac_status']
    ]);

    updateVehicleStatus($conn, $_POST['vehicle_id']);
    
    createNotification(
        $conn,
        $_SESSION['user_id'],
        "LogBook Added",
        "New Logbook entry added successfully.",
        "info",
        "log_book",
        $conn->lastInsertId()
    );

    header("Location: logbook_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Log Book</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>


<!-- Navbar & sidebar -->
<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<h3>Add Log Entry</h3>

<form method="POST">

<select name="vehicle_id" class="form-control mb-2" required>
<option value="">Select Vehicle</option>
<?php foreach($vehicles as $v): ?>
<option value="<?= $v['vehicle_id'] ?>">
<?= $v['vehicle_number'] ?> - <?= $v['vehicle_name'] ?>
</option>
<?php endforeach; ?>
</select>

<input type="date" name="log_date" class="form-control mb-2" required>

<input type="text" name="from_location" placeholder="From" class="form-control mb-2">
<input type="text" name="to_location" placeholder="To" class="form-control mb-2">

<input type="time" name="departure_time" class="form-control mb-2">
<input type="time" name="return_time" class="form-control mb-2">

<input type="number" id="mstart" name="meter_start" placeholder="Meter Start" class="form-control mb-2">
<input type="number" id="mend" name="meter_end" placeholder="Meter End" class="form-control mb-2">

<input type="number" id="avg" name="avg_petrol" placeholder="Avg Petrol" class="form-control mb-2">
<input type="number" id="issued" name="petrol_issued" placeholder="Issued Petrol" class="form-control mb-2">

<select name="ac_status" class="form-control mb-2">
<option>AC</option>
<option>NON-AC</option>
</select>

<input type="text" id="distance" class="form-control mb-2" placeholder="Distance" readonly>
<input type="text" id="consumed" class="form-control mb-2" placeholder="Comsumed Petrol" readonly>
<input type="text" id="remaining" class="form-control mb-2" placeholder="Remaining Petrol" readonly>

<button name="add" class="btn btn-success">Add</button>

</form>
</div>

<script>
function calc(){
let m1 = +mstart.value || 0;
let m2 = +mend.value || 0;
let avg = +avg.value || 1;
let issued = +issued.value || 0;

let dist = m2 - m1;
let cons = dist / avg;
let rem = issued - cons;

distance.value = dist;
consumed.value = cons.toFixed(2);
remaining.value = rem.toFixed(2);
}

document.querySelectorAll("#mstart,#mend,#avg,#issued").forEach(e=>{
e.addEventListener("input", calc);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("toggle").onclick = function(){
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>