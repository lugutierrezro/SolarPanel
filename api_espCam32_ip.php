<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data["ip"]) || !isset($data["ssid"]) || !isset($data["mac"])) {
    echo json_encode(["status" => "error", "message" => "JSON inválido o faltan parámetros"]);
    exit();
}

try {
    $sql = "
        INSERT INTO esp32cam_ips (mac, ssid, ip, last_update)
        VALUES (:mac, :ssid, :ip, NOW())
        ON CONFLICT (mac)
        DO UPDATE SET ssid = EXCLUDED.ssid, ip = EXCLUDED.ip, last_update = NOW()
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
        "data" => $data
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn = null;
?>
