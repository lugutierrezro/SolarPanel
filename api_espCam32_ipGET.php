<?php
header("Content-Type: application/json");
require 'db.php';

try {
    $stmt = $conn->query("SELECT ip, ssid, mac FROM esp32cam_ips ORDER BY last_update DESC LIMIT 1");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode([
            "status" => "ok",
            "ip" => $data['ip'],
            "ssid" => $data['ssid'],
            "mac" => $data['mac']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No hay registros"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn = null;
?>
