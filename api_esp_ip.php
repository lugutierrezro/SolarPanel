<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // GUARDAR IP DEL ESP32
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['ip'])) {
            echo json_encode(["error" => "IP requerida"]);
            exit();
        }

        $ip = trim($data['ip']);
        $ssid = isset($data['ssid']) ? trim($data['ssid']) : null;
        $mac = isset($data['mac']) ? trim($data['mac']) : null;

        // Insertar o actualizar (si existe el mismo MAC)
        if ($mac) {
            // Verificar si ya existe
            $check = $conn->prepare("SELECT id FROM esp32_ips WHERE mac = :mac LIMIT 1");
            $check->bindParam(':mac', $mac);
            $check->execute();

            if ($check->fetch(PDO::FETCH_ASSOC)) {
                // Actualizar
                $stmt = $conn->prepare("UPDATE esp32_ips SET ip = :ip, ssid = :ssid, last_update = NOW() WHERE mac = :mac");
                $stmt->bindParam(':ip', $ip);
                $stmt->bindParam(':ssid', $ssid);
                $stmt->bindParam(':mac', $mac);
                $stmt->execute();
                echo json_encode(["status" => "ok", "message" => "IP actualizada", "ip" => $ip]);
            } else {
                // Insertar nuevo
                $stmt = $conn->prepare("INSERT INTO esp32_ips (ip, ssid, mac) VALUES (:ip, :ssid, :mac)");
                $stmt->bindParam(':ip', $ip);
                $stmt->bindParam(':ssid', $ssid);
                $stmt->bindParam(':mac', $mac);
                $stmt->execute();
                echo json_encode(["status" => "ok", "message" => "IP guardada", "ip" => $ip]);
            }
        } else {
            // Sin MAC, solo insertar
            $stmt = $conn->prepare("INSERT INTO esp32_ips (ip, ssid) VALUES (:ip, :ssid)");
            $stmt->bindParam(':ip', $ip);
            $stmt->bindParam(':ssid', $ssid);
            $stmt->execute();
            echo json_encode(["status" => "ok", "message" => "IP guardada", "ip" => $ip]);
        }

    } elseif ($method === 'GET') {
        // OBTENER ÚLTIMA IP REGISTRADA
        $stmt = $conn->prepare("SELECT ip, ssid, mac, last_update FROM esp32_ips ORDER BY last_update DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                "status" => "ok",
                "ip" => $row['ip'],
                "ssid" => $row['ssid'],
                "mac" => $row['mac'],
                "last_update" => $row['last_update']
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No hay IPs registradas"]);
        }

    } else {
        echo json_encode(["error" => "Método no permitido"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
