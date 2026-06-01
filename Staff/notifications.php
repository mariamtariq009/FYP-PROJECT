<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ==========================
   DELETE NOTIFICATION
========================== */
if (isset($_GET['delete'])) {

    $notification_id = intval($_GET['delete']);

    $stmt = $conn->prepare("
        DELETE FROM notifications
        WHERE notification_id = ?
        AND user_id = ?
    ");

    $stmt->execute([$notification_id, $user_id]);

    header("Location: notifications.php");
    exit();
}

/* ==========================
   MARK ALL READ
========================== */
if (isset($_GET['markall'])) {

    $stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
    ");

    $stmt->execute([$user_id]);

    header("Location: notifications.php");
    exit();
}

/* ==========================
   SAFE UNREAD COUNT
========================== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
");

$stmt->execute([$user_id]);
$unreadRow = $stmt->fetch(PDO::FETCH_ASSOC);

$unreadCount = $unreadRow['total'] ?? 0;

/* ==========================
   GET NOTIFICATIONS
========================== */
$stmt = $conn->prepare("
    SELECT *
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==========================
   AUTO MARK READ
========================== */
$conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
")->execute([$user_id]);

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Notifications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

<style>
.content{
    margin-left:30px;
    padding:25px;
}

.notification-card{
    border:none;
    border-radius:15px;
    transition:.3s;
}

.notification-card:hover{
    transform:translateY(-2px);
}
</style>

</head>

<body>

<?php include 'layout.php'; ?>

<div class="content">

<div class="main-content">

<div class="container-fluid">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold">Notifications</h2>

        <small class="text-muted">
            Total Notifications: <?= count($notifications) ?>
        </small>
    </div>

    <div>
        <span class="badge bg-danger fs-6 me-2">
            Unread: <?= $unreadCount ?>
        </span>

        <a href="?markall=1" class="btn btn-primary">
            Mark All Read
        </a>
    </div>

</div>

<!-- NOTIFICATIONS -->
<?php if (!empty($notifications)): ?>

    <?php foreach ($notifications as $n): ?>

        <?php
        $typeColor = match($n['type'] ?? 'info') {
            'success' => 'success',
            'warning' => 'warning',
            'danger'  => 'danger',
            'info'    => 'info',
            default   => 'secondary'
        };
        ?>

        <div class="card notification-card shadow-sm mb-3">

            <div class="card-body">

                <div class="row align-items-center">

                    <div class="col-md-9">

                        <h5 class="fw-bold mb-1">
                            <?= htmlspecialchars($n['title'] ?? '') ?>
                        </h5>

                        <p class="mb-2 text-muted">
                            <?= htmlspecialchars($n['message'] ?? '') ?>
                        </p>

                        <small class="text-secondary">
                            <?= htmlspecialchars($n['created_at'] ?? '') ?>
                        </small>

                    </div>

                    <div class="col-md-3 text-end">

                        <span class="badge bg-<?= $typeColor ?>">
                            <?= ucfirst($n['type'] ?? 'info') ?>
                        </span>

                        <br><br>

                        <?php if (!empty($n['is_read'])): ?>
                            <span class="badge bg-success">Read</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Unread</span>
                        <?php endif; ?>

                        <br><br>

                        <a href="?delete=<?= $n['notification_id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete notification?')">
                            Delete
                        </a>

                    </div>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="alert alert-info">
        No notifications available.
    </div>

<?php endif; ?>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>