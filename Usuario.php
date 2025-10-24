<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'db.php'; // conexión PostgreSQL PDO

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        // REGISTRAR USUARIO
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['nombre'], $data['email'], $data['password'])) {
            echo json_encode(["error" => "Nombre, email y contraseña requeridos"]);
            exit();
        }

        $nombre = trim($data['nombre']);
        $email = trim($data['email']);
        $password = password_hash(trim($data['password']), PASSWORD_DEFAULT);

        // Verificar si ya existe el email
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(["error" => "El email ya está registrado"]);
            exit();
        }

        // Insertar usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Usuario registrado correctamente", "nombre" => $nombre, "email" => $email]);
        } else {
            echo json_encode(["error" => "Error al registrar usuario"]);
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
    // Siempre devolver JSON aunque haya error
    echo json_encode(["error" => $e->getMessage()]);
}
