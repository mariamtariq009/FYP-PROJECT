<?php
session_start();
include("../db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/*
|--------------------------------------
| FETCH DUTIES AS EVENTS
|--------------------------------------
*/
$stmt = $conn->prepare("
    SELECT 
        d.id,
        d.route_name,
        d.location,
        d.duty_date,
        d.start_time,
        d.end_time,
        d.status,
        u.name AS staff_name
    FROM duties d
    LEFT JOIN users u ON d.user_id = u.id
");

$stmt->execute();
$duties = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------
| CONVERT TO CALENDAR EVENTS
|--------------------------------------
*/
$events = [];

foreach ($duties as $d) {

    $events[] = [
        'id' => $d['id'],
        'title' => $d['staff_name'] . " - " . $d['route_name'],
        'start' => $d['duty_date'] . "T" . $d['start_time'],
        'end' => $d['duty_date'] . "T" . $d['end_time'],

        'color' => 
            ($d['status'] == 'Active') ? '#28a745' :
            (($d['status'] == 'Completed') ? '#0d6efd' : '#dc3545')
    ];
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Duty Calendar</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<link rel="stylesheet" href="assets/css/style.css">
<style>
#calendar {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
}
</style>

</head>

<body class="bg-light">

<?php include 'includes/layout.php'; ?>

<div class="content p-4">

<div class="container-fluid">

<h3 class="mb-3">📅 Duty Calendar View</h3>

<div id="calendar"></div>

</div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        editable: false,

        events: <?= json_encode($events) ?>

    });

    calendar.render();
});

</script>script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>