<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH BOOKING HISTORY (WITH JOINS)
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT 
        b.*,

        v.vehicle_name,
        v.vehicle_number,

        u.name AS staff_name,
        u.phone AS staff_phone,

        a.name AS admin_name

    FROM bookings b

    LEFT JOIN vehicles v 
        ON b.vehicle_id = v.vehicle_id

    LEFT JOIN users u 
        ON b.staff_id = u.id

    LEFT JOIN users a 
        ON b.approved_by = a.id

    ORDER BY b.id DESC
");

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking History</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f4f6f9;
        }

        .card-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .badge-status {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 13px;
        }

        .route {
            background: #eef2f7;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>

<body>

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

    <h3 class="mb-4">📋 Booking History</h3>

    <div class="card-box">

        <?php if (empty($bookings)) { ?>
            <div class="alert alert-warning text-center">
                No booking history found
            </div>
        <?php } else { ?>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Seats</th>
                        <th>Vehicle</th>
                        <th>Staff</th>
                        <th>Admin</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $i = 1;
                foreach ($bookings as $b) {
                ?>

                <tr>

                    <td><?= $i++ ?></td>

                    <td>
                        <b><?= htmlspecialchars($b['full_name']) ?></b>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['email']) ?><br>
                        <?= htmlspecialchars($b['phone_number']) ?>
                    </td>

                    <td>
                        <span class="route">
                            <?= htmlspecialchars($b['place_from']) ?>
                            →
                            <?= htmlspecialchars($b['place_to']) ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['departure_datetime']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($b['bus_seats']) ?>
                    </td>

                    <td>
                        <?php if (!empty($b['vehicle_name'])) { ?>
                            <span class="badge bg-info text-dark">
                                <?= $b['vehicle_name'] ?><br>
                                <?= $b['vehicle_number'] ?>
                            </span>
                        <?php } else { ?>
                            <span class="badge bg-secondary">Not Assigned</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?php if (!empty($b['staff_name'])) { ?>
                            <?= $b['staff_name'] ?><br>
                            <small><?= $b['staff_phone'] ?></small>
                        <?php } else { ?>
                            <span class="badge bg-secondary">Not Assigned</span>
                        <?php } ?>
                    </td>

                    <td>
                        <?= $b['admin_name'] ?? 'System' ?>
                    </td>

                    <td>
                        <?php
                        $status = $b['status'];

                        if ($status == 'approved') {
                            echo '<span class="badge bg-success">Approved</span>';
                        } elseif ($status == 'rejected') {
                            echo '<span class="badge bg-danger">Rejected</span>';
                        } elseif ($status == 'completed') {
                            echo '<span class="badge bg-primary">Completed</span>';
                        } else {
                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                        }
                        ?>
                    </td>

                    <td>
                        <?= date('d M Y h:i A', strtotime($b['created_at'])) ?>
                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

        <?php } ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</body>
</html>