<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

include '../db.php';
include 'includes/notification_helper.php';

$message = "";

if(isset($_POST['submit'])){

    $full_name          = trim($_POST['full_name']);
    $designation        = trim($_POST['designation']);
    $department         = trim($_POST['department']);
    $team_members       = (int)$_POST['team_members'];
    $phone_number       = trim($_POST['phone_number']);
    $email              = trim($_POST['email']);
    $cnic_number        = trim($_POST['cnic_number']);
    $address            = trim($_POST['address']);
    $facility           = trim($_POST['facility']);
    $departure_datetime = $_POST['departure_datetime'];
    $arrival_datetime   = $_POST['arrival_datetime'];
    $place_from         = trim($_POST['place_from']);
    $place_to           = trim($_POST['place_to']);
    $visiting_place     = trim($_POST['visiting_place']);
    $purpose            = trim($_POST['purpose']);
    $booking_type       = $_POST['booking_type'];
    $priority_level     = $_POST['priority_level'];
    $bus_seats          = (int)$_POST['bus_seats'];

    try{

        $stmt = $conn->prepare("
            INSERT INTO bookings
            (
                full_name,
                designation,
                department,
                team_members,
                phone_number,
                email,
                cnic_number,
                address,
                facility,
                departure_datetime,
                arrival_datetime,
                place_from,
                place_to,
                visiting_place,
                purpose,
                booking_type,
                priority_level,
                bus_seats,
                booked_by_admin,
                status
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?,
                ?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->execute([
            $full_name,
            $designation,
            $department,
            $team_members,
            $phone_number,
            $email,
            $cnic_number,
            $address,
            $facility,
            $departure_datetime,
            $arrival_datetime,
            $place_from,
            $place_to,
            $visiting_place,
            $purpose,
            $booking_type,
            $priority_level,
            $bus_seats,
            1,
            'pending'
        ]);

        $booking_id = $conn->lastInsertId();

        createNotification(
            $conn,
            $_SESSION['user_id'],
            'Booking Added',
            'Booking request created successfully',
            'success',
            'booking',
            $booking_id
        );

        $message = "
        <div class='alert alert-success'>
            Booking added successfully.
        </div>";

    }catch(PDOException $e){

        $message = "
        <div class='alert alert-danger'>
            ".$e->getMessage()."
        </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Add Booking</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

<style>

.page-card{
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 0 15px rgba(0,0,0,.08);
}

.page-title{
    font-weight:700;
}

</style>

</head>
<body>

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<div class="container-fluid">

    <div class="page-card">

        <h2 class="page-title mb-4">
            ➕ Add Booking
        </h2>

        <?= $message ?>

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text"
                           name="full_name"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text"
                           name="designation"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text"
                           name="department"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Team Members</label>
                    <input type="number"
                           min="1"
                           name="team_members"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text"
                           name="phone_number"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">CNIC Number</label>
                    <input type="text"
                           name="cnic_number"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Facility</label>
                    <input type="text"
                           name="facility"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address"
                          rows="3"
                          class="form-control"
                          required></textarea>
            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Departure Date & Time
                    </label>

                    <input type="datetime-local"
                           name="departure_datetime"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Arrival Date & Time
                    </label>

                    <input type="datetime-local"
                           name="arrival_datetime"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">From</label>
                    <input type="text"
                           name="place_from"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">To</label>
                    <input type="text"
                           name="place_to"
                           class="form-control"
                           required>
                </div>

            </div>

            <div class="mb-3">
                <label class="form-label">
                    Visiting Place
                </label>

                <input type="text"
                       name="visiting_place"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">

                <label class="form-label">
                    Purpose
                </label>

                <textarea name="purpose"
                          rows="4"
                          class="form-control"
                          required></textarea>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Booking Type
                    </label>

                    <select name="booking_type"
                            class="form-select"
                            required>

                        <option value="">Select</option>
                        <option value="official">Official</option>
                        <option value="private">Private</option>
                        <option value="project">Project</option>
                        <option value="student_tour">Student Tour</option>
                        <option value="other">Other</option>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Priority Level
                    </label>

                    <select name="priority_level"
                            class="form-select">

                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="emergency">Emergency</option>

                    </select>

                </div>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Required Seats
                </label>

                <input type="number"
                       min="1"
                       name="bus_seats"
                       class="form-control"
                       required>

            </div>

            <button type="submit"
                    name="submit"
                    class="btn btn-success w-100">

                Save Booking

            </button>

        </form>

    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>