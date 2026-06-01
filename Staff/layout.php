<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../db.php';
require_once __DIR__ . '/../Admin/includes/status_sync.php';

$username = $_SESSION['username'] ?? 'Staff';

if (isset($conn) && ($_SESSION['role'] ?? '') === 'staff') {
    syncAllStatuses($conn);
}
$user_id  = $_SESSION['user_id'] ?? 0;

$staffOnDuty = false;
if ($user_id && isset($conn)) {
    $od = $conn->prepare("SELECT availability_status FROM users WHERE id = ?");
    $od->execute([$user_id]);
    $staffOnDuty = ($od->fetchColumn() === 'on_duty');
}

$current = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------
| FIXED NOTIFICATION QUERY (BASED ON YOUR DB)
|--------------------------------------
*/
$unread = 0;

if ($user_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$user_id]);
    $unread = $stmt->fetchColumn();
}

function active($current, $page){
    return $current === $page ? 'active' : '';
}
?>

<!-- NAVBAR -->
<nav class="navbar fixed-top px-3 shadow-sm bg-white">

    <button id="sidebarToggle" class="btn">☰</button>

    <a class="navbar-brand fw-bold ms-2" href="dashboard.php">
        Staff Panel
    </a>

    <div class="ms-auto d-flex align-items-center">

        <!-- NOTIFICATION -->
        <a href="notifications.php" class="me-3 position-relative text-dark">
            🔔
            <?php if($unread > 0): ?>
                <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                    <?= $unread ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- USER -->
        <div class="dropdown">
            <span data-bs-toggle="dropdown" style="cursor:pointer;">
                <?= htmlspecialchars($username) ?>
            </span>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="setting.php">Settings</a></li>
                <li><hr></li>
                <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
            </ul>
        </div>

    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <a class="<?= active($current,'dashboard.php') ?>" href="dashboard.php">🏠 Dashboard</a>


    <a class="<?= active($current,'my_vehicle.php') ?>" href="my_vehicle.php">🚌 My Vehicle</a>

    <a class="<?= active($current,'request_vehicle.php') ?>" href="request_vehicle.php">📩 Request Vehicle</a>

    <a class="<?= active($current,'salary.php') ?>" href="salary.php">💰 Salary</a>

    <!-- <a class="<?= active($current,'fuel_log.php') ?>" href="fuel_log.php">⛽ Fuel Logs</a> -->

    <a class="<?= active($current,'notifications.php') ?>" href="notifications.php">
        🔔 Notifications
        <?php if($unread > 0): ?>
            <span class="badge bg-danger"><?= $unread ?></span>
        <?php endif; ?>
    </a>
    <a href="apply_leave.php">
        📝 Apply Leave
    </a>

    <a href="my_leaves.php">
        📋 My Leaves
    </a>
    <a href="maintenance_request.php">
        <i class="fas fa-calendar-alt"></i>
           📩 Maintenance Request
    </a>
    <a href="my_maintenance.php">
        <i class="fas fa-calendar-alt"></i>
           📋 My Maintenance 
    </a>

    <a href="../logout.php" class="text-danger">🚪 Logout</a>

</div>

<?php if ($staffOnDuty): ?>
<script>window.FYP_ON_DUTY = true;</script>
<script src="assets/js/gps_autosend.js"></script>
<?php endif; ?>

<script>
document.getElementById("sidebarToggle")?.addEventListener("click", function(){
    document.getElementById("sidebar").classList.toggle("active");
});
</script>