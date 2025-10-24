<?php
header("Content-Type: application/json");

$host = "dpg-d3ttdbmuk2gs73del1vg-a.oregon-postgres.render.com";
$db   = "solpanel_8cdl";
$user = "solpanel_8cdl_user";
$pass = "96OYZO1ww8FHmu27zB7xheuYIKOCTB1W";
$port = "5432";

try {
    // Conexión PDO
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta de prueba
    $stmt = $conn->query("SELECT 1 AS test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "message" => "Conexión exitosa a la base de datos",
        "test" => $result
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
