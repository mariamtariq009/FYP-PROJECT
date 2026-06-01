<?php
session_start();
require '../db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$message = "";

/* Vehicle Categories */
$categoryStmt = $conn->query("
    SELECT category_id, category_name
    FROM vehicle_categories
    ORDER BY category_name ASC
");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

/* Add Vehicle */
if(isset($_POST['add_vehicle'])){

    try{

        $company_name      = trim($_POST['company_name']);
        $category_id       = $_POST['category_id'];
        $vehicle_name      = trim($_POST['vehicle_name']);
        $make_model        = trim($_POST['make_model']);
        $vehicle_number    = trim($_POST['vehicle_number']);
        $model_year        = $_POST['model_year'];
        $engine_capacity   = $_POST['engine_capacity_cc'];
        $seating_capacity  = $_POST['seating_capacity'];
        $fuel_type         = $_POST['fuel_type'];
        $chassis_number    = trim($_POST['chassis_number']);
        $gps_device_number = trim($_POST['gps_device_number']);
        $deployment_plan   = trim($_POST['deployment_plan']);
        $insurance_expiry  = !empty($_POST['insurance_expiry']) ? $_POST['insurance_expiry'] : null;
        $token_expiry      = !empty($_POST['token_expiry']) ? $_POST['token_expiry'] : null;
        $status            = $_POST['current_status'];

        /* Check Vehicle Number */
        $checkVehicle = $conn->prepare("
            SELECT vehicle_id
            FROM vehicles
            WHERE vehicle_number = ?
        ");
        $checkVehicle->execute([$vehicle_number]);

        if($checkVehicle->rowCount() > 0){
            throw new Exception("Vehicle number already exists.");
        }

        /* Company Exists ? */
        $companyStmt = $conn->prepare("
            SELECT company_id
            FROM vehicle_companies
            WHERE company_name = ?
            LIMIT 1
        ");
        $companyStmt->execute([$company_name]);

        $company = $companyStmt->fetch(PDO::FETCH_ASSOC);

        if($company){

            $company_id = $company['company_id'];

        }else{

            $insertCompany = $conn->prepare("
                INSERT INTO vehicle_companies(company_name)
                VALUES(?)
            ");
            $insertCompany->execute([$company_name]);

            $company_id = $conn->lastInsertId();
        }

        /* Insert Vehicle */
        $stmt = $conn->prepare("
            INSERT INTO vehicles
            (
                company_id,
                category_id,
                vehicle_name,
                make_model,
                vehicle_number,
                model_year,
                engine_capacity_cc,
                seating_capacity,
                fuel_type,
                chassis_number,
                gps_device_number,
                deployment_plan,
                insurance_expiry,
                token_expiry,
                current_status
            )
            VALUES
            (
                :company_id,
                :category_id,
                :vehicle_name,
                :make_model,
                :vehicle_number,
                :model_year,
                :engine_capacity_cc,
                :seating_capacity,
                :fuel_type,
                :chassis_number,
                :gps_device_number,
                :deployment_plan,
                :insurance_expiry,
                :token_expiry,
                :current_status
            )
        ");

        $stmt->execute([

            ':company_id'          => $company_id,
            ':category_id'         => $category_id,
            ':vehicle_name'        => $vehicle_name,
            ':make_model'          => $make_model,
            ':vehicle_number'      => $vehicle_number,
            ':model_year'          => $model_year,
            ':engine_capacity_cc'  => $engine_capacity,
            ':seating_capacity'    => $seating_capacity,
            ':fuel_type'           => $fuel_type,
            ':chassis_number'      => $chassis_number,
            ':gps_device_number'   => $gps_device_number,
            ':deployment_plan'     => $deployment_plan,
            ':insurance_expiry'    => $insurance_expiry,
            ':token_expiry'        => $token_expiry,
            ':current_status'      => $status

        ]);

        $message = "
        <div class='alert alert-success'>
            Vehicle added successfully.
        </div>";

    }catch(Exception $e){

        $message = "
        <div class='alert alert-danger'>
            ".$e->getMessage()."
        </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Add Vehicle</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<h3 class="mb-3">Add Vehicle</h3>

<?= $message ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-2">
<label>Company Name</label>
<input
type="text"
name="company_name"
class="form-control"
placeholder="Toyota, Hino, Isuzu..."
required>
</div>

<div class="col-md-6 mb-2">
<label>Vehicle Category</label>
<select
name="category_id"
class="form-control"
required>

<option value="">Select Category</option>

<?php foreach($categories as $cat): ?>

<option value="<?= $cat['category_id'] ?>">
<?= htmlspecialchars($cat['category_name']) ?>
</option>

<?php endforeach; ?>

</select>
</div>

<div class="col-md-6 mb-2">
<label>Vehicle Name</label>
<input
type="text"
name="vehicle_name"
class="form-control"
placeholder="Toyota Corolla"
required>
</div>

<div class="col-md-6 mb-2">
<label>Make / Model</label>
<input
type="text"
name="make_model"
class="form-control"
placeholder="GLI, APV, Coaster..."
required>
</div>

<div class="col-md-6 mb-2">
<label>Vehicle Number</label>
<input
type="text"
name="vehicle_number"
class="form-control"
required>
</div>

<div class="col-md-6 mb-2">
<label>Model Year</label>
<input
type="number"
name="model_year"
class="form-control"
min="1990"
max="2100"
required>
</div>

<div class="col-md-6 mb-2">
<label>Engine Capacity (CC)</label>
<input
type="number"
name="engine_capacity_cc"
class="form-control"
required>
</div>

<div class="col-md-6 mb-2">
<label>Seating Capacity</label>
<input
type="number"
name="seating_capacity"
class="form-control"
required>
</div>

<div class="col-md-6 mb-2">
<label>Fuel Type</label>
<select
name="fuel_type"
class="form-control"
required>

<option value="Petrol">Petrol</option>
<option value="Diesel">Diesel</option>
<option value="Hybrid">Hybrid</option>
<option value="Electric">Electric</option>

</select>
</div>

<div class="col-md-6 mb-2">
<label>Chassis Number</label>
<input
type="text"
name="chassis_number"
class="form-control">
</div>

<div class="col-md-6 mb-2">
<label>GPS Device Number</label>
<input
type="text"
name="gps_device_number"
class="form-control">
</div>

<div class="col-md-6 mb-2">
<label>Insurance Expiry</label>
<input
type="date"
name="insurance_expiry"
class="form-control">
</div>

<div class="col-md-6 mb-2">
<label>Token Expiry</label>
<input
type="date"
name="token_expiry"
class="form-control">
</div>

<div class="col-md-12 mb-2">
<label>Deployment / Utilization Plan</label>
<textarea
name="deployment_plan"
class="form-control"
rows="3"></textarea>
</div>

<div class="col-md-6 mb-3">
<label>Status</label>
<select
name="current_status"
class="form-control">

<option value="available">Available</option>
<option value="maintenance">Maintenance</option>
<option value="inactive">Inactive</option>
<option value="emergency">Emergency</option>

</select>
</div>

</div>

<button
type="submit"
name="add_vehicle"
class="btn btn-primary">

Add Vehicle

</button>

</form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("toggle").onclick = function(){
    document.getElementById("sidebar").classList.toggle("active");
}
</script>

</body>
</html>