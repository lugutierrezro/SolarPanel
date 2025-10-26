<?php
// api_save_reading.php - Guardar nuevas lecturas
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST']);
    exit;
}

require_once 'db.php';

try {
    // Obtener datos del body (JSON)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validar que llegó JSON válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Validar campos requeridos
    $required = ['voltage', 'current', 'power', 'temperature', 'humidity'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) && $data[$field] !== 0) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields: ' . implode(', ', $missing)
        ]);
        exit;
    }

    // Validar que sean números válidos
    $voltage = floatval($data['voltage']);
    $current = floatval($data['current']);
    $power = floatval($data['power']);
    $temperature = floatval($data['temperature']);
    $humidity = floatval($data['humidity']);

    // Validaciones de rango (opcional pero recomendado)
    if ($voltage < 0 || $voltage > 50) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Voltage out of range (0-50V)']);
        exit;
    }

    if ($humidity < 0 || $humidity > 100) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Humidity out of range (0-100%)']);
        exit;
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("
        INSERT INTO sensor_readings (voltage, current, power, temperature, humidity, timestamp)
        VALUES (:voltage, :current, :power, :temperature, :humidity, NOW())
        RETURNING id, timestamp
    ");

    $stmt->execute([
        ':voltage' => $voltage,
        ':current' => $current,
        ':power' => $power,
        ':temperature' => $temperature,
        ':humidity' => $humidity
    ]);

    $inserted = $stmt->fetch();

    if ($inserted) {
        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Reading saved successfully',
            'data' => [
                'id' => intval($inserted['id']),
                'timestamp' => $inserted['timestamp'],
                'values' => [
                    'voltage' => $voltage,
                    'current' => $current,
                    'power' => $power,
                    'temperature' => $temperature,
                    'humidity' => $humidity
                ]
            ]
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save reading'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
