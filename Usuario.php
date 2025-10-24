<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php'; // conexión PostgreSQL PDO

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // LOGIN DE USUARIO
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            echo json_encode(["error" => "Email y contraseña requeridos"]);
            exit();
        }

        $email = trim($data['email']);
        $password = trim($data['password']);

        // Buscar usuario en la base de datos
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["error" => "Usuario no encontrado"]);
            exit();
        }

        // Verificar la contraseña hasheada
        if (password_verify($password, $user['password'])) {
            // Generar token simple
            $token = bin2hex(random_bytes(16));

            // Actualizar token en DB
            $update = $conn->prepare("UPDATE usuarios SET token = :token, last_login = NOW() WHERE id = :id");
            $update->bindParam(':token', $token);
            $update->bindParam(':id', $user['id']);
            $update->execute();

            // Retornar JSON con usuario
            echo json_encode([
                "id" => intval($user['id']),
                "nombre" => $user['nombre'],
                "email" => $user['email'],
                "tipo" => $user['tipo'],
                "token" => $token
            ]);
        } else {
            echo json_encode(["error" => "Contraseña incorrecta"]);
        }

    } elseif ($method === 'GET') {
        // LISTAR USUARIOS
        $stmt = $conn->prepare("SELECT id, nombre, email, tipo FROM usuarios ORDER BY id ASC");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($usuarios);

    } else {
        echo json_encode(["error" => "Método no permitido"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
