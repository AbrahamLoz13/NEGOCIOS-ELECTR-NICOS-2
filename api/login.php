<?php
// api/login.php
require 'db.php';
header('Content-Type: application/json');

// Desactivar errores visuales para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$correo = $data['correo'] ?? '';
$pass   = $data['password'] ?? '';

if (empty($correo) || empty($pass)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos por llenar."]);
    exit;
}

// Consultamos usuario, su estado y SU ROL
$stmt = $conn->prepare("SELECT id, nombre, password, rol, estado FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // 1. VERIFICACIÓN DE SUSPENSIÓN
    $estado_actual = strtolower(trim($user['estado'])); 

    if ($estado_actual === 'suspendido') {
        echo json_encode([
            "status"  => "error", 
            "message" => "🚫 ACCESO DENEGADO: Esta cuenta ha sido suspendida. Contacta al administrador."
        ]);
        exit;
    }

    // 2. VERIFICAR CONTRASEÑA
    if ($pass === $user['password'] || password_verify($pass, $user['password'])) {
        // Login Exitoso: Devolvemos el rol que tenga en la BD (admin, vendedor o usuario)
        echo json_encode([
            "status" => "success",
            "data"   => [
                "id"     => $user['id'],
                "nombre" => $user['nombre'],
                "rol"    => $user['rol'] // Aquí viaja el dato clave para el JS
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "El correo no está registrado."]);
}

$stmt->close();
$conn->close();
?>