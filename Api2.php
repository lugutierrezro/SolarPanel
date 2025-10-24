<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

// Leer POST JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['voltage']) || !isset($input['current']) || !isset($input['power']) || !isset($input['temperature']) || !isset($input['humidity'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit();
}

// Preparar e insertar en PostgreSQL
$query = "INSERT INTO sensor_readings (voltage, current, power, temperature, humidity) VALUES ($1, $2, $3, $4, $5)";
$result = pg_query_params($conn, $query, [
    $input['voltage'],
    $input['current'],
    $input['power'],
    $input['temperature'],
    $input['humidity']
]);

if ($result) {
    echo json_encode(["status" => "success", "message" => "Datos guardados correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al insertar los datos"]);
}

pg_close($conn);
?>

