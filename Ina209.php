<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Conexión a la base de datos PostgreSQL
include 'db.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Leer datos enviados por POST
        $raw_input = file_get_contents("php://input");
        $data = json_decode($raw_input, true);
        
        if ($data === null) {
            echo json_encode([
                "status" => "error",
                "message" => "JSON mal formado o no recibido",
                "raw_input" => $raw_input
            ]);
            exit();
        }

        // El ESP32 envía: voltage, current, power
        // La base de datos espera: voltaje, corriente, potencia
        if(isset($data['voltage']) && isset($data['current']) && isset($data['power'])) {
            
            $voltaje = floatval($data['voltage']);
            $corriente = floatval($data['current']);
            $potencia = floatval($data['power']);
            
            $sql = "INSERT INTO panel_solar (voltaje, corriente, potencia) VALUES (:v, :c, :p)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':v' => $voltaje,
                ':c' => $corriente,
                ':p' => $potencia
            ]);

            echo json_encode([
                "status" => "ok",
                "action" => "insert",
                "voltage" => $voltaje,
                "current" => $corriente,
                "power" => $potencia,
                "message" => "Datos INA219 guardados correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Datos incompletos - faltan voltage, current o power",
                "raw_input" => $raw_input
            ]);
        }
    } elseif ($method === 'GET') {
        // Leer el último registro
        $stmt = $conn->prepare("SELECT voltaje, corriente, potencia, created_at FROM panel_solar ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                "status" => "ok",
                "action" => "read",
                "voltage" => floatval($row['voltaje']),
                "current" => floatval($row['corriente']),
                "power" => floatval($row['potencia']),
                "timestamp" => $row['created_at']
            ]);
        } else {
            echo json_encode([
                "status" => "empty",
                "message" => "No hay datos INA219 aún"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido",
            "method_received" => $method
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>

