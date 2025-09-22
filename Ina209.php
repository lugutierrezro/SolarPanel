<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Conexión a la base de datos PostgreSQL
include 'db.php'; // tu db.php con PDO pgsql

// Leer datos enviados por POST
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if(isset($data['voltaje']) && isset($data['corriente']) && isset($data['potencia'])) {
    
    $voltaje = $data['voltaje'];
    $corriente = $data['corriente'];
    $potencia = $data['potencia'];
    
    try {
        $sql = "INSERT INTO panel_solar (voltaje, corriente, potencia) VALUES (:v, :c, :p)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':v' => $voltaje,
            ':c' => $corriente,
            ':p' => $potencia
        ]);

        echo json_encode(["status" => "success", "message" => "Datos guardados correctamente"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}
?>
