<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../index.php");
    exit();
}

if(isset($_POST['submit_leave'])){

    $staff_id = $_SESSION['user_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("
        INSERT INTO staff_leaves
        (
            staff_id,
            leave_type,
            start_date,
            end_date,
            reason,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, 'pending')
    ");

    if($stmt->execute([
        $staff_id,
        $leave_type,
        $start_date,
        $end_date,
        $reason
    ])){
        $success = true;
    }
}


// Leave Insert Success

$leave_id = $conn->lastInsertId();

/* GET ADMIN */
$admin = $conn->query("
SELECT id
FROM users
WHERE role='admin'
LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$admin_id = $admin['id'] ?? 1;

/* SEND NOTIFICATION TO ADMIN */

$notify = $conn->prepare("
INSERT INTO notifications
(
user_id,
title,
message,
type,
module,
reference_id,
is_read
)
VALUES
(
?,
?,
?,
?,
?,
?,
0
)
");

$notify->execute([
$admin_id,
'New Leave Request',
'Staff member submitted a leave request.',
'info',
'leave',
$leave_id
]);
?>

<!DOCTYPE html>
<html>
<head>
<title>Apply Leave</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">

<div class="container">

<div class="card shadow border-0">
<div class="card-header bg-primary text-white">
<h4>📝 Apply Leave</h4>
</div>

<div class="card-body">

<?php if(isset($success)): ?>
<div class="alert alert-success">
Leave request submitted successfully.
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Leave Type</label>

<select name="leave_type" class="form-select" required>
<option value="">Select</option>
<option value="casual">Casual Leave</option>
<option value="medical">Medical Leave</option>
<option value="emergency">Emergency Leave</option>
<option value="annual">Annual Leave</option>
</select>
</div>

<div class="row">

<div class="col-md-6">
<label class="form-label">Start Date</label>
<input type="date"
       name="start_date"
       class="form-control"
       required>
</div>

<div class="col-md-6">
<label class="form-label">End Date</label>
<input type="date"
       name="end_date"
       class="form-control"
       required>
</div>

</div>

<div class="mt-3">
<label class="form-label">Reason</label>

<textarea name="reason"
          rows="5"
          class="form-control"
          required></textarea>
</div>

<button class="btn btn-primary mt-3"
        type="submit"
        name="submit_leave">
    Submit Leave Request
</button>

</form>

</div>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>