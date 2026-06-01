<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = (int)$_SESSION['user_id'];
$role = (string)$_SESSION['role'];

$reportType = $_GET['type'] ?? 'logbook';
$grouping = $_GET['grouping'] ?? 'monthly';
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$vehicleId = isset($_GET['vehicle_id']) && $_GET['vehicle_id'] !== ''
    ? (int)$_GET['vehicle_id']
    : null;

$allowedTypes = [
    'logbook',
    'pol',
    'repairs',
    'fuel_consumption',
    'fuel_cost',
    'repair_expense',
    'staff_repairs',
    'distance_summary'
];

$allowedGroupings = [
    'daily',
    'weekly',
    'monthly',
    'yearly',
    'vehicle'
];

if (!in_array($reportType, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Report Type']);
    exit();
}

if (!in_array($grouping, $allowedGroupings, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Grouping']);
    exit();
}

function stmt(PDO $conn, string $sql, array $params = []): PDOStatement {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

$where = [];
$params = [];

/* Staff restriction — assigned vehicles only */
if ($role === 'staff') {
    $where[] = "EXISTS (
        SELECT 1 FROM vehicle_assignments va
        WHERE va.vehicle_id = v.vehicle_id
          AND va.staff_id = :staff_id
    )";
    $params[':staff_id'] = $userId;
}

/* Report Types */
switch ($reportType) {

    case 'logbook':
        $table = "log_book l";
        $join = "JOIN vehicles v ON v.vehicle_id = l.vehicle_id";
        $dateCol = "l.log_date";
        $vehicleCol = "l.vehicle_id";

        $metrics = "
            COUNT(*) AS trips,
            SUM(l.distance) AS total_distance,
            SUM(l.petrol_issued) AS petrol_issued,
            SUM(l.petrol_consumed) AS petrol_consumed
        ";
        break;

    case 'pol':
        $table = "pol_records p";
        $join = "JOIN vehicles v ON v.vehicle_id = p.vehicle_id";
        $dateCol = "p.fuel_date";
        $vehicleCol = "p.vehicle_id";

        $metrics = "
            SUM(p.liters) AS total_liters,
            SUM(p.fuel_amount) AS fuel_amount,
            SUM(p.total_amount) AS total_amount
        ";
        break;

    case 'repairs':
        $table = "repair_history r";
        $join = "JOIN vehicles v ON v.vehicle_id = r.vehicle_id";
        $dateCol = "r.repair_date";
        $vehicleCol = "r.vehicle_id";

        $metrics = "
            COUNT(*) AS repairs,
            SUM(r.amount) AS total_amount,
            SUM(r.gst) AS gst,
            SUM(r.pst) AS pst
        ";
        break;

    case 'fuel_consumption':
        $table = "log_book l";
        $join = "JOIN vehicles v ON v.vehicle_id = l.vehicle_id";
        $dateCol = "l.log_date";
        $vehicleCol = "l.vehicle_id";

        $metrics = "
            SUM(l.petrol_issued) AS total_issued,
            SUM(l.petrol_consumed) AS total_consumed,
            SUM(l.remaining_petrol) AS remaining_petrol
        ";
        break;

    case 'fuel_cost':
        $table = "pol_records p";
        $join = "JOIN vehicles v ON v.vehicle_id = p.vehicle_id";
        $dateCol = "p.fuel_date";
        $vehicleCol = "p.vehicle_id";

        $metrics = "
            SUM(p.fuel_amount) AS fuel_cost,
            SUM(p.total_amount) AS total_cost
        ";
        break;

    case 'repair_expense':
        $table = "repair_history r";
        $join = "JOIN vehicles v ON v.vehicle_id = r.vehicle_id";
        $dateCol = "r.repair_date";
        $vehicleCol = "r.vehicle_id";

        $metrics = "
            SUM(r.amount) AS repair_amount,
            SUM(r.gst) AS gst,
            SUM(r.pst) AS pst
        ";
        break;

    case 'staff_repairs':
        $table = "repair_history r";
        $join = "
            JOIN vehicles v ON v.vehicle_id = r.vehicle_id
            JOIN users u ON u.id = r.staff_id
        ";
        $dateCol = "r.repair_date";
        $vehicleCol = "r.vehicle_id";

        $metrics = "
            u.name AS staff_name,
            COUNT(*) AS repairs,
            SUM(r.amount) AS total_amount
        ";
        break;

    case 'distance_summary':
        $table = "log_book l";
        $join = "JOIN vehicles v ON v.vehicle_id = l.vehicle_id";
        $dateCol = "l.log_date";
        $vehicleCol = "l.vehicle_id";

        $metrics = "
            SUM(l.distance) AS total_distance
        ";
        break;
}

/* Date Filters */
if (!empty($from) && !empty($to)) {
    $where[] = "$dateCol BETWEEN :from AND :to";
    $params[':from'] = $from;
    $params[':to'] = $to;
}

/* Vehicle Filter */
if (!empty($vehicleId)) {
    $where[] = "$vehicleCol = :vehicle_id";
    $params[':vehicle_id'] = $vehicleId;
}

/* Grouping */
switch ($grouping) {
    case 'daily':
        $groupKey = "DATE($dateCol)";
        break;

    case 'weekly':
        $groupKey = "YEARWEEK($dateCol)";
        break;

    case 'monthly':
        $groupKey = "DATE_FORMAT($dateCol,'%Y-%m')";
        break;

    case 'yearly':
        $groupKey = "YEAR($dateCol)";
        break;

    case 'vehicle':
        $groupKey = "v.vehicle_id";
        break;
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = "WHERE " . implode(' AND ', $where);
}

$sql = "
SELECT
    $groupKey AS group_label,
    v.vehicle_number,
    v.vehicle_name,
    IFNULL(vc.category_name, 'Unknown') AS vehicle_type,
    v.fuel_type,
    v.current_status AS vehicle_status,
    $metrics
FROM $table
$join
LEFT JOIN vehicle_categories vc ON vc.category_id = v.category_id
$whereSql
GROUP BY group_label, v.vehicle_id, v.vehicle_number, v.vehicle_name, vc.category_name, v.fuel_type, v.current_status
ORDER BY group_label ASC
";

$rows = stmt($conn, $sql, $params)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'rows' => $rows
]);