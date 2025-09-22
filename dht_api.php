<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Importar la conexión a la base de datos PostgreSQL
include 'db.php';

// Leer JSON enviado por el ESP32
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['temperature']) && isset($input['humidity'])) {
        // Guardar los datos en la base de datos
        $stmt = $conn->prepare("INSERT INTO dht_logs (temperature, humidity) VALUES (:temp, :hum)");
        $stmt->bindParam(':temp', $input['temperature']);
        $stmt->bindParam(':hum', $input['humidity']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "message" => "Datos guardados"]);
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Leer el último registro
        $stmt = $conn->prepare("SELECT temperature, humidity FROM dht_logs ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                "status" => "ok",
                "data" => [
                    "temperature" => floatval($row['temperature']),
                    "humidity" => floatval($row['humidity'])
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No hay datos"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Datos incompletos o método no soportado"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
