<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // LISTAR
    // =========================
    case 'GET':

        $result = $conn->query("
            SELECT id, nombre, contacto, telefono, correo
            FROM proveedores
            ORDER BY id DESC
        ");

        $proveedores = [];

        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }

        echo json_encode($proveedores);
        break;


    // =========================
    // CREAR
    // =========================
    case 'POST':

        $nombre   = trim($_POST['nombre'] ?? '');
        $contacto = trim($_POST['contacto'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');

        if ($nombre === '') {
            echo json_encode(["status" => "error", "message" => "Nombre obligatorio"]);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO proveedores (nombre, contacto, telefono, correo)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param("ssss", $nombre, $contacto, $telefono, $correo);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        break;


    // =========================
    // EDITAR (PUT)
    // =========================
    case 'PUT':

        parse_str(file_get_contents("php://input"), $data);

        $id       = intval($data['id'] ?? 0);
        $nombre   = trim($data['nombre'] ?? '');
        $contacto = trim($data['contacto'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $correo   = trim($data['correo'] ?? '');

        if ($id <= 0) {
            echo json_encode(["status" => "error", "message" => "ID inválido"]);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE proveedores
            SET nombre=?, contacto=?, telefono=?, correo=?
            WHERE id=?
        ");

        $stmt->bind_param("ssssi", $nombre, $contacto, $telefono, $correo, $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        break;


    // =========================
    // ELIMINAR
    // =========================
    case 'DELETE':

        $input = json_decode(file_get_contents("php://input"), true);

        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(["status" => "error", "message" => "ID inválido"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM proveedores WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        break;

    default:
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}

$conn->close();
?>
