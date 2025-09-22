<?php
// Permitir CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Incluir conexión PDO a PostgreSQL
require "db.php";

try {
    // ====== POST: guardar nueva posición ======
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $input = json_decode(file_get_contents('php://input'), true);

        // Debug opcional: guardar lo que llega
        // file_put_contents("debug.txt", date('Y-m-d H:i:s') . " POST: " . json_encode($input) . "\n", FILE_APPEND);

        if (isset($input['servoPos'])) {
            $servoPos = intval($input['servoPos']);

            $sql = "INSERT INTO servo_movements (servoPos, created_at) VALUES (:pos, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([":pos" => $servoPos]);

            echo json_encode([
                "status" => "ok",
                "action" => "insert",
                "servoPos" => $servoPos,
                "message" => "Servo actualizado"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Falta servoPos en POST"
            ]);
        }

    // ====== GET: leer último valor ======
    } elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
        $sql = "SELECT servoPos, created_at 
                FROM servo_movements 
                ORDER BY id DESC LIMIT 1";
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
            echo json_encode([
                "status" => "empty",
                "message" => "No hay registros aún"
            ]);
        }

    // ====== Otros métodos ======
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
