<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");


include 'db.php'; 

// Parámetros de consulta
$range = isset($_GET['range']) ? $_GET['range'] : 'day'; // day, week, month, hour
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

$query = "";
switch ($range) {
    case 'hour':
        $query = "SELECT id, timestamp, voltage, current, power, temperature, humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 hour'
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        break;

    case 'day':
        $query = "SELECT id, timestamp, voltage, current, power, temperature, humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 day'
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        break;

    case 'week':
        $query = "SELECT date_trunc('hour', timestamp) AS timestamp,
                         AVG(voltage) AS voltage,
                         AVG(current) AS current,
                         AVG(power) AS power,
                         AVG(temperature) AS temperature,
                         AVG(humidity) AS humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 week'
                  GROUP BY date_trunc('hour', timestamp)
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        break;

    case 'month':
        $query = "SELECT date_trunc('day', timestamp) AS timestamp,
                         AVG(voltage) AS voltage,
                         AVG(current) AS current,
                         AVG(power) AS power,
                         AVG(temperature) AS temperature,
                         AVG(humidity) AS humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 month'
                  GROUP BY date_trunc('day', timestamp)
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        break;

    case 'latest':
        $query = "SELECT id, timestamp, voltage, current, power, temperature, humidity
                  FROM sensor_readings
                  ORDER BY timestamp DESC
                  LIMIT 1";
        break;

    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Rango no válido"]);
        exit();
}

// Preparar y ejecutar con PDO
try {
    $stmt = $conn->prepare($query);
    if ($range !== 'latest') {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir valores a float y ordenar de más antiguo a más reciente
    $readings = array_reverse(array_map(function($row){
        return [
            "timestamp" => $row['timestamp'],
            "voltage" => isset($row['voltage']) ? floatval($row['voltage']) : null,
            "current" => isset($row['current']) ? floatval($row['current']) : null,
            "power" => isset($row['power']) ? floatval($row['power']) : null,
            "temperature" => isset($row['temperature']) ? floatval($row['temperature']) : null,
            "humidity" => isset($row['humidity']) ? floatval($row['humidity']) : null
        ];
    }, $readings));

    echo json_encode([
        "status" => "success",
        "range" => $range,
        "count" => count($readings),
        "data" => $readings
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
