<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Conexión a la base de datos
include 'bd.php'; // Tu conexión ya existente

// Leer datos enviados por POST
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if(isset($data['voltaje']) && isset($data['corriente']) && isset($data['potencia'])) {
    
    $voltaje = $data['voltaje'];
    $corriente = $data['corriente'];
    $potencia = $data['potencia'];
    
    $sql = "INSERT INTO panel_solar (voltaje, corriente, potencia) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddd", $voltaje, $corriente, $potencia);
    
    if($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Datos guardados correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar los datos"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}

$conn->close();
?>
