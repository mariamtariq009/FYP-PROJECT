<?php
session_start();
require '../db.php';
include 'includes/notification_helper.php';
include 'includes/update_vehicle_status.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$message = "";

/* =========================
   VEHICLES + CURRENT ASSIGNED STAFF
========================= */

$vstmt = $conn->prepare("
SELECT 
v.vehicle_id,
v.vehicle_number,
v.vehicle_name,
u.id AS staff_id,
u.username
FROM vehicles v

LEFT JOIN vehicle_assignments va
    ON va.vehicle_id = v.vehicle_id
    AND va.duty_status IN ('assigned','on_duty')

LEFT JOIN users u
    ON u.id = va.staff_id

GROUP BY v.vehicle_id
");

$vstmt->execute();
$vehicles = $vstmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   STAFF LIST (ACTIVE ONLY)
========================= */

$sstmt = $conn->prepare("
SELECT id, username 
FROM users 
WHERE role='staff' 
AND employment_status='active'
");

$sstmt->execute();
$staffs = $sstmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   INSERT REPAIR
========================= */

if(isset($_POST['add'])){

    $stmt = $conn->prepare("
        INSERT INTO repair_history 
        (vehicle_id, staff_id, repair_date, details, description, amount, gst, pst, bill_no, remarks)
        VALUES 
        (:vehicle_id, :staff_id, :repair_date, :details, :description, :amount, :gst, :pst, :bill_no, :remarks)
    ");

    $stmt->execute([

        ':vehicle_id' => $_POST['vehicle_id'],
        ':staff_id' => $_POST['staff_id'],
        ':repair_date' => $_POST['repair_date'],
        ':details' => $_POST['details'] ?? '',
        ':description' => $_POST['description'] ?? '',
        ':amount' => $_POST['amount'] ?: 0,
        ':gst' => $_POST['gst'] ?: 0,
        ':pst' => $_POST['pst'] ?: 0,
        ':bill_no' => $_POST['bill_no'] ?? '',
        ':remarks' => $_POST['remarks'] ?? ''
    ]);

    /* UPDATE VEHICLE STATUS */
    updateVehicleStatus($conn, $_POST['vehicle_id']);

    /* NOTIFICATION */
    createNotification(
        $conn,
        $_SESSION['user_id'],
        "Repair Record Added",
        "New repair record added successfully.",
        "warning",
        "repair_history",
        $conn->lastInsertId()
    );

    $message = "<div class='alert alert-success'>Repair added successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Repair</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php include 'includes/layout.php'; ?>

<div class="content">
<div class="container mt-4">

<h3>Add Repair</h3>

<?= $message ?>

<form method="POST">

<div class="row g-3">

<!-- VEHICLE -->
<div class="col-md-4">
<label>Vehicle</label>

<select id="vehicleSelect" name="vehicle_id" class="form-control" required>

<option value="">Select Vehicle</option>

<?php foreach($vehicles as $v): ?>
<option
value="<?= $v['vehicle_id'] ?>"
data-name="<?= $v['vehicle_name'] ?>"
data-staff="<?= $v['staff_id'] ?>"
>
<?= $v['vehicle_number'] ?>
</option>
<?php endforeach; ?>

</select>
</div>

<!-- VEHICLE NAME -->
<div class="col-md-4">
<label>Vehicle Name</label>
<input type="text" id="vehicleName" class="form-control" readonly>
</div>

<!-- STAFF -->
<div class="col-md-4">
<label>Staff</label>

<select name="staff_id" id="staffSelect" class="form-control" required>

<option value="">Select Staff</option>

<?php foreach($staffs as $s): ?>
<option value="<?= $s['id'] ?>">
<?= $s['username'] ?>
</option>
<?php endforeach; ?>

</select>
</div>

<!-- DATE -->
<div class="col-md-4">
<label>Repair Date</label>
<input type="date" name="repair_date" class="form-control" required>
</div>

<!-- DETAILS -->
<div class="col-md-4">
<label>Details</label>
<input type="text" name="details" class="form-control">
</div>

<!-- DESCRIPTION -->
<div class="col-md-4">
<label>Description</label>
<input type="text" name="description" class="form-control">
</div>

<!-- AMOUNT -->
<div class="col-md-4">
<label>Amount</label>
<input type="number" step="0.01" name="amount" class="form-control">
</div>

<!-- GST -->
<div class="col-md-4">
<label>GST</label>
<input type="number" step="0.01" name="gst" class="form-control">
</div>

<!-- PST -->
<div class="col-md-4">
<label>PST</label>
<input type="number" step="0.01" name="pst" class="form-control">
</div>

<!-- BILL -->
<div class="col-md-4">
<label>Bill No</label>
<input type="text" name="bill_no" class="form-control">
</div>

<!-- REMARKS -->
<div class="col-md-12">
<label>Remarks</label>
<textarea name="remarks" class="form-control"></textarea>
</div>

<!-- BUTTON -->
<div class="col-md-12">
<button type="submit" name="add" class="btn btn-success">
Add Repair
</button>
</div>

</div>

</form>

</div>
</div>

<!-- AUTO FILL -->
<script>
document.getElementById("vehicleSelect").addEventListener("change", function(){

    let selected = this.options[this.selectedIndex];

    document.getElementById("vehicleName").value =
        selected.getAttribute("data-name") || '';

    let staffId = selected.getAttribute("data-staff");

    if(staffId){
        document.getElementById("staffSelect").value = staffId;
    } else {
        document.getElementById("staffSelect").value = '';
    }

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