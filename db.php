<?php
$host = "dpg-d3ttdbmuk2gs73del1vg-a.oregon-postgres.render.com";
$db   = "solpanel_8cdl";
$user = "solpanel_8cdl_user";
$pass = "96OYZO1ww8FHmu27zB7xheuYikoCTB1W";
$port = "5432";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}
?>
