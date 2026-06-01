<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../Admin/includes/duty_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'staff') {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['username'] ?? 'Staff';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_duty'])) {
        $r = startStaffDuty($conn, $user_id);
        $_SESSION['flash'] = $r['message'];
    }
    if (isset($_POST['end_duty'])) {
        $r = endStaffDuty($conn, $user_id);
        $_SESSION['flash'] = $r['message'];
    }
    header('Location: dashboard.php');
    exit();
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$statusStmt = $conn->prepare('SELECT availability_status FROM users WHERE id=?');
$statusStmt->execute([$user_id]);
$currentStatus = $statusStmt->fetchColumn() ?: 'available';

$assignments = $conn->prepare("
    SELECT va.*, v.vehicle_number, v.vehicle_name, v.current_status
    FROM vehicle_assignments va
    JOIN vehicles v ON v.vehicle_id = va.vehicle_id
    WHERE va.staff_id = ?
      AND va.duty_status IN ('assigned','on_duty','completed')
    ORDER BY va.assignment_id DESC
    LIMIT 10
");
$assignments->execute([$user_id]);
$assignmentList = $assignments->fetchAll(PDO::FETCH_ASSOC);

$duties = $conn->prepare("
    SELECT d.*, v.vehicle_number, v.vehicle_name
    FROM duties d
    LEFT JOIN vehicles v ON d.vehicle_id = v.vehicle_id
    WHERE d.user_id = ?
    ORDER BY d.duty_date DESC, d.id DESC
    LIMIT 20
");
$duties->execute([$user_id]);
$dutyList = $duties->fetchAll(PDO::FETCH_ASSOC);

$pl = $conn->prepare("SELECT COUNT(*) FROM staff_leaves WHERE staff_id=? AND status='pending'");
$pl->execute([$user_id]);
$pendingLeaves = (int)$pl->fetchColumn();

$lv = $conn->prepare("SELECT COUNT(*) FROM staff_leaves WHERE staff_id=? AND status='approved' AND CURDATE() BETWEEN start_date AND end_date");
$lv->execute([$user_id]);
$onLeave = (int)$lv->fetchColumn();

$ns = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
$ns->execute([$user_id]);
$unread = (int)$ns->fetchColumn();

$activeAssign = getActiveAssignment($conn, $user_id);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
body { background:#f4f6f9; }
.stat-card { border:none; border-radius:12px; }
.duty-card { border-left:4px solid #0d6efd; }
</style>
</head>
<body class="light">

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">
<div class="container-fluid">
    <?php if ($flash): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?= htmlspecialchars($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Welcome, <?= htmlspecialchars($user_name) ?></h3>
            <p class="text-muted mb-0">Staff dashboard</p>
        </div>
        <span class="badge fs-6 bg-<?=
            $currentStatus === 'on_duty' ? 'success' :
            ($currentStatus === 'leave' ? 'warning text-dark' : 'secondary')
        ?>"><?= htmlspecialchars(str_replace('_', ' ', $currentStatus)) ?></span>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Duty control</h5>
                <p class="text-muted small mb-0">
                    Assigned ≠ on duty. Press <strong>Start Duty</strong> when you begin work; GPS sends automatically.
                </p>
                <?php if ($activeAssign): ?>
                <small>Vehicle: <b><?= htmlspecialchars($activeAssign['vehicle_number']) ?></b>
                    — <?= htmlspecialchars($activeAssign['duty_status']) ?></small>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($onLeave): ?>
                    <span class="badge bg-warning text-dark">On approved leave</span>
                <?php elseif ($currentStatus === 'on_duty'): ?>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="end_duty" class="btn btn-warning">End duty</button>
                    </form>
                <?php else: ?>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="start_duty" class="btn btn-success" <?= !$activeAssign && empty(array_filter($dutyList, fn($d) => $d['status']==='Active')) ? 'disabled' : '' ?>>
                            Start duty
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card stat-card bg-primary text-white p-3"><h2 class="mb-0"><?= count($dutyList) ?></h2><small>Scheduled duties</small></div></div>
        <div class="col-md-3"><div class="card stat-card bg-success text-white p-3"><h2 class="mb-0"><?= count(array_filter($assignmentList, fn($a)=>in_array($a['duty_status'],['assigned','on_duty']))) ?></h2><small>Active assignments</small></div></div>
        <div class="col-md-3"><div class="card stat-card bg-warning text-dark p-3"><h2 class="mb-0"><?= $pendingLeaves ?></h2><small>Pending leaves</small></div></div>
        <div class="col-md-3"><div class="card stat-card bg-danger text-white p-3"><h2 class="mb-0"><?= $unread ?></h2><small>Notifications</small></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 p-3">
                <h5>My scheduled duties</h5>
                <?php if (empty($dutyList)): ?>
                    <p class="text-muted">No duties scheduled.</p>
                <?php else: foreach ($dutyList as $d): ?>
                    <div class="duty-card bg-light rounded p-3 mb-2">
                        <b><?= htmlspecialchars($d['route_name'] ?? 'Duty') ?></b>
                        <span class="badge bg-<?= ($d['status']??'')==='Active'?'success':'secondary' ?> float-end"><?= htmlspecialchars($d['status']??'') ?></span>
                        <div class="small text-muted mt-1">
                            <?= htmlspecialchars($d['duty_date']??'') ?>
                            <?= htmlspecialchars(($d['start_time']??'').' - '.($d['end_time']??'')) ?><br>
                            <?= htmlspecialchars($d['vehicle_number']??'No vehicle') ?> · <?= htmlspecialchars($d['location']??'') ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 p-3 mb-3">
                <h5>My vehicle assignments</h5>
                <?php if (empty($assignmentList)): ?>
                    <p class="text-muted">No assignments yet.</p>
                <?php else: foreach ($assignmentList as $a): ?>
                    <div class="border rounded p-2 mb-2">
                        <b><?= htmlspecialchars($a['vehicle_number']) ?></b> — <?= htmlspecialchars($a['vehicle_name']) ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($a['duty_status']) ?></span>
                        <?php if (!empty($a['remarks'])): ?><br><small><?= htmlspecialchars($a['remarks']) ?></small><?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
                <a href="my_vehicle.php" class="btn btn-sm btn-outline-primary">View vehicles</a>
            </div>

            <div class="card shadow-sm border-0 p-3">
                <h5 class="text-danger">Emergency</h5>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#emergencyModal">Send emergency alert</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emergencyModal">
<div class="modal-dialog">
<div class="modal-content">
<form action="send_emergency.php" method="POST" id="emergencyForm">
<div class="modal-header bg-danger text-white">
    <h5 class="modal-title">Emergency alert</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label class="form-label">Vehicle</label>
        <select name="vehicle_id" class="form-select" required>
            <option value="">Select vehicle</option>
            <?php
            $ev = $conn->prepare("
                SELECT v.vehicle_id, v.vehicle_number FROM vehicle_assignments va
                JOIN vehicles v ON v.vehicle_id = va.vehicle_id
                WHERE va.staff_id = ? AND va.duty_status IN ('assigned','on_duty')
            ");
            $ev->execute([$user_id]);
            while ($row = $ev->fetch(PDO::FETCH_ASSOC)):
            ?>
            <option value="<?= (int)$row['vehicle_id'] ?>"><?= htmlspecialchars($row['vehicle_number']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="3" required></textarea>
    </div>
    <input type="hidden" name="latitude" id="emLat">
    <input type="hidden" name="longitude" id="emLng">
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="submit" name="submit_emergency" class="btn btn-danger">Send</button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('emergencyForm')?.addEventListener('submit', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(p) {
            document.getElementById('emLat').value = p.coords.latitude;
            document.getElementById('emLng').value = p.coords.longitude;
        });
    }
});
</script>
</body>
</html>
