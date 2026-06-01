<?php
session_start();
require '../db.php';
include 'includes/notification_helper.php';
require_once 'includes/status_sync.php';
require_once 'includes/duty_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if (isset($_POST['assign_vehicle'])) {
    try {
        $vehicle_id = (int)$_POST['vehicle_id'];
        $staff_id = (int)$_POST['staff_id'];
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$vehicle_id || !$staff_id) {
            throw new Exception('Please select vehicle and staff.');
        }

        $check = $conn->prepare("
            SELECT COUNT(*) FROM vehicle_assignments
            WHERE vehicle_id = ? AND duty_status IN ('assigned','on_duty')
        ");
        $check->execute([$vehicle_id]);
        if ((int)$check->fetchColumn() > 0) {
            throw new Exception('This vehicle is already assigned. Unassign it first.');
        }

        $assignment_id = createVehicleAssignment(
            $conn,
            $vehicle_id,
            $staff_id,
            (int)$_SESSION['user_id'],
            $remarks ?: 'Admin assignment'
        );

        $vStmt = $conn->prepare('SELECT vehicle_name, vehicle_number FROM vehicles WHERE vehicle_id=?');
        $vStmt->execute([$vehicle_id]);
        $vehicle = $vStmt->fetch(PDO::FETCH_ASSOC);

        createNotification(
            $conn,
            $staff_id,
            'Vehicle Assigned',
            "Vehicle {$vehicle['vehicle_name']} ({$vehicle['vehicle_number']}) assigned. Start duty when you begin work.",
            'info',
            'assignment',
            $assignment_id
        );

        $message = "<div class='alert alert-success'>Vehicle assigned. Staff remains <strong>available</strong> until they press Start Duty.</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if (isset($_POST['unassign_id'])) {
    $result = unassignVehicle($conn, (int)$_POST['unassign_id'], (int)$_SESSION['user_id']);
    $message = $result['ok']
        ? "<div class='alert alert-success'>" . htmlspecialchars($result['message']) . "</div>"
        : "<div class='alert alert-warning'>" . htmlspecialchars($result['message']) . "</div>";
}

$vehicles = $conn->query("
    SELECT vehicle_id, vehicle_name, vehicle_number, make_model, current_status
    FROM vehicles
    WHERE current_status IN ('available','assigned','inactive')
      AND vehicle_id NOT IN (
          SELECT vehicle_id FROM vehicle_assignments WHERE duty_status IN ('assigned','on_duty')
      )
    ORDER BY vehicle_name
")->fetchAll(PDO::FETCH_ASSOC);

$staffList = $conn->query("
    SELECT id, name, designation, availability_status
    FROM users
    WHERE role = 'staff' AND employment_status = 'active'
      AND availability_status IN ('available','on_duty')
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$activeAssignments = $conn->query("
    SELECT va.assignment_id, va.duty_status, va.assignment_date, va.remarks,
           v.vehicle_number, v.vehicle_name, v.current_status AS vehicle_status,
           u.name AS staff_name, u.availability_status AS staff_status
    FROM vehicle_assignments va
    JOIN vehicles v ON v.vehicle_id = va.vehicle_id
    JOIN users u ON u.id = va.staff_id
    WHERE va.duty_status IN ('assigned','on_duty')
    ORDER BY va.assignment_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

$history = $conn->query("
    SELECT va.assignment_id, va.duty_status, va.start_time, va.end_time, va.remarks,
           v.vehicle_number, u.name AS staff_name
    FROM vehicle_assignments va
    JOIN vehicles v ON v.vehicle_id = va.vehicle_id
    JOIN users u ON u.id = va.staff_id
    WHERE va.duty_status IN ('completed','cancelled')
    ORDER BY va.end_time DESC, va.assignment_id DESC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Assign Vehicle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">
<h3 class="mb-3">Assign Vehicle</h3>
<?= $message ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow p-4 h-100">
            <h5 class="mb-3">New assignment</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Select vehicle</option>
                        <?php foreach ($vehicles as $v): ?>
                        <option value="<?= (int)$v['vehicle_id'] ?>">
                            <?= htmlspecialchars($v['vehicle_name'] . ' — ' . $v['vehicle_number'] . ' [' . $v['current_status'] . ']') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Staff</label>
                    <select name="staff_id" class="form-select" required>
                        <option value="">Select staff</option>
                        <?php foreach ($staffList as $s): ?>
                        <option value="<?= (int)$s['id'] ?>">
                            <?= htmlspecialchars($s['name'] . ' — ' . $s['designation'] . ' [' . $s['availability_status'] . ']') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Trip / duty details"></textarea>
                </div>
                <button type="submit" name="assign_vehicle" class="btn btn-primary w-100">Assign vehicle</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow p-4 mb-3">
            <h5 class="mb-3">Currently assigned</h5>
            <?php if (empty($activeAssignments)): ?>
                <p class="text-muted mb-0">No active assignments.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Vehicle</th>
                            <th>Staff status</th>
                            <th>Duty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($activeAssignments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['staff_name']) ?></td>
                            <td><?= htmlspecialchars($a['vehicle_number'] . ' — ' . $a['vehicle_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($a['staff_status']) ?></span></td>
                            <td><span class="badge bg-<?= $a['duty_status'] === 'on_duty' ? 'success' : 'info' ?>"><?= htmlspecialchars($a['duty_status']) ?></span></td>
                            <td>
                                <?php if ($a['duty_status'] === 'assigned'): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Unassign this vehicle?');">
                                    <input type="hidden" name="unassign_id" value="<?= (int)$a['assignment_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Unassign</button>
                                </form>
                                <?php else: ?>
                                <small class="text-muted">On duty</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="card shadow p-4">
            <h6 class="text-muted">Recent assignment history</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Staff</th><th>Vehicle</th><th>Status</th><th>Ended</th></tr></thead>
                    <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['staff_name']) ?></td>
                            <td><?= htmlspecialchars($h['vehicle_number']) ?></td>
                            <td><?= htmlspecialchars($h['duty_status']) ?></td>
                            <td><?= htmlspecialchars($h['end_time'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
