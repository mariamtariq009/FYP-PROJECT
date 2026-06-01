<?php
session_start();
require '../db.php';
include 'includes/notification_helper.php';
include 'includes/update_vehicle_status.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$message = '';

/* FETCH VEHICLES */
$vehicles = $conn->query("
    SELECT vehicle_id, vehicle_name, vehicle_number, fuel_type 
    FROM vehicles
")->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['add'])){

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

    try{

        $stmt = $conn->prepare("
            INSERT INTO pol_records
            (
                vehicle_id,
                fuel_date,
                details,
                liters,
                rate,
                fuel_amount,
                filter_change_type,
                filter_change_amount,
                gst,
                pst,
                total_amount
            )
            VALUES
            (
                :vehicle_id,
                :fuel_date,
                :details,
                :liters,
                :rate,
                :fuel_amount,
                :filter_change_type,
                :filter_change_amount,
                :gst,
                :pst,
                :total_amount
            )
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
            ':total_amount' => $total_amount
        ]);

        updateVehicleStatus($conn, $_POST['vehicle_id']);
        
        createNotification(
            $conn,
            $_SESSION['user_id'],
            "Fuel Record Added",
            "New Fuel record added successfully.",
            "info",
            "pol_records",
            $conn->lastInsertId()
        );

        $message = "<div class='alert alert-success'>POL record added successfully!</div>";

    }catch(PDOException $e){
        $message = "<div class='alert alert-danger'>".$e->getMessage()."</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add POL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/layout.php'; ?>

<div class="content p-4 mt-5">
    <div class="card shadow mt-5">
        <div class="card-body">

            <h3 class="mb-4">Add POL Record</h3>

            <?= $message ?>

            <form method="POST">
                <div class="row g-3">

                    <!-- Vehicle -->
                    <div class="col-md-4">
                        <label>Vehicle</label>
                        <select name="vehicle_id" id="vehicle" class="form-control" required>
                            <option value="">Select Vehicle</option>

                            <?php foreach($vehicles as $v): ?>
                                <option 
                                    value="<?= $v['vehicle_id'] ?>"
                                    data-fuel="<?= $v['fuel_type'] ?>"
                                >
                                    <?= $v['vehicle_name'] ?> 
                                    (<?= $v['vehicle_number'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fuel Type Auto -->
                    <div class="col-md-4">
                        <label>Fuel Type</label>
                        <input type="text" id="fuel_type" class="form-control" readonly>
                    </div>

                    <!-- Date -->
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" name="fuel_date" class="form-control" required>
                    </div>

                    <!-- Details -->
                    <div class="col-md-6">
                        <label>Details</label>
                        <textarea name="details" class="form-control"></textarea>
                    </div>

                    <!-- Liters -->
                    <div class="col-md-3">
                        <label>Liters</label>
                        <input type="number" step="0.01" id="liters" name="liters" class="form-control" required>
                    </div>

                    <!-- Rate -->
                    <div class="col-md-3">
                        <label>Rate</label>
                        <input type="number" step="0.01" id="rate" name="rate" class="form-control" required>
                    </div>

                    <!-- Fuel Amount -->
                    <div class="col-md-3">
                        <label>Fuel Amount</label>
                        <input type="number" id="fuel_amount" class="form-control" readonly>
                    </div>

                    <!-- Filter Change -->
                    <div class="col-md-3">
                        <label>Filter Change</label>
                        <select name="filter_change" id="filter_change" class="form-control">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>

                    <!-- Filter Type -->
                    <div class="col-md-3 filter-box d-none">
                        <label>Filter Type</label>
                        <select name="filter_change_type" class="form-control">
                            <option value="">Select Filter Type</option>
                            <option value="Oil Filter">Oil Filter</option>
                            <option value="Air Filter">Air Filter</option>
                            <option value="Diesel Filter">Diesel Filter</option>
                        </select>
                    </div>

                    <!-- Filter Amount -->
                    <div class="col-md-3 filter-box d-none">
                        <label>Filter Amount</label>
                        <input type="number" step="0.01" id="filter_amount" name="filter_change_amount" class="form-control">
                    </div>

                    <!-- GST -->
                    <div class="col-md-3">
                        <label>GST</label>
                        <input type="number" id="gst" name="gst" class="form-control">
                    </div>

                    <!-- PST -->
                    <div class="col-md-3">
                        <label>PST</label>
                        <input type="number" id="pst" name="pst" class="form-control">
                    </div>

                    <!-- Total -->
                    <div class="col-md-3">
                        <label>Total Amount</label>
                        <input type="number" id="total" class="form-control" readonly>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" name="add" class="btn btn-success px-5">
                            Add Record
                        </button>

                        <a href="pol_list.php" class="btn btn-secondary px-5">
                            Back
                        </a>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.getElementById('vehicle').addEventListener('change', function () {
    let selected = this.options[this.selectedIndex];
    let fuelType = selected.getAttribute('data-fuel') || '';
    document.getElementById('fuel_type').value = fuelType;
});

document.getElementById('filter_change').addEventListener('change', function () {
    let boxes = document.querySelectorAll('.filter-box');

    if(this.value === 'Yes'){
        boxes.forEach(box => box.classList.remove('d-none'));
    }else{
        boxes.forEach(box => box.classList.add('d-none'));
    }

    calc();
});

function calc(){

    let liters = parseFloat(document.getElementById('liters').value) || 0;
    let rate = parseFloat(document.getElementById('rate').value) || 0;
    let filter = parseFloat(document.getElementById('filter_amount')?.value) || 0;
    let gst = parseFloat(document.getElementById('gst').value) || 0;
    let pst = parseFloat(document.getElementById('pst').value) || 0;

    let fuel = liters * rate;
    let total = fuel + filter;

    document.getElementById('fuel_amount').value = fuel.toFixed(2);
    document.getElementById('total').value = total.toFixed(2);
}

document.querySelectorAll('#liters,#rate,#filter_amount,#gst,#pst')
.forEach(el=>{
    el.addEventListener('input', calc);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>