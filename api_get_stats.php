<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // $conn viene de aquí (PDO)

try {
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
    
    $stmt_today = $conn->query($query_today);
    $stats_today = $stmt_today->fetch(PDO::FETCH_ASSOC);

    // Última lectura
    $query_latest = "SELECT * FROM sensor_readings ORDER BY timestamp DESC LIMIT 1";
    $stmt_latest = $conn->query($query_latest);
    $latest = $stmt_latest->fetch(PDO::FETCH_ASSOC);

    // Resumen semanal
    $query_week = "SELECT 
        AVG(voltage) AS avg_voltage,
        AVG(current) AS avg_current,
        AVG(power) AS avg_power,
        AVG(temperature) AS avg_temperature,
        AVG(humidity) AS avg_humidity
    FROM sensor_readings
    WHERE timestamp >= NOW() - INTERVAL '7 days'";
    
    $stmt_week = $conn->query($query_week);
    $stats_week = $stmt_week->fetch(PDO::FETCH_ASSOC);

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

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
?>
