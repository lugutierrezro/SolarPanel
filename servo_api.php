<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "db.php"; // conexión PDO

try {
    $method = $_SERVER["REQUEST_METHOD"];
    
    if ($method === "POST") {
        // 📩 ESCRITURA: Guardar nueva posición desde ESP32
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['servoPos'])) {
            $servoPos = intval($input['servoPos']);
            
            // Validar rango
            if ($servoPos < 0 || $servoPos > 180) {
                echo json_encode([
                    "status" => "error",
                    "message" => "servoPos fuera de rango (0-180)"
                ]);
                exit();
            }
            
            $sql = "INSERT INTO servo_movements (servoPos, created_at) VALUES (:pos, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([":pos" => $servoPos]);
            
            echo json_encode([
                "status" => "ok",
                "action" => "insert",
                "servoPos" => $servoPos,
                "message" => "Posición guardada correctamente"
            ]);
            
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Falta el parámetro servoPos"
            ]);
        }
        
    } elseif ($method === "GET") {
        // 📤 LECTURA: Obtener última posición para el ESP32
        $sql = "SELECT servoPos, created_at 
                FROM servo_movements 
                ORDER BY id DESC 
                LIMIT 1";
        
        $stmt = $conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            echo json_encode([
                "status" => "ok",
                "action" => "read",
                "servoPos" => intval($row["servoPos"]),
                "timestamp" => $row["created_at"]
            ]);
        } else {
            // Si no hay datos, enviar posición por defecto
            echo json_encode([
                "status" => "ok",
                "action" => "read",
                "servoPos" => 90,
                "timestamp" => date('Y-m-d H:i:s'),
                "message" => "Usando posición por defecto"
            ]);
        }
        
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido. Use GET o POST"
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
}
?>
