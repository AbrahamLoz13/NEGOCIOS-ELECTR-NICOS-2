<?php
require 'db.php';
header('Content-Type: application/json');
// Silenciamos errores visuales para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Seleccionamos solo lo que existe en tu tabla
        $sql = "SELECT id, nombre, correo, telefono, rol, estado FROM usuarios ORDER BY id DESC";
        $result = $conn->query($sql);
        $clients = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) { $clients[] = $row; }
        }
        echo json_encode($clients);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        $nombre = $data['nombre'];
        $correo = $data['correo'];
        $telefono = $data['telefono'] ?? '';
        $calle = $data['calle'] ?? '';
        $numero = $data['numeroCasa'] ?? '';
        $codigoPostal = $data['codigoPostal'] ?? '';
        $rol = !empty($data['rol']) ? $data['rol'] : "usuario";
        $plainPassword = !empty($data['password']) ? $data['password'] : "12345";
$pass = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Si usas hash en login: $pass = password_hash($pass, PASSWORD_DEFAULT);

        // Validar duplicado
        $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "El correo ya existe"]);
            exit;
        }

        // Insert sin dirección
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, telefono, password, rol, estado, calle, numeroCasa, codigoPostal) VALUES (?, ?, ?, ?, ?, 'activo', ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nombre, $correo, $telefono, $pass, $rol, $calle, $numero, $codigoPostal);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Usuario registrado"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'PUT': 
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];

        // LÓGICA DE SUSPENDER (Si solo mandamos estado)
        if (isset($data['estado']) && !isset($data['nombre'])) {
            $nuevo_estado = $data['estado'];
            $stmt = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevo_estado, $id);
        } 
        // LÓGICA DE EDITAR DATOS (Si mandamos nombre)
        else {
            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $telefono = $data['telefono'];
            $rol = $data['rol'];
            
            // Checar si hay cambio de contraseña
            if (!empty($data['password'])) {
                $pass = $data['password']; 
                // $pass = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, rol=?, password=? WHERE id=?");
                $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $rol, $pass, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, rol=? WHERE id=?");
                $stmt->bind_param("ssssi", $nombre, $correo, $telefono, $rol, $id);
            }
        }

        if ($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error", "message" => $stmt->error]);
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