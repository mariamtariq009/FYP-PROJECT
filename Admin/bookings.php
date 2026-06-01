<?php
session_start();
require_once '../db.php';
require_once '../send_mail.php';
require_once 'includes/notification_helper.php';
require_once 'includes/status_sync.php';
require_once 'includes/duty_helper.php';

$message = "";

/*
|--------------------------------------------------------------------------
| APPROVE BOOKING
|--------------------------------------------------------------------------
*/
if (isset($_POST['approve'])) {

    $booking_id = $_POST['booking_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $staff_id   = $_POST['staff_id'];

    if (empty($vehicle_id) || empty($staff_id)) {
        $message = "<div class='alert alert-danger'>Please select vehicle and staff</div>";
    } else {

        // Get booking
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE id=?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {

            // Update booking
            $update = $conn->prepare("
                UPDATE bookings
                SET status='approved',
                    vehicle_id=?,
                    staff_id=?,
                    approved_by=?
                WHERE id=?
            ");

            $update->execute([
                $vehicle_id,
                $staff_id,
                $_SESSION['user_id'],
                $booking_id
            ]);

            // Get vehicle
            $v = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id=?");
            $v->execute([$vehicle_id]);
            $vehicle = $v->fetch(PDO::FETCH_ASSOC);

            // Get staff
            $s = $conn->prepare("SELECT * FROM users WHERE id=?");
            $s->execute([$staff_id]);
            $staff = $s->fetch(PDO::FETCH_ASSOC);

            // Notification to staff
            createNotification(
                $conn,
                $staff_id,
                "New Duty Assigned",
                "You are assigned for booking: {$booking['place_from']} → {$booking['place_to']}",
                "success",
                "booking",
                $booking_id
            );

            // Email to client
            $subject = "Booking Approved - Transport System";

            $body = "
                <h3>Booking Approved</h3>
                <p><b>Name:</b> {$booking['full_name']}</p>
                <p><b>Route:</b> {$booking['place_from']} → {$booking['place_to']}</p>
                <p><b>Date:</b> {$booking['departure_datetime']}</p>

                <hr>

                <h4>Vehicle Details</h4>
                <p>{$vehicle['vehicle_name']} ({$vehicle['vehicle_number']})</p>

                <h4>Staff Assigned</h4>
                <p>{$staff['name']} - {$staff['phone']}</p>

                <br>
                <p>Thank you</p>
            ";

            sendMail($booking['email'], $subject, $body);

            createVehicleAssignment(
                $conn,
                (int)$vehicle_id,
                (int)$staff_id,
                (int)$_SESSION['user_id'],
                'Booking #' . $booking_id . ': ' . $booking['place_from'] . ' → ' . $booking['place_to']
            );

            $message = "<div class='alert alert-success'>Booking Approved Successfully</div>";
        }
    }
}


/*
|--------------------------------------------------------------------------
| REJECT BOOKING
|--------------------------------------------------------------------------
*/
if (isset($_POST['reject'])) {

    $booking_id = $_POST['booking_id'];

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id=?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {

        $update = $conn->prepare("
            UPDATE bookings
            SET status='rejected',
                approved_by=?
            WHERE id=?
        ");

        $update->execute([
            $_SESSION['user_id'],
            $booking_id
        ]);

        // Email
        $subject = "Booking Rejected";
        $body = "
            <h3>Booking Rejected</h3>
            <p>Dear {$booking['full_name']},</p>
            <p>Your booking request was rejected.</p>
        ";

        sendMail($booking['email'], $subject, $body);

        $message = "<div class='alert alert-danger'>Booking Rejected</div>";
    }
}


/*
|--------------------------------------------------------------------------
| FETCH DATA
|--------------------------------------------------------------------------
*/
$bookings = $conn->query("
    SELECT * FROM bookings
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$vehicles = $conn->query("
    SELECT v.* FROM vehicles v
    WHERE v.current_status IN ('available','assigned')
      AND v.vehicle_id NOT IN (
          SELECT vehicle_id FROM vehicle_assignments WHERE duty_status IN ('assigned','on_duty')
      )
    ORDER BY v.vehicle_name
")->fetchAll(PDO::FETCH_ASSOC);

$staff = $conn->query("
    SELECT u.* FROM users u
    WHERE u.role = 'staff' AND u.employment_status = 'active'
      AND u.availability_status = 'available'
      AND u.id NOT IN (
          SELECT staff_id FROM staff_leaves
          WHERE status = 'approved' AND CURDATE() BETWEEN start_date AND end_date
      )
    ORDER BY u.name
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<h2 class="mb-4">🚌 Booking Requests</h2>

<?= $message ?>

<?php foreach ($bookings as $b) { ?>

<div class="card p-3 mb-3 shadow-sm">

    <h5><?= $b['full_name'] ?></h5>

    <p>
        <b>Route:</b> <?= $b['place_from'] ?> → <?= $b['place_to'] ?><br>
        <b>Email:</b> <?= $b['email'] ?><br>
        <b>Phone:</b> <?= $b['phone_number'] ?><br>
        <b>Date:</b> <?= $b['departure_datetime'] ?><br>
        <b>Seats:</b> <?= $b['bus_seats'] ?><br>
        <b>Status:</b> <?= $b['status'] ?>
    </p>

    <?php if (!empty($b['vehicle_id']) && !empty($b['staff_id']) && $b['status'] === 'approved'): ?>
        <p class="text-success"><b>Assigned:</b> Vehicle #<?= (int)$b['vehicle_id'] ?> · Staff #<?= (int)$b['staff_id'] ?></p>
    <?php endif; ?>

    <?php if ($b['status'] == 'pending') { ?>

    <form method="POST">

        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">

        <div class="row">

            <div class="col-md-6">
                <label>Assign Vehicle</label>
                <select name="vehicle_id" class="form-control">
                    <option value="">Select Vehicle</option>
                    <?php foreach ($vehicles as $v) { ?>
                        <option value="<?= $v['vehicle_id'] ?>">
                            <?= $v['vehicle_name'] ?> (<?= $v['vehicle_number'] ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-6">
                <label>Assign Staff</label>
                <select name="staff_id" class="form-control">
                    <option value="">Select Staff</option>
                    <?php foreach ($staff as $s) { ?>
                        <option value="<?= $s['id'] ?>">
                            <?= $s['name'] ?> (<?= $s['phone'] ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

        </div>

        <div class="mt-3">
            <button name="approve" class="btn btn-success">Approve</button>
            <button name="reject" class="btn btn-danger">Reject</button>
        </div>

    </form>

    <?php } ?>

</div>

<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</body>
</html>