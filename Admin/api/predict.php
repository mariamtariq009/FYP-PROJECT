<?php

$data = [

    "vehicle_type" => $_POST['vehicle_type'],
    "company" => $_POST['company'],
    "model" => $_POST['model'],
    "engine_cc" => $_POST['engine_cc'],
    "fuel_type" => $_POST['fuel_type'],
    "vehicle_age" => $_POST['vehicle_age'],
    "avg_km_per_day" => $_POST['avg_km_per_day'],
    "traffic_condition" => $_POST['traffic_condition'],
    "ac_usage" => $_POST['ac_usage'],
    "road_type" => $_POST['road_type'],
    "city" => $_POST['city'],
    "load_weight" => $_POST['load_weight'],
    "fuel_avg_kmpl" => $_POST['fuel_avg_kmpl']

];

$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

$context = stream_context_create($options);

$result = file_get_contents(
    "http://127.0.0.1:5000/predict",
    false,
    $context
);

$response = json_decode($result, true);

?>

<!DOCTYPE html>
<html>
<head>

    <title>Prediction Result</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="text-success mb-4">
            Fuel Prediction Result
        </h2>

        <h4>
            Monthly Fuel Used:
            <span class="text-primary">
                <?php echo $response['monthly_fuel_used_liters']; ?>
                Liters
            </span>
        </h4>

        <h4 class="mt-3">
            Estimated Cost:
            <span class="text-danger">
                Rs.
                <?php echo $response['estimated_cost']; ?>
            </span>
        </h4>

        <a href="../prediction.php" class="btn btn-dark mt-4">
            Predict Again
        </a>

    </div>

</div>

</body>
</html>