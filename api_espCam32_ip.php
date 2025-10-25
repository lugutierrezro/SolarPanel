<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Si el método es OPTIONS, solo responde (para CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';



// Leer cuerpo JSON enviado por ESP32-CAM
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data["ip"]) || !isset($data["ssid"]) || !isset($data["mac"])) {
    echo json_encode(["status" => "error", "message" => "JSON inválido o faltan parámetros"]);
    exit();
}

$ip = $conn->real_escape_string($data["ip"]);
$ssid = $conn->real_escape_string($data["ssid"]);
$mac = $conn->real_escape_string($data["mac"]);

// Crear tabla si no existe
$conn->query("
    CREATE TABLE IF NOT EXISTS esp32_cam_devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mac VARCHAR(50) UNIQUE,
        ssid VARCHAR(100),
        ip VARCHAR(50),
        last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
");

// Insertar o actualizar el dispositivo
$sql = "
    INSERT INTO esp32_cam_devices (mac, ssid, ip)
    VALUES ('$mac', '$ssid', '$ip')
    ON DUPLICATE KEY UPDATE ssid = VALUES(ssid), ip = VALUES(ip), last_update = CURRENT_TIMESTAMP
";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Dispositivo actualizado correctamente",
        "data" => ["ip" => $ip, "ssid" => $ssid, "mac" => $mac]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $conn->error]);
}

$conn->close();
?>
