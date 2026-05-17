<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}

require 'db.php';
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$correo = trim($data['correo'] ?? '');
$pass   = $data['password'] ?? '';

if (empty($correo) || empty($pass)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos por llenar."]);
    exit;
}

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

    // 🔥 VERIFICACIÓN ESTRICTA DE LA CONTRASEÑA ENCRIPTADA 🔥
    // Se elimina el `|| $pass === $user['password']`. Si no está hasheada, no entra.
    if (password_verify($pass, $user['password'])) {
        
        // Borramos el hash del arreglo para que nunca viaje al navegador del usuario
        unset($user['password']);
        
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