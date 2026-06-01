<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_GET['id'])){
    header("Location: pol_list.php");
    exit();
}

$id = $_GET['id'];

/* FETCH VEHICLES */
$vehicles = $conn->query("
    SELECT vehicle_id, vehicle_name, vehicle_number, fuel_type 
    FROM vehicles
")->fetchAll(PDO::FETCH_ASSOC);

/* FETCH RECORD */
$stmt = $conn->prepare("SELECT * FROM pol_records WHERE pol_id=:id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$row){
    die("Record not found");
}

/* UPDATE RECORD */
if(isset($_POST['update'])){

    $vehicle_id = $_POST['vehicle_id'] ?? null;
    $fuel_date = $_POST['fuel_date'] ?? null;
    $details = $_POST['details'] ?? '';
    $liters = $_POST['liters'] ?? 0;
    $rate = $_POST['rate'] ?? 0;

    $filter_change = $_POST['filter_change'] ?? 'No';
    $filter_change_type = $_POST['filter_change_type'] ?? '';
    $filter_change_amount = $_POST['filter_change_amount'] ?? 0;

    $gst = $_POST['gst'] ?? 0;
    $pst = $_POST['pst'] ?? 0;

    if($filter_change == "No"){
        $filter_change_type = "None";
        $filter_change_amount = 0;
    }

    $fuel_amount = $liters * $rate;
    $total_amount = $fuel_amount + $filter_change_amount + $gst + $pst;

    $stmt = $conn->prepare("
        UPDATE pol_records SET
            vehicle_id=:vehicle_id,
            fuel_date=:fuel_date,
            details=:details,
            liters=:liters,
            rate=:rate,
            fuel_amount=:fuel_amount,
            filter_change_type=:filter_change_type,
            filter_change_amount=:filter_change_amount,
            gst=:gst,
            pst=:pst,
            total_amount=:total_amount
        WHERE pol_id=:id
    ");

    $stmt->execute([
        ':vehicle_id' => $vehicle_id,
        ':fuel_date' => $fuel_date,
        ':details' => $details,
        ':liters' => $liters,
        ':rate' => $rate,
        ':fuel_amount' => $fuel_amount,
        ':filter_change_type' => $filter_change_type,
        ':filter_change_amount' => $filter_change_amount,
        ':gst' => $gst,
        ':pst' => $pst,
        ':total_amount' => $total_amount,
        ':id' => $id
    ]);


    createNotification(
        $conn,
        $_SESSION['user_id'],
        "Fuel Record Updated",
        "Fuel record updated successfully.",
        "info",
        "pol_records",
        $pol_id
    );

    header("Location: pol_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit POL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
<div class="card shadow p-4">

<h3 class="text-center mb-4">Edit POL Record</h3>

<form method="POST">
<div class="row g-3">

<!-- Vehicle -->
<div class="col-md-4">
<label>Vehicle</label>
<select name="vehicle_id" id="vehicle" class="form-control" required>
<?php foreach($vehicles as $v): ?>
<option
    value="<?= $v['vehicle_id'] ?>"
    data-fuel="<?= $v['fuel_type'] ?>"
    <?= ($row['vehicle_id'] == $v['vehicle_id']) ? 'selected' : '' ?>
>
    <?= $v['vehicle_name'] ?> (<?= $v['vehicle_number'] ?>)
</option>
<?php endforeach; ?>
</select>
</div>

<!-- Fuel Type -->
<div class="col-md-4">
<label>Fuel Type</label>
<input type="text" id="fuel_type" class="form-control" readonly>
</div>

<!-- Date -->
<div class="col-md-4">
<label>Date</label>
<input type="date" name="fuel_date" value="<?= $row['fuel_date'] ?>" class="form-control" required>
</div>

<!-- Details -->
<div class="col-md-6">
<label>Details</label>
<textarea name="details" class="form-control"><?= $row['details'] ?></textarea>
</div>

<!-- Liters -->
<div class="col-md-3">
<label>Liters</label>
<input type="number" step="0.01" id="liters" name="liters" value="<?= $row['liters'] ?>" class="form-control">
</div>

<!-- Rate -->
<div class="col-md-3">
<label>Rate</label>
<input type="number" step="0.01" id="rate" name="rate" value="<?= $row['rate'] ?>" class="form-control">
</div>

<!-- Fuel Amount -->
<div class="col-md-3">
<label>Fuel Amount</label>
<input type="number" id="fuel_amount" value="<?= $row['fuel_amount'] ?>" class="form-control" readonly>
</div>

<!-- Filter Change -->
<div class="col-md-3">
<label>Filter Change</label>
<select name="filter_change" id="filter_change" class="form-control">
<option value="No" <?= ($row['filter_change_type']=="None") ? 'selected' : '' ?>>No</option>
<option value="Yes" <?= ($row['filter_change_type']!="None") ? 'selected' : '' ?>>Yes</option>
</select>
</div>

<div class="col-md-3 filter-box">
<label>Filter Type</label>
<select name="filter_change_type" class="form-control">

<option value="">Select Filter Type</option>

<option value="Oil Filter"
<?= ($row['filter_change_type']=="Oil Filter") ? 'selected' : '' ?>>
Oil Filter
</option>

<option value="Air Filter"
<?= ($row['filter_change_type']=="Air Filter") ? 'selected' : '' ?>>
Air Filter
</option>

<option value="Diesel Filter"
<?= ($row['filter_change_type']=="Diesel Filter") ? 'selected' : '' ?>>
Diesel Filter
</option>

</select>
</div>

<!-- Filter Amount -->
<div class="col-md-3 filter-box">
<label>Filter Amount</label>
<input type="number" step="0.01"
id="filter_amount"
name="filter_change_amount"
value="<?= $row['filter_change_amount'] ?>"
class="form-control">
</div>

<!-- GST -->
<div class="col-md-3">
<label>GST</label>
<input type="number" id="gst" name="gst"
value="<?= $row['gst'] ?>"
class="form-control">
</div>

<!-- PST -->
<div class="col-md-3">
<label>PST</label>
<input type="number" id="pst" name="pst"
value="<?= $row['pst'] ?>"
class="form-control">
</div>

<!-- Total -->
<div class="col-md-3">
<label>Total</label>
<input type="number"
id="total"
value="<?= $row['total_amount'] ?>"
class="form-control"
readonly>
</div>

<div class="col-12 text-center mt-4">
<button type="submit" name="update" class="btn btn-warning px-5">
Update
</button>

<a href="pol_list.php" class="btn btn-secondary px-5">
Back
</a>
</div>

</div>
</form>

</div>
</div>

<script>
function loadFuelType(){
    let vehicle = document.getElementById('vehicle');
    let fuel = vehicle.options[vehicle.selectedIndex].getAttribute('data-fuel');
    document.getElementById('fuel_type').value = fuel;
}

function toggleFilter(){
    let filterChange = document.getElementById('filter_change').value;
    let boxes = document.querySelectorAll('.filter-box');

    if(filterChange === 'Yes'){
        boxes.forEach(box=>box.classList.remove('d-none'));
    }else{
        boxes.forEach(box=>box.classList.add('d-none'));
    }
}

function calc(){

    let liters = parseFloat(document.getElementById('liters').value) || 0;
    let rate = parseFloat(document.getElementById('rate').value) || 0;
    let filter = parseFloat(document.getElementById('filter_amount').value) || 0;
    let gst = parseFloat(document.getElementById('gst').value) || 0;
    let pst = parseFloat(document.getElementById('pst').value) || 0;

    let fuel = liters * rate;
    let total = fuel + filter;

    document.getElementById('fuel_amount').value = fuel.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
}

document.getElementById('vehicle').addEventListener('change', loadFuelType);
document.getElementById('filter_change').addEventListener('change', toggleFilter);

document.querySelectorAll('#liters,#rate,#filter_amount,#gst,#pst')
.forEach(el=>{
    el.addEventListener('input', calc);
});

loadFuelType();
toggleFilter();
</script>

</body>
</html>