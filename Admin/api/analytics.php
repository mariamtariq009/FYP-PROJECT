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

$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : null;
$to = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : null;
$vehicleId = isset($_GET['vehicle_id']) && $_GET['vehicle_id'] !== '' ? (int)$_GET['vehicle_id'] : null;

function addDateFilter(array &$where, array &$params, string $col, ?string $from, ?string $to): void
{
    if ($from !== null && $to !== null) {
        $where[] = "$col BETWEEN :from AND :to";
        $params[':from'] = $from;
        $params[':to'] = $to;
    }
}

function addVehicleFilter(array &$where, array &$params, string $col, ?int $vehicleId): void
{
    if ($vehicleId !== null) {
        $where[] = "$col = :vehicle_id";
        $params[':vehicle_id'] = $vehicleId;
    }
}

function staffVehicleClause(string $vehicleAlias = 'v'): string
{
    return "EXISTS (
        SELECT 1 FROM vehicle_assignments va
        WHERE va.vehicle_id = $vehicleAlias.vehicle_id
          AND va.staff_id = :staff_id
          AND va.duty_status IN ('assigned','on_duty')
    )";
}

function fetchAll(PDO $conn, string $sql, array $params = []): array
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function fetchValue(PDO $conn, string $sql, array $params = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

$vehicleWhere = [];
$vehicleParams = [];

if ($role === 'staff') {
    $vehicleWhere[] = staffVehicleClause('v');
    $vehicleParams[':staff_id'] = $userId;
}
addVehicleFilter($vehicleWhere, $vehicleParams, 'v.vehicle_id', $vehicleId);

$vehicleWhereSql = $vehicleWhere ? ('WHERE ' . implode(' AND ', $vehicleWhere)) : '';

$totalVehicles = (int)fetchValue(
    $conn,
    "SELECT COUNT(*) FROM vehicles v $vehicleWhereSql",
    $vehicleParams
);

$logWhere = [];
$logParams = $vehicleParams;
if ($role === 'staff') {
    $logWhere[] = staffVehicleClause('v');
}
addDateFilter($logWhere, $logParams, 'l.log_date', $from, $to);
addVehicleFilter($logWhere, $logParams, 'l.vehicle_id', $vehicleId);
$logWhereSql = $logWhere ? ('WHERE ' . implode(' AND ', $logWhere)) : '';

$totalTrips = (int)fetchValue(
    $conn,
    "SELECT COUNT(*) FROM log_book l JOIN vehicles v ON v.vehicle_id = l.vehicle_id $logWhereSql",
    $logParams
);

$totalDistance = (float)(fetchValue(
    $conn,
    "SELECT IFNULL(SUM(l.distance),0) FROM log_book l JOIN vehicles v ON v.vehicle_id = l.vehicle_id $logWhereSql",
    $logParams
) ?? 0);

$polWhere = [];
$polParams = $vehicleParams;
if ($role === 'staff') {
    $polWhere[] = staffVehicleClause('v');
}
addDateFilter($polWhere, $polParams, 'p.fuel_date', $from, $to);
addVehicleFilter($polWhere, $polParams, 'p.vehicle_id', $vehicleId);
$polWhereSql = $polWhere ? ('WHERE ' . implode(' AND ', $polWhere)) : '';

$totalFuelLiters = (float)(fetchValue(
    $conn,
    "SELECT IFNULL(SUM(p.liters),0) FROM pol_records p JOIN vehicles v ON v.vehicle_id = p.vehicle_id $polWhereSql",
    $polParams
) ?? 0);

$totalFuelAmount = (float)(fetchValue(
    $conn,
    "SELECT IFNULL(SUM(p.total_amount),0) FROM pol_records p JOIN vehicles v ON v.vehicle_id = p.vehicle_id $polWhereSql",
    $polParams
) ?? 0);

$repWhere = [];
$repParams = $vehicleParams;
if ($role === 'staff') {
    $repWhere[] = staffVehicleClause('v');
}
addDateFilter($repWhere, $repParams, 'r.repair_date', $from, $to);
addVehicleFilter($repWhere, $repParams, 'r.vehicle_id', $vehicleId);
$repWhereSql = $repWhere ? ('WHERE ' . implode(' AND ', $repWhere)) : '';

$totalRepairAmount = (float)(fetchValue(
    $conn,
    "SELECT IFNULL(SUM(r.amount + IFNULL(r.gst,0) + IFNULL(r.pst,0)),0)
     FROM repair_history r JOIN vehicles v ON v.vehicle_id = r.vehicle_id
     $repWhereSql",
    $repParams
) ?? 0);

$trendLimit = 12;

$distanceTrend = fetchAll(
    $conn,
    "SELECT DATE_FORMAT(l.log_date,'%Y-%m') ym,
            COUNT(*) trips,
            IFNULL(SUM(l.distance),0) total_distance
     FROM log_book l
     JOIN vehicles v ON v.vehicle_id = l.vehicle_id
     $logWhereSql
     GROUP BY ym
     ORDER BY ym DESC
     LIMIT $trendLimit",
    $logParams
);
$distanceTrend = array_reverse($distanceTrend);

$fuelTrend = fetchAll(
    $conn,
    "SELECT DATE_FORMAT(p.fuel_date,'%Y-%m') ym,
            IFNULL(SUM(p.liters),0) liters,
            IFNULL(SUM(p.total_amount),0) amount
     FROM pol_records p
     JOIN vehicles v ON v.vehicle_id = p.vehicle_id
     $polWhereSql
     GROUP BY ym
     ORDER BY ym DESC
     LIMIT $trendLimit",
    $polParams
);
$fuelTrend = array_reverse($fuelTrend);

$repairTrend = fetchAll(
    $conn,
    "SELECT DATE_FORMAT(r.repair_date,'%Y-%m') ym,
            IFNULL(SUM(r.amount + IFNULL(r.gst,0) + IFNULL(r.pst,0)),0) amount
     FROM repair_history r
     JOIN vehicles v ON v.vehicle_id = r.vehicle_id
     $repWhereSql
     GROUP BY ym
     ORDER BY ym DESC
     LIMIT $trendLimit",
    $repParams
);
$repairTrend = array_reverse($repairTrend);

$vehicleTypes = fetchAll(
    $conn,
    "SELECT IFNULL(vc.category_name, 'Unknown') AS label, COUNT(*) AS value
     FROM vehicles v
     LEFT JOIN vehicle_categories vc ON vc.category_id = v.category_id
     $vehicleWhereSql
     GROUP BY vc.category_name
     ORDER BY value DESC",
    $vehicleParams
);

$fuelTypes = fetchAll(
    $conn,
    "SELECT IFNULL(v.fuel_type, 'Unknown') AS label, COUNT(*) AS value
     FROM vehicles v
     $vehicleWhereSql
     GROUP BY v.fuel_type
     ORDER BY value DESC",
    $vehicleParams
);

$totalStaff = 0;
$activeBookings = 0;
$onDutyStaff = 0;

if ($role === 'admin') {
    $totalStaff = (int)fetchValue($conn, "SELECT COUNT(*) FROM users WHERE role='staff'");
    $activeBookings = (int)fetchValue(
        $conn,
        "SELECT COUNT(*) FROM bookings WHERE status='approved' AND NOW() BETWEEN departure_datetime AND arrival_datetime"
    );
    $onDutyStaff = (int)fetchValue(
        $conn,
        "SELECT COUNT(*) FROM users WHERE role='staff' AND availability_status='on_duty'"
    );
}

echo json_encode([
    'totals' => [
        'vehicles' => $totalVehicles,
        'trips' => $totalTrips,
        'distance' => $totalDistance,
        'fuel_liters' => $totalFuelLiters,
        'fuel_amount' => $totalFuelAmount,
        'repair_amount' => $totalRepairAmount,
        'staff' => $totalStaff,
        'active_bookings' => $activeBookings,
        'on_duty_staff' => $onDutyStaff,
    ],
    'trends' => [
        'distance' => $distanceTrend,
        'fuel' => $fuelTrend,
        'repair' => $repairTrend,
    ],
    'splits' => [
        'vehicle_types' => $vehicleTypes,
        'fuel_types' => $fuelTypes,
    ],
]);
