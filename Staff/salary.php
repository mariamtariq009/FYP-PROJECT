<?php 
session_start();
include("../db.php");

// SECURITY CHECK
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "staff"){
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------
| FETCH SALARY (PDO FIXED)
|--------------------------------------
*/
$stmt = $conn->prepare("
    SELECT * 
    FROM salaries 
    WHERE user_id = :uid 
    ORDER BY id DESC
");
$stmt->bindParam(':uid', $user_id);
$stmt->execute();

$salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Salary</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="light">

<?php include 'layout.php'; ?>

<div class="content p-4">

<div class="main-content">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">My Salary History</h3>

        <span class="badge bg-dark">
            Total Records: <?= count($salaries) ?>
        </span>
    </div>

    <div class="card border-0 shadow-sm p-3" style="border-radius:15px;">

        <div class="table-responsive">

            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Month</th>
                        <th>Basic Salary</th>
                        <th>Bonus</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php if(count($salaries) > 0): ?>

                    <?php foreach($salaries as $row): 
                        $bonus = $row['bonus'] ?? 0;
                        $total = $row['amount'] + $bonus;
                    ?>

                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['month']) ?></td>

                            <td>Rs. <?= number_format($row['amount']) ?></td>

                            <td>Rs. <?= number_format($bonus) ?></td>

                            <td class="text-success fw-bold">
                                Rs. <?= number_format($total) ?>
                            </td>

                            <td>
                                <?php if($row['status'] == 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No salary records found
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

</div>

</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");

    if (btn && sidebar) {
        btn.addEventListener("click", function () {
            sidebar.classList.toggle("active");
        });
    }
});
</script>
</body>
</html>