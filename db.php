<?php
header("Content-Type: application/json");

$host = "dpg-d3ttdbmuk2gs73del1vg-a.oregon-postgres.render.com";
$port = "5432";
$db   = "solpanel_8cdl";
$user = "solpanel_8cdl_user";
$pass = "96OYZO1ww8FHmu27zB7xheuYikoCTB1W";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db;sslmode=require",
        $user,
        $pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta de prueba
    $stmt = $conn->query("SELECT 1 AS test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>


