<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Conexión a PostgreSQL
$host = "dpg-d38bpinfte5s73buuht0-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "solarpanel";
$user = "solarpanel_user";
$password = "oBsuyBBYSmxFICCdUxWOb97QK49EeAxG";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "No se pudo conectar a la base de datos"]);
    exit();
}

// Estadísticas del día actual
$query_today = "SELECT 
    COUNT(*) AS total_readings,
    AVG(voltage) AS avg_voltage,
    MAX(voltage) AS max_voltage,
    MIN(voltage) AS min_voltage,
    AVG(current) AS avg_current,
    MAX(current) AS max_current,
    AVG(power) AS avg_power,
    MAX(power) AS max_power,
    SUM(power) / 60.0 AS total_energy_wh,
    AVG(temperature) AS avg_temperature,
    MAX(temperature) AS max_temperature,
    MIN(temperature) AS min_temperature,
    AVG(humidity) AS avg_humidity,
    MAX(humidity) AS max_humidity,
    MIN(humidity) AS min_humidity
FROM sensor_readings
WHERE timestamp >= CURRENT_DATE";

$result_today = pg_query($conn, $query_today);
$stats_today = pg_fetch_assoc($result_today);

// Última lectura
$query_latest = "SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 1";
$result_latest = pg_query($conn, $query_latest);
$latest = pg_fetch_assoc($result_latest);

// Resumen semanal
$query_week = "SELECT 
    AVG(voltage) AS avg_voltage,
    AVG(current) AS avg_current,
    AVG(power) AS avg_power,
    AVG(temperature) AS avg_temperature,
    AVG(humidity) AS avg_humidity
FROM sensor_readings
WHERE timestamp >= NOW() - INTERVAL '7 days'";

$result_week = pg_query($conn, $query_week);
$stats_week = pg_fetch_assoc($result_week);

echo json_encode([
    "status" => "success",
    "latest" => [
        "timestamp" => $latest['timestamp'],
        "voltage" => floatval($latest['voltage']),
        "current" => floatval($latest['current']),
        "power" => floatval($latest['power']),
        "temperature" => floatval($latest['temperature']),
        "humidity" => floatval($latest['humidity'])
    ],
    "today" => [
        "total_readings" => intval($stats_today['total_readings']),
        "avg_voltage" => round(floatval($stats_today['avg_voltage']), 2),
        "max_voltage" => round(floatval($stats_today['max_voltage']), 2),
        "min_voltage" => round(floatval($stats_today['min_voltage']), 2),
        "avg_current" => round(floatval($stats_today['avg_current']), 3),
        "max_current" => round(floatval($stats_today['max_current']), 3),
        "avg_power" => round(floatval($stats_today['avg_power']), 2),
        "max_power" => round(floatval($stats_today['max_power']), 2),
        "total_energy_wh" => round(floatval($stats_today['total_energy_wh']), 2),
        "avg_temperature" => round(floatval($stats_today['avg_temperature']), 1),
        "max_temperature" => round(floatval($stats_today['max_temperature']), 1),
        "min_temperature" => round(floatval($stats_today['min_temperature']), 1),
        "avg_humidity" => round(floatval($stats_today['avg_humidity']), 1),
        "max_humidity" => round(floatval($stats_today['max_humidity']), 1),
        "min_humidity" => round(floatval($stats_today['min_humidity']), 1)
    ],
    "week_average" => [
        "avg_voltage" => round(floatval($stats_week['avg_voltage']), 2),
        "avg_current" => round(floatval($stats_week['avg_current']), 3),
        "avg_power" => round(floatval($stats_week['avg_power']), 2),
        "avg_temperature" => round(floatval($stats_week['avg_temperature']), 1),
        "avg_humidity" => round(floatval($stats_week['avg_humidity']), 1)
    ]
]);

pg_close($conn);
?>