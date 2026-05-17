<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 1. Conexión a la BD
require 'db.php'; // Asegúrate de que el archivo db.php esté en la misma ruta o ajusta la ruta

// 2. Recibir datos JSON
$input = json_decode(file_get_contents("php://input"), true);
$nombre   = trim($input['nombre'] ?? '');
$correo   = trim($input['correo'] ?? '');
$password_plana = $input['password'] ?? '';
$rol      = 'usuario'; // Fuerza a que sea usuario normal por seguridad

// 3. Validaciones Backend
if (empty($nombre) || empty($correo) || empty($password_plana)) {
    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios"]);
    exit();
}

// 4. Verificar si el correo ya existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Este correo ya está registrado"]);
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// 🔥 5. LA MAGIA DE LA SEGURIDAD: HASHEAR LA CONTRASEÑA 🔥
$pass_hasheada = password_hash($password_plana, PASSWORD_DEFAULT);

// 6. Insertar nuevo usuario (Lo ponemos como activo directamente según el flujo que pides)
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES (?, ?, ?, ?, 'activo')");
$stmt->bind_param("ssss", $nombre, $correo, $pass_hasheada, $rol);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Usuario registrado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar en BD"]);
}

$stmt->close();
$conn->close();
?>