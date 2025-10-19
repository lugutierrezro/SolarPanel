<?php
// Headers CORS y Content-Type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir conexión a base de datos
require_once 'db.php';

// Función de log para debugging
function logDebug($message, $data = null) {
    error_log("[INA219 API] " . $message);
    if ($data !== null) {
        error_log("[INA219 API] Data: " . json_encode($data));
    }
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    logDebug("Request method: " . $method);
    
    if ($method === 'POST') {
        // ==========================================
        // 📩 ESCRITURA: Guardar datos INA219
        // ==========================================
        
        // Leer datos crudos
        $raw_input = file_get_contents("php://input");
        logDebug("Raw input received", $raw_input);
        
        // Validar que llegó algo
        if (empty($raw_input)) {
            echo json_encode([
                "status" => "error",
                "message" => "No se recibió ningún dato (body vacío)"
            ]);
            exit();
        }
        
        // Parsear JSON
        $data = json_decode($raw_input, true);
        
        // Validar que el JSON es válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "JSON mal formado: " . json_last_error_msg(),
                "raw_input" => $raw_input
            ]);
            exit();
        }
        
        logDebug("JSON decoded successfully", $data);
        
        // Validar campos requeridos
        if (!isset($data['voltage']) || !isset($data['current']) || !isset($data['power'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Datos incompletos. Se requiere: voltage, current y power",
                "received_keys" => array_keys($data),
                "raw_input" => $raw_input
            ]);
            exit();
        }
        
        // Convertir a números
        $voltaje = floatval($data['voltage']);
        $corriente = floatval($data['current']);
        $potencia = floatval($data['power']);
        
        logDebug("Values to insert", [
            'voltaje' => $voltaje,
            'corriente' => $corriente,
            'potencia' => $potencia
        ]);
        
        // Insertar en base de datos
        $sql = "INSERT INTO panel_solar (voltaje, corriente, potencia, created_at) 
                VALUES (:v, :c, :p, NOW())";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':v' => $voltaje,
            ':c' => $corriente,
            ':p' => $potencia
        ]);
        
        if ($success) {
            $lastId = $conn->lastInsertId();
            logDebug("Insert successful, ID: " . $lastId);
            
            echo json_encode([
                "status" => "ok",
                "action" => "insert",
                "id" => intval($lastId),
                "voltage" => $voltaje,
                "current" => $corriente,
                "power" => $potencia,
                "message" => "Datos INA219 guardados correctamente"
            ]);
        } else {
            throw new Exception("Error al ejecutar INSERT");
        }
        
    } elseif ($method === 'GET') {
        // ==========================================
        // 📤 LECTURA: Obtener último registro
        // ==========================================
        
        $sql = "SELECT id, voltaje, corriente, potencia, created_at 
                FROM panel_solar 
                ORDER BY id DESC 
                LIMIT 1";
        
        $stmt = $conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            echo json_encode([
                "status" => "ok",
                "action" => "read",
                "id" => intval($row['id']),
                "voltage" => floatval($row['voltaje']),
                "current" => floatval($row['corriente']),
                "power" => floatval($row['potencia']),
                "timestamp" => $row['created_at']
            ]);
        } else {
            echo json_encode([
                "status" => "empty",
                "message" => "No hay datos INA219 en la base de datos aún"
            ]);
        }
        
    } else {
        // ==========================================
        // ❌ MÉTODO NO PERMITIDO
        // ==========================================
        
        http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "Método no permitido. Use GET o POST",
            "method_received" => $method
        ]);
    }
    
} catch (PDOException $e) {
    // ==========================================
    // ❌ ERROR DE BASE DE DATOS
    // ==========================================
    
    logDebug("Database error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de base de datos: " . $e->getMessage(),
        "error_code" => $e->getCode()
    ]);
    
} catch (Exception $e) {
    // ==========================================
    // ❌ ERROR GENERAL
    // ==========================================
    
    logDebug("General error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
