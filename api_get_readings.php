<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; 

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$conn) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "No se pudo conectar a la base de datos"]);
    exit();
}

// Parámetros de consulta
$range = isset($_GET['range']) ? $_GET['range'] : 'day'; // day, week, month, hour
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

$query = "";
switch ($range) {
    case 'hour':
        // Últimas lecturas de la última hora
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
                  LIMIT $limit";
        break;
    
    case 'day':
        // Últimas lecturas del día (cada 5 minutos aproximadamente)
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
                  LIMIT $limit";
        break;
    
    case 'week':
        // Promedio por hora de la última semana
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
                  LIMIT $limit";
        break;
    
    case 'month':
        // Promedio por día del último mes
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
                  LIMIT $limit";
        break;
    
    case 'latest':
        // Última lectura
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

$result = pg_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al consultar datos"]);
    exit();
}

$readings = [];
while ($row = pg_fetch_assoc($result)) {
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

pg_close($conn);

?>
