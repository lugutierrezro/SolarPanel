<?php
header("Access-Control-Allow-Origin: *"); // permitir desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "db.php"; // conexión a la base de datos

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // --- 📌 Escritura (guardar posición) ---
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['servoPos'])) {
        $servoPos = intval($input['servoPos']);

        try {
            $sql = "INSERT INTO servo_log (pos) VALUES (:pos)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":pos", $servoPos, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                "status" => "ok",
                "servoPos" => $servoPos,
                "message" => "Posición insertada correctamente"
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se recibió servoPos"
        ]);
    }
} elseif ($method === 'GET') {
    // --- 📌 Lectura (última posición) ---
    try {
        $sql = "SELECT pos, created_at FROM servo_log ORDER BY id DESC LIMIT 1";
        $stmt = $conn->query($sql);
        $lastRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lastRow) {
            echo json_encode([
                "status" => "ok",
                "servoPos" => intval($lastRow['pos']),
                "timestamp" => $lastRow['created_at']
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No hay datos en la base de datos"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
} else {
    // Otros métodos no soportados
    echo json_encode([
        "status" => "error",
        "message" => "Método no permitido"
    ]);
}
?>
