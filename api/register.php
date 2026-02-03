<?php
// api/register.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 1. Conexión
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pet_palace";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Error de conexión a BD: " . $conn->connect_error]);
    exit();
}

// 2. Recibir datos JSON
$input = json_decode(file_get_contents("php://input"), true);
$nombre   = $input['nombre'] ?? '';
$correo   = $input['correo'] ?? '';
$password = $input['password'] ?? '';
$rol      = 'usuario'; // Por defecto, todos los que se registran son usuarios normales

// 3. Validaciones
if (empty($nombre) || empty($correo) || empty($password)) {
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
    exit();
}
$check->close();

// 5. Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre, $correo, $password, $rol);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Usuario registrado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar en BD"]);
}

$stmt->close();
$conn->close();
?>