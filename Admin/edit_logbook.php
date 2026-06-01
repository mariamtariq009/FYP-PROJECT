<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$id = $_GET['id'] ?? 0;

/* ✅ FETCH LOGBOOK */
$stmt = $conn->prepare("SELECT * FROM log_book WHERE log_id=:id");
$stmt->execute([':id'=>$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$row){
    echo "Record not found!";
    exit();
}

/* ✅ VEHICLES (WITH STAFF AUTO LINK) */
$vstmt = $conn->prepare("
SELECT v.vehicle_id, v.vehicle_number, v.vehicle_name,
       va.staff_id, u.username
FROM vehicles v
LEFT JOIN vehicle_assignments va ON va.vehicle_id = v.vehicle_id
    AND va.duty_status IN ('assigned','on_duty')
LEFT JOIN users u ON u.id = va.staff_id
GROUP BY v.vehicle_id
");
$vstmt->execute();
$vehicles = $vstmt->fetchAll(PDO::FETCH_ASSOC);

/* ✅ UPDATE */
if(isset($_POST['update'])){

    $distance = $_POST['meter_end'] - $_POST['meter_start'];
    $consumed = $distance / ($_POST['avg_petrol'] ?: 1);
    $remaining = $_POST['petrol_issued'] - $consumed;

    $stmt = $conn->prepare("
        UPDATE log_book SET 
            vehicle_id=:vehicle_id,
            log_date=:log_date,
            from_location=:from_location,
            to_location=:to_location,
            departure_time=:departure_time,
            return_time=:return_time,
            meter_start=:meter_start,
            meter_end=:meter_end,
            distance=:distance,
            avg_petrol=:avg_petrol,
            petrol_issued=:petrol_issued,
            petrol_consumed=:petrol_consumed,
            remaining_petrol=:remaining_petrol,
            ac_status=:ac_status
        WHERE log_id=:id
    ");

    $stmt->execute([
        ':vehicle_id'=>$_POST['vehicle_id'],
        ':log_date'=>$_POST['log_date'],
        ':from_location'=>$_POST['from_location'],
        ':to_location'=>$_POST['to_location'],
        ':departure_time'=>$_POST['departure_time'],
        ':return_time'=>$_POST['return_time'],
        ':meter_start'=>$_POST['meter_start'],
        ':meter_end'=>$_POST['meter_end'],
        ':distance'=>$distance,
        ':avg_petrol'=>$_POST['avg_petrol'],
        ':petrol_issued'=>$_POST['petrol_issued'],
        ':petrol_consumed'=>$consumed,
        ':remaining_petrol'=>$remaining,
        ':ac_status'=>$_POST['ac_status'],
        ':id'=>$id
    ]);

    createNotification(
        $conn,
        $_SESSION['user_id'],
        "LogBook Updated",
        "Logbook record updated successfully.",
        "info",
        "log_book",
        $log_id
    );

    header("Location: logbook_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Logbook</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


</head>

<body>



<!-- CONTENT -->
<div class="content p-4">

<div class="container">
<div class="card shadow p-4">

<h3 class="text-center mb-4">✏️ Edit Logbook</h3>

<form method="POST">
<div class="row g-3">

<!-- Vehicle -->
<div class="col-md-4">
<label>Vehicle</label>
<select id="vehicleSelect" name="vehicle_id" class="form-control" required>
<?php foreach($vehicles as $v): ?>
<option 
value="<?= $v['vehicle_id'] ?>"
data-name="<?= $v['vehicle_name'] ?>"
data-staff="<?= $v['staff_id'] ?>"
<?= $row['vehicle_id']==$v['vehicle_id']?'selected':'' ?>>
<?= $v['vehicle_number'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<!-- Vehicle Name -->
<div class="col-md-4">
<label>Vehicle Name</label>
<input type="text" id="vehicleName" class="form-control" readonly>
</div>

<!-- Staff -->
<div class="col-md-4">
<label>Staff</label>
<input type="text" id="staffName" class="form-control" readonly>
</div>

<!-- Date -->
<div class="col-md-4">
<label>Date</label>
<input type="date" name="log_date" value="<?= $row['log_date'] ?>" class="form-control">
</div>

<!-- From -->
<div class="col-md-4">
<label>From</label>
<input type="text" name="from_location" value="<?= $row['from_location'] ?>" class="form-control">
</div>

<!-- To -->
<div class="col-md-4">
<label>To</label>
<input type="text" name="to_location" value="<?= $row['to_location'] ?>" class="form-control">
</div>

<!-- Times -->
<div class="col-md-4">
<label>Departure</label>
<input type="time" name="departure_time" value="<?= $row['departure_time'] ?>" class="form-control">
</div>

<div class="col-md-4">
<label>Return</label>
<input type="time" name="return_time" value="<?= $row['return_time'] ?>" class="form-control">
</div>

<!-- Meter -->
<div class="col-md-4">
<label>Meter Start</label>
<input type="number" id="mstart" name="meter_start" value="<?= $row['meter_start'] ?>" class="form-control">
</div>

<div class="col-md-4">
<label>Meter End</label>
<input type="number" id="mend" name="meter_end" value="<?= $row['meter_end'] ?>" class="form-control">
</div>

<!-- Fuel -->
<div class="col-md-4">
<label>Avg Petrol</label>
<input type="number" id="avg" name="avg_petrol" value="<?= $row['avg_petrol'] ?>" class="form-control">
</div>

<div class="col-md-4">
<label>Petrol Issued</label>
<input type="number" id="issued" name="petrol_issued" value="<?= $row['petrol_issued'] ?>" class="form-control">
</div>

<!-- AC -->
<div class="col-md-4">
<label>AC Status</label>
<select name="ac_status" class="form-control">
<option <?= $row['ac_status']=="AC"?"selected":"" ?>>AC</option>
<option <?= $row['ac_status']=="ACX"?"selected":"" ?>>ACX</option>
</select>
</div>

<!-- AUTO -->
<div class="col-md-4">
<label>Distance</label>
<input type="text" id="distance" class="form-control" readonly>
</div>

<div class="col-md-4">
<label>Consumed</label>
<input type="text" id="consumed" class="form-control" readonly>
</div>

<div class="col-md-4">
<label>Remaining</label>
<input type="text" id="remaining" class="form-control" readonly>
</div>

<!-- BUTTON -->
<div class="col-12 text-center mt-4">
<button class="btn btn-primary px-5" name="update">Update</button>
<a href="logbook_list.php" class="btn btn-secondary px-5">Cancel</a>
</div>

</div>
</form>

</div>
</div>
</div>

<script>
function calc(){
let mstart = +document.getElementById("mstart").value || 0;
let mend = +document.getElementById("mend").value || 0;
let avg = +document.getElementById("avg").value || 1;
let issued = +document.getElementById("issued").value || 0;

let distance = mend - mstart;
let consumed = distance / avg;
let remaining = issued - consumed;

document.getElementById("distance").value = distance.toFixed(2);
document.getElementById("consumed").value = consumed.toFixed(2);
document.getElementById("remaining").value = remaining.toFixed(2);
}

document.querySelectorAll("#mstart,#mend,#avg,#issued")
.forEach(el=>el.addEventListener("input",calc));

window.onload = calc;

/* AUTO FILL VEHICLE */
document.getElementById("vehicleSelect").addEventListener("change", function(){
let opt = this.options[this.selectedIndex];

document.getElementById("vehicleName").value = opt.dataset.name || '';
document.getElementById("staffName").value = opt.dataset.staff || '';
});

window.onload = function(){
calc();
document.getElementById("vehicleSelect").dispatchEvent(new Event("change"));
};
</script>

</body>
</html>