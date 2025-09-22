<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// incluir conexión
require_once "db.php";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recibir JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['ldr_left']) && isset($input['ldr_right'])) {
            $ldrLeft = intval($input['ldr_left']);
            $ldrRight = intval($input['ldr_right']);

            $stmt = $conn->prepare("INSERT INTO ldr_readings (ldr_left, ldr_right) VALUES (:l, :r)");
            $stmt->execute(['l' => $ldrLeft, 'r' => $ldrRight]);

            echo json_encode([
                "status" => "ok",
                "ldr_left" => $ldrLeft,
                "ldr_right" => $ldrRight
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Valores LDR faltantes"]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener último registro
        $stmt = $conn->query("SELECT ldr_left, ldr_right, created_at 
                              FROM ldr_readings 
                              ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(["status" => "ok", "data" => $row]);
        } else {
            echo json_encode(["status" => "error", "message" => "No hay lecturas"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
