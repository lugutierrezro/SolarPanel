<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Responder OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';

// Leer JSON enviado por ESP32-CAM
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data["ip"]) || !isset($data["ssid"]) || !isset($data["mac"])) {
    echo json_encode(["status" => "error", "message" => "JSON inválido o faltan parámetros"]);
    exit();
}

// Preparar SQL con parámetros (PDO)
try {
    // Crear tabla si no existe
    $conn->exec("
        CREATE TABLE IF NOT EXISTS esp32_cam_devices (
            id SERIAL PRIMARY KEY,
            mac VARCHAR(50) UNIQUE,
            ssid VARCHAR(100),
            ip VARCHAR(50),
            last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insertar o actualizar
    $sql = "
        INSERT INTO esp32_cam_devices (mac, ssid, ip, last_update)
        VALUES (:mac, :ssid, :ip, CURRENT_TIMESTAMP)
        ON CONFLICT (mac)
        DO UPDATE SET ssid = EXCLUDED.ssid, ip = EXCLUDED.ip, last_update = CURRENT_TIMESTAMP
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':mac' => $data['mac'],
        ':ssid' => $data['ssid'],
        ':ip' => $data['ip']
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Dispositivo actualizado correctamente",
        "data" => ["ip" => $data["ip"], "ssid" => $data["ssid"], "mac" => $data["mac"]]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn = null;
?>
