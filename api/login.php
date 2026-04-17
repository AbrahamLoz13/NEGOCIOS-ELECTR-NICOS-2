<?php
// api/login.php
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

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

// AHORA PEDIMOS TODAS LAS COLUMNAS A LA BASE DE DATOS
$stmt = $conn->prepare("SELECT id, nombre, correo, telefono, password, rol, estado, calle, colonia, ciudad, numeroCasa, codigoPostal FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $estado_actual = strtolower(trim($user['estado'])); 

    if ($estado_actual === 'suspendido') {
        echo json_encode([
            "status"  => "error", 
            "message" => "ACCESO DENEGADO: Esta cuenta ha sido suspendida. Contacta al administrador."
        ]);
        exit;
    }

    // Se verifica la contraseña encriptada o la contraseña en texto plano
    if (password_verify($pass, $user['password']) || $pass === $user['password']) {
        echo json_encode([
            "status" => "success",
            "data"   => [
                "id"           => $user['id'],
                "nombre"       => $user['nombre'],
                "correo"       => $user['correo'],
                "rol"          => $user['rol'],
                "telefono"     => $user['telefono'] ?? '',
                "calle"        => $user['calle'] ?? '',
                "colonia"      => $user['colonia'] ?? '',
                "ciudad"       => $user['ciudad'] ?? '',
                "codigoPostal" => $user['codigoPostal'] ?? ''
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