<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/../../db.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    die('Unauthorized');
}

$type = $_GET['type'] ?? 'staff';
$filename = $type . '_report_' . date('Y-m-d_H-i-s') . '.csv';

require __DIR__ . '/other_reports_data.php';
$rows = $GLOBALS['other_report_rows'] ?? [];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
if (!empty($rows)) {
    fputcsv($output, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['No Data Found']);
}
fclose($output);
exit();
