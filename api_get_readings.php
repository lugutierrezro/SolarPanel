<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // db.php debe crear $conn como PDO

// Parámetros de consulta
$range = isset($_GET['range']) ? $_GET['range'] : 'day'; // day, week, month, hour
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

$query = "";
$params = [];

switch ($range) {
    case 'hour':
        $query = "SELECT 
                    id,
                    timestamp,
                    voltage,
                    current,
                    power,
                    temperature,
                    humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 hour'
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        $params[':limit'] = $limit;
        break;
    
    case 'day':
        $query = "SELECT 
                    id,
                    timestamp,
                    voltage,
                    current,
                    power,
                    temperature,
                    humidity
                  FROM sensor_readings
                  WHERE timestamp >= NOW() - INTERVAL '1 day'
                  ORDER BY timestamp DESC
                  LIMIT :limit";
        $params[':limit'] = $limit;
        break;
    
    case 'week':
        $query = "SELECT 
                    date_trunc('hour', timestamp) AS timestamp,
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
        $params[':limit'] = $limit;
        break;
    
    case 'month':
        $query = "SELECT 
                    date_trunc('day', timestamp) AS timestamp,
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
        $params[':limit'] = $limit;
        break;
    
    case 'latest':
        $query = "SELECT 
                    id,
                    timestamp,
                    voltage,
                    current,
                    power,
                    temperature,
                    humidity
                  FROM sensor_readings
                  ORDER BY timestamp DESC
                  LIMIT 1";
        break;
    
    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Rango no válido"]);
        exit();
}

try {
    $stmt = $conn->prepare($query);

    // Bind de parámetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $readings = [];
    foreach ($rows as $row) {
        $readings[] = [
            "timestamp" => $row['timestamp'],
            "voltage" => floatval($row['voltage']),
            "current" => floatval($row['current']),
            "power" => floatval($row['power']),
            "temperature" => floatval($row['temperature']),
            "humidity" => floatval($row['humidity'])
        ];
    }

    // Invertir para que los datos más antiguos estén primero
    $readings = array_reverse($readings);

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
