<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "db.php"; // conexión PDO a PostgreSQL

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // 📩 Guardar nueva posición
        $input = json_decode(file_get_contents('php://input'), true);

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

    } elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
        // 📤 Leer último valor
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
