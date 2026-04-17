<?php
require 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $sql = "SELECT id, nombre, correo, telefono, rol, estado, calle, colonia, ciudad, numeroCasa, codigoPostal FROM usuarios ORDER BY id DESC";
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
            $colonia = $data['colonia'] ?? '';
            $ciudad = $data['ciudad'] ?? '';
            $numero = $data['numeroCasa'] ?? 'S/N';
            $codigoPostal = !empty($data['codigoPostal']) ? intval($data['codigoPostal']) : 0;
            $rol = !empty($data['rol']) ? $data['rol'] : "usuario";
            $pass = password_hash(!empty($data['password']) ? $data['password'] : "12345", PASSWORD_DEFAULT);

            $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $check->bind_param("s", $correo);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "El correo ya existe"]);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, telefono, password, rol, estado, calle, colonia, ciudad, numeroCasa, codigoPostal) VALUES (?, ?, ?, ?, ?, 'activo', ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssi", $nombre, $correo, $telefono, $pass, $rol, $calle, $colonia, $ciudad, $numero, $codigoPostal);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Usuario registrado"]);
            }
            break;

        case 'PUT': 
            $data = json_decode(file_get_contents("php://input"), true);
            
            if(!isset($data['id'])) {
                 echo json_encode(["status" => "error", "message" => "ID de usuario faltante"]);
                 exit;
            }
            $id = intval($data['id']);

            if (isset($data['estado']) && !isset($data['nombre'])) {
                $stmt = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
                $stmt->bind_param("si", $data['estado'], $id);
            } 
            else {
                $nombre = $data['nombre'] ?? '';
                $correo = $data['correo'] ?? '';
                $telefono = $data['telefono'] ?? '';
                $rol = $data['rol'] ?? 'usuario';
                
                $calle = $data['calle'] ?? '';
                $colonia = $data['colonia'] ?? '';
                $ciudad = $data['ciudad'] ?? '';
                $numeroCasa = $data['numeroCasa'] ?? 'S/N';
                $codigoPostal = !empty($data['codigoPostal']) ? intval($data['codigoPostal']) : 0;

                if (!empty($data['password'])) {
                    $pass = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, rol=?, password=?, calle=?, colonia=?, ciudad=?, numeroCasa=?, codigoPostal=? WHERE id=?");
                    $stmt->bind_param("ssssssssssi", $nombre, $correo, $telefono, $rol, $pass, $calle, $colonia, $ciudad, $numeroCasa, $codigoPostal, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=?, rol=?, calle=?, colonia=?, ciudad=?, numeroCasa=?, codigoPostal=? WHERE id=?");
                    $stmt->bind_param("sssssssssi", $nombre, $correo, $telefono, $rol, $calle, $colonia, $ciudad, $numeroCasa, $codigoPostal, $id);
                }
            }

            if ($stmt->execute()) echo json_encode(["status" => "success"]);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", intval($data['id']));
            if ($stmt->execute()) echo json_encode(["status" => "success"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Fallo interno en BD: " . $e->getMessage()]);
}
$conn->close();
?>