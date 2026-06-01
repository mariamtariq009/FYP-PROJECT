<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$vehicle = isset($_GET['vehicle']) && $_GET['vehicle'] !== '' ? (int)$_GET['vehicle'] : null;

$logWhere = ['1=1'];
$polWhere = ['1=1'];
$repWhere = ['1=1'];
$params = [];

if ($from !== '' && $to !== '') {
    $logWhere[] = 'l.log_date BETWEEN :from AND :to';
    $polWhere[] = 'p.fuel_date BETWEEN :from AND :to';
    $repWhere[] = 'r.repair_date BETWEEN :from AND :to';
    $params[':from'] = $from;
    $params[':to'] = $to;
}

if ($vehicle !== null) {
    $logWhere[] = 'l.vehicle_id = :vehicle_id';
    $polWhere[] = 'p.vehicle_id = :vehicle_id';
    $repWhere[] = 'r.vehicle_id = :vehicle_id';
    $params[':vehicle_id'] = $vehicle;
}

$logSql = implode(' AND ', $logWhere);
$polSql = implode(' AND ', $polWhere);
$repSql = implode(' AND ', $repWhere);

function q(PDO $conn, string $sql, array $params = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

$pol = (float)(q($conn, "SELECT IFNULL(SUM(p.total_amount),0) FROM pol_records p WHERE $polSql", $params)->fetchColumn() ?? 0);
$repair = (float)(q($conn, "SELECT IFNULL(SUM(r.amount),0) FROM repair_history r WHERE $repSql", $params)->fetchColumn() ?? 0);
$logs = (int)q($conn, "SELECT COUNT(*) FROM log_book l WHERE $logSql", $params)->fetchColumn();

$kmData = q($conn, "
    SELECT l.log_date, IFNULL(SUM(l.distance),0) AS km
    FROM log_book l
    WHERE $logSql
    GROUP BY l.log_date
    ORDER BY l.log_date ASC
", $params)->fetchAll(PDO::FETCH_ASSOC);

$polGraph = q($conn, "
    SELECT p.fuel_date, IFNULL(SUM(p.liters),0) AS liters
    FROM pol_records p
    WHERE $polSql
    GROUP BY p.fuel_date
    ORDER BY p.fuel_date ASC
", $params)->fetchAll(PDO::FETCH_ASSOC);

$typeData = q($conn, "
    SELECT IFNULL(vc.category_name, 'Unknown') AS type, COUNT(*) AS total
    FROM vehicles v
    LEFT JOIN vehicle_categories vc ON vc.category_id = v.category_id
    GROUP BY vc.category_name
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total' => $pol + $repair,
    'pol' => $pol,
    'repair' => $repair,
    'logs' => $logs,
    'km' => $kmData,
    'polGraph' => $polGraph,
    'vehicleType' => $typeData,
]);
