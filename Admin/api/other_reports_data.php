<?php
declare(strict_types=1);

if (!isset($conn)) {
    require __DIR__ . '/../../db.php';
}

$type = $_GET['type'] ?? 'staff';

function otherStmt(PDO $conn, string $sql, array $params = []): array
{
    $s = $conn->prepare($sql);
    $s->execute($params);
    return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$rows = [];

switch ($type) {
    case 'staff':
        $rows = otherStmt($conn, "
            SELECT id, name, username, email, phone, cnic, joining_date,
                   license_number, license_expiry, employment_status, availability_status
            FROM users WHERE role='staff' ORDER BY name ASC
        ");
        break;
    case 'vehicles':
        $rows = otherStmt($conn, "
            SELECT v.vehicle_id, v.vehicle_name, v.vehicle_number,
                   IFNULL(vc.category_name, 'Unknown') AS vehicle_type,
                   v.model_year, v.fuel_type, v.current_status,
                   v.gps_device_number, v.last_location,
                   u.name AS assigned_staff, u.username AS staff_username
            FROM vehicles v
            LEFT JOIN vehicle_categories vc ON vc.category_id = v.category_id
            LEFT JOIN vehicle_assignments va ON va.vehicle_id = v.vehicle_id
                AND va.duty_status IN ('assigned','on_duty')
            LEFT JOIN users u ON u.id = va.staff_id
            ORDER BY v.vehicle_name ASC
        ");
        break;
    case 'license':
        $rows = otherStmt($conn, "
            SELECT name, username, cnic, license_number, license_expiry,
                   DATEDIFF(license_expiry, CURDATE()) AS days_left
            FROM users
            WHERE role='staff' AND license_expiry IS NOT NULL
            ORDER BY license_expiry ASC
        ");
        break;
    case 'maintenance':
        $rows = otherStmt($conn, "
            SELECT v.vehicle_name, v.vehicle_number,
                (SELECT IFNULL(SUM(amount),0) FROM repair_history r WHERE r.vehicle_id = v.vehicle_id) AS repair_cost,
                (SELECT IFNULL(SUM(total_amount),0) FROM pol_records p WHERE p.vehicle_id = v.vehicle_id) AS fuel_cost,
                (
                    IFNULL((SELECT SUM(amount) FROM repair_history r WHERE r.vehicle_id = v.vehicle_id),0)
                    + IFNULL((SELECT SUM(total_amount) FROM pol_records p WHERE p.vehicle_id = v.vehicle_id),0)
                ) AS total_cost
            FROM vehicles v ORDER BY total_cost DESC
        ");
        break;
    case 'assignments':
        $rows = otherStmt($conn, "
            SELECT u.name AS staff_name, u.username, u.availability_status,
                   v.vehicle_name, v.vehicle_number,
                   IFNULL(vc.category_name, 'Unknown') AS vehicle_type,
                   v.current_status, va.duty_status, va.assignment_date
            FROM vehicle_assignments va
            JOIN users u ON u.id = va.staff_id
            JOIN vehicles v ON v.vehicle_id = va.vehicle_id
            LEFT JOIN vehicle_categories vc ON vc.category_id = v.category_id
            WHERE va.duty_status IN ('assigned','on_duty')
            ORDER BY va.assignment_date DESC
        ");
        break;
    case 'inactive_vehicles':
        $rows = otherStmt($conn, "
            SELECT v.vehicle_id, v.vehicle_name, v.vehicle_number, v.current_status,
                GREATEST(
                    IFNULL((SELECT MAX(log_date) FROM log_book WHERE vehicle_id = v.vehicle_id), '1970-01-01'),
                    IFNULL((SELECT MAX(fuel_date) FROM pol_records WHERE vehicle_id = v.vehicle_id), '1970-01-01'),
                    IFNULL((SELECT MAX(DATE(created_at)) FROM vehicle_gps_logs WHERE vehicle_id = v.vehicle_id), '1970-01-01')
                ) AS last_used
            FROM vehicles v
            HAVING last_used < DATE_SUB(CURDATE(), INTERVAL 5 DAY)
               OR v.current_status = 'inactive'
            ORDER BY last_used ASC
        ");
        break;

    case 'bookings':
        $rows = otherStmt($conn, "
            SELECT b.id, b.full_name, b.department, b.place_from, b.place_to,
                   b.departure_datetime, b.arrival_datetime, b.status,
                   v.vehicle_number, u.name AS driver_name
            FROM bookings b
            LEFT JOIN vehicles v ON v.vehicle_id = b.vehicle_id
            LEFT JOIN users u ON u.id = b.staff_id
            ORDER BY b.created_at DESC
        ");
        break;
    default:
        $rows = [];
}

$GLOBALS['other_report_rows'] = $rows;
