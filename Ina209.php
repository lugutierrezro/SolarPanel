<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'db.php'; // tu conexión PDO pgsql

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if(isset($data['voltage']) && isset($data['current']) && isset($data['power'])) {
    
    $voltaje = $data['voltage'];
    $corriente = $data['current'];
    $potencia = $data['power'];
    
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
