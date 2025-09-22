<?php
$host = "dpg-d38bpinfte5s73buuht0-a.oregon-postgres.render.com";
$db   = "solarpanel";
$user = "solarpanel_user";
$pass = "oBsuyBBYSmxFICCdUxWOb97QK49EeAxG";
$port = "5432";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}
?>
