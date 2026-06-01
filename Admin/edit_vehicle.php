<?php
session_start();
require '../db.php';
require 'includes/notification_helper.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

/* VEHICLE */
$stmt = $conn->prepare("
SELECT * FROM vehicles WHERE vehicle_id = ?
");
$stmt->execute([$id]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$v){
    die("Vehicle not found");
}

/* CATEGORIES */
$catStmt = $conn->query("
SELECT category_id, category_name
FROM vehicle_categories
");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/* UPDATE */
if(isset($_POST['update'])){

    try{

        $company_name = trim($_POST['company_name']);

        /* check or insert company */
        $c = $conn->prepare("
            SELECT company_id FROM vehicle_companies
            WHERE company_name = ?
        ");
        $c->execute([$company_name]);
        $company = $c->fetch(PDO::FETCH_ASSOC);

        if($company){
            $company_id = $company['company_id'];
        }else{
            $ins = $conn->prepare("
                INSERT INTO vehicle_companies(company_name)
                VALUES(?)
            ");
            $ins->execute([$company_name]);
            $company_id = $conn->lastInsertId();
        }

        /* UPDATE VEHICLE */
        $update = $conn->prepare("
            UPDATE vehicles SET

            company_id = :company_id,
            category_id = :category_id,
            vehicle_name = :vehicle_name,
            make_model = :make_model,
            vehicle_number = :vehicle_number,
            model_year = :model_year,
            engine_capacity_cc = :engine_capacity_cc,
            seating_capacity = :seating_capacity,
            fuel_type = :fuel_type,
            chassis_number = :chassis_number,
            gps_device_number = :gps_device_number,
            deployment_plan = :deployment_plan,
            insurance_expiry = :insurance_expiry,
            token_expiry = :token_expiry

            WHERE vehicle_id = :id
        ");

        $update->execute([

            ':company_id' => $company_id,
            ':category_id' => $_POST['category_id'],
            ':vehicle_name' => $_POST['vehicle_name'],
            ':make_model' => $_POST['make_model'],
            ':vehicle_number' => $_POST['vehicle_number'],
            ':model_year' => $_POST['model_year'],
            ':engine_capacity_cc' => $_POST['engine_capacity_cc'],
            ':seating_capacity' => $_POST['seating_capacity'],
            ':fuel_type' => $_POST['fuel_type'],
            ':chassis_number' => $_POST['chassis_number'],
            ':gps_device_number' => $_POST['gps_device_number'],
            ':deployment_plan' => $_POST['deployment_plan'],
            ':insurance_expiry' => $_POST['insurance_expiry'],
            ':token_expiry' => $_POST['token_expiry'],
            ':id' => $id
        ]);

        createNotification(
            $conn,
            $_SESSION['user_id'],
            "Vehicle Updated",
            "Vehicle updated successfully.",
            "info",
            "vehicles",
            $id
        );

        header("Location: vehicle_list.php");
        exit();

    }catch(Exception $e){
        echo "Error: ".$e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Vehicle</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="content p-4">

<div class="container">

<div class="card shadow p-4">

<h3>Edit Vehicle</h3>

<form method="POST">

<!-- COMPANY -->
<label>Company Name</label>
<input
type="text"
name="company_name"
class="form-control mb-2"
value="<?= $v['company_id'] ?>"
placeholder="Toyota / Hino / Suzuki">

<!-- CATEGORY -->
<label>Category</label>
<select name="category_id" class="form-control mb-2">

<?php foreach($categories as $c): ?>

<option value="<?= $c['category_id'] ?>"
<?= $v['category_id']==$c['category_id']?'selected':'' ?>>

<?= $c['category_name'] ?>

</option>

<?php endforeach; ?>

</select>

<!-- VEHICLE NAME -->
<input class="form-control mb-2" name="vehicle_name"
value="<?= $v['vehicle_name'] ?>">

<!-- MODEL -->
<input class="form-control mb-2" name="make_model"
value="<?= $v['make_model'] ?>">

<!-- NUMBER -->
<input class="form-control mb-2" name="vehicle_number"
value="<?= $v['vehicle_number'] ?>">

<!-- YEAR -->
<input class="form-control mb-2" name="model_year"
value="<?= $v['model_year'] ?>">

<!-- ENGINE -->
<input class="form-control mb-2" name="engine_capacity_cc"
value="<?= $v['engine_capacity_cc'] ?>">

<!-- SEATS -->
<input class="form-control mb-2" name="seating_capacity"
value="<?= $v['seating_capacity'] ?>">

<!-- FUEL -->
<select name="fuel_type" class="form-control mb-2">
<option value="Petrol" <?= $v['fuel_type']=='Petrol'?'selected':'' ?>>Petrol</option>
<option value="Diesel" <?= $v['fuel_type']=='Diesel'?'selected':'' ?>>Diesel</option>
<option value="Hybrid" <?= $v['fuel_type']=='Hybrid'?'selected':'' ?>>Hybrid</option>
<option value="Electric" <?= $v['fuel_type']=='Electric'?'selected':'' ?>>Electric</option>
</select>

<!-- GPS -->
<input class="form-control mb-2" name="gps_device_number"
value="<?= $v['gps_device_number'] ?>">

<!-- DEPLOYMENT -->
<textarea name="deployment_plan" class="form-control mb-2">
<?= $v['deployment_plan'] ?>
</textarea>

<!-- DATES -->
<input type="date" name="insurance_expiry"
class="form-control mb-2"
value="<?= $v['insurance_expiry'] ?>">

<input type="date" name="token_expiry"
class="form-control mb-2"
value="<?= $v['token_expiry'] ?>">

<!-- BUTTON -->
<button class="btn btn-primary" name="update">
Update Vehicle
</button>

<a href="vehicle_list.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>

</div>

</body>
</html>