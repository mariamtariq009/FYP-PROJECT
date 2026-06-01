<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM repair_history WHERE repair_id=?");
$stmt->execute([$id]);
$repair = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$repair){
    die("Not found");
}

/* vehicles + assigned staff */
$vstmt = $conn->query("
SELECT 
v.vehicle_id,
v.vehicle_number,
v.vehicle_name,
ua.staff_id,
u.name
FROM vehicles v
LEFT JOIN vehicle_assignments ua
    ON ua.vehicle_id = v.vehicle_id
    AND ua.duty_status IN ('assigned','on_duty')
LEFT JOIN users u ON u.id = ua.staff_id
GROUP BY v.vehicle_id
");

$vehicles = $vstmt->fetchAll(PDO::FETCH_ASSOC);

/* staff */
$sstmt = $conn->query("SELECT id,name FROM users WHERE role='staff'");
$staffs = $sstmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['update'])){

    $stmt = $conn->prepare("
        UPDATE repair_history SET
        vehicle_id=:vehicle_id,
        staff_id=:staff_id,
        repair_date=:repair_date,
        details=:details,
        description=:description,
        amount=:amount,
        gst=:gst,
        pst=:pst,
        bill_no=:bill_no,
        remarks=:remarks
        WHERE repair_id=:id
    ");

    $stmt->execute([
        ':vehicle_id'=>$_POST['vehicle_id'],
        ':staff_id'=>$_POST['staff_id'],
        ':repair_date'=>$_POST['repair_date'],
        ':details'=>$_POST['details'],
        ':description'=>$_POST['description'],
        ':amount'=>$_POST['amount'],
        ':gst'=>$_POST['gst'],
        ':pst'=>$_POST['pst'],
        ':bill_no'=>$_POST['bill_no'],
        ':remarks'=>$_POST['remarks'],
        ':id'=>$id
    ]);

    createNotification(
        $conn,
        $_SESSION['user_id'],
        "Repair Updated",
        "Repair record updated successfully.",
        "info",
        "repair_history",
        $id
    );

    header("Location: repair_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Repair</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


</head>

<body>

<div class="container mt-5">
<div class="card shadow p-4">

<h3 class="mb-4 text-center">✏️ Edit Repair</h3>

<form method="POST">
<div class="row g-3">

<!-- 🔥 Vehicle Number -->
<div class="col-md-4">
<label>Vehicle Number</label>
<select id="vehicleSelect" name="vehicle_id" class="form-control" required>

<?php foreach($vehicles as $v): ?>
<option 
value="<?= $v['vehicle_id'] ?>"
data-name="<?= $v['vehicle_name'] ?>"
data-staff="<?= $v['staff_id'] ?>"
<?= $repair['vehicle_id']==$v['vehicle_id']?'selected':'' ?>
>
<?= $v['vehicle_number'] ?>
</option>
<?php endforeach; ?>

</select>
</div>

<!-- 🔥 Vehicle Name -->
<div class="col-md-4">
<label>Vehicle Name</label>
<input type="text" id="vehicleName" class="form-control" readonly>
</div>

<!-- 🔥 Staff -->
<div class="col-md-4">
<label>Staff</label>
<select name="staff_id" id="staffSelect" class="form-control" required>
<?php foreach($staffs as $s): ?>
<option value="<?= $s['id'] ?>" <?= $repair['staff_id']==$s['id']?'selected':'' ?>>
<?= $s['username'] ?>
</option>
<?php endforeach; ?>
</select>
</div>

<!-- Date -->
<div class="col-md-4">
<label>Date</label>
<input type="date" name="repair_date" value="<?= $repair['repair_date'] ?>" class="form-control" required>
</div>

<!-- Details -->
<div class="col-md-6">
<label>Details</label>
<input type="text" name="details" value="<?= $repair['details'] ?>" class="form-control">
</div>

<!-- Description -->
<div class="col-md-6">
<label>Description</label>
<textarea name="description" class="form-control"><?= $repair['description'] ?></textarea>
</div>

<!-- Amount -->
<div class="col-md-4">
<label>Amount</label>
<input type="number" step="0.01" name="amount" value="<?= $repair['amount'] ?>" class="form-control">
</div>

<!-- GST -->
<div class="col-md-4">
<label>GST</label>
<input type="number" step="0.01" name="gst" value="<?= $repair['gst'] ?>" class="form-control">
</div>

<!-- PST -->
<div class="col-md-4">
<label>PST</label>
<input type="number" step="0.01" name="pst" value="<?= $repair['pst'] ?>" class="form-control">
</div>

<!-- Bill -->
<div class="col-md-6">
<label>Bill No</label>
<input type="text" name="bill_no" value="<?= $repair['bill_no'] ?>" class="form-control">
</div>

<!-- Remarks -->
<div class="col-md-6">
<label>Remarks</label>
<input type="text" name="remarks" value="<?= $repair['remarks'] ?>" class="form-control">
</div>

<div class="col-12 mt-3 text-center">
<button type="submit" name="update" class="btn btn-primary px-5">Update</button>
<a href="repair_history.php" class="btn btn-secondary px-5">Cancel</a>
</div>

</div>
</form>

</div>
</div>

<!-- 🔥 AUTO FILL SCRIPT -->
<script>
function setVehicleData(){
    let select = document.getElementById("vehicleSelect");
    let selected = select.options[select.selectedIndex];

    let vehicleName = selected.getAttribute("data-name");
    let staffId = selected.getAttribute("data-staff");

    document.getElementById("vehicleName").value = vehicleName || '';

    if(staffId){
        document.getElementById("staffSelect").value = staffId;
    }
}

// Change event
document.getElementById("vehicleSelect").addEventListener("change", setVehicleData);

// Page load pe bhi set ho
window.onload = setVehicleData;
</script>

</body>
</html>