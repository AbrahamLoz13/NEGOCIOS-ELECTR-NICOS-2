<?php
require 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Ahora traemos también el 'estado'
        $sql = "SELECT id, nombre, correo, telefono, estado FROM usuarios WHERE rol != 'admin' ORDER BY id DESC";
        $result = $conn->query($sql);
        $clients = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) { $clients[] = $row; }
        }
        echo json_encode($clients);
        break;

    case 'POST':
        // (Tu código de guardar cliente, sin cambios)
        $data = json_decode(file_get_contents("php://input"), true);
        $nombre = $data['nombre'];
        $correo = $data['correo'];
        $telefono = $data['telefono'];
        $pass = "12345"; 
        $rol = "usuario";
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, telefono, password, rol, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
        $stmt->bind_param("sssss", $nombre, $correo, $telefono, $pass, $rol);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Cliente registrado"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'PUT': 
        // NUEVO: CAMBIAR ESTADO (SUSPENDER / ACTIVAR)
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $nuevo_estado = $data['estado']; // 'activo' o 'suspendido'

        $stmt = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error"]);
        break;
}
$conn->close();
?>