<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
// api/db.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pet_palace";

// ESTO ES CLAVE: Desactiva que PHP imprima errores en pantalla
// para que no rompa el formato JSON que espera JavaScript.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Si falla, devolvemos un JSON de error, NO texto plano.
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Error de conexión: " . $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8");
?>