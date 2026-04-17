<?php
require 'db.php';
header('Content-Type: application/json');
error_reporting(0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';

try {
    if ($action === 'login') {
        $correo = $input['correo'];
        $pass = $input['password'];

        $stmt = $conn->prepare("SELECT id, nombre, correo, telefono, password, rol, estado, calle, colonia, ciudad, numeroCasa, codigoPostal FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if ($user['estado'] === 'suspendido') {
                echo json_encode(["status" => "error", "message" => "Cuenta suspendida."]);
                exit;
            }
            
            if ($user['estado'] === 'pendiente') {
                $newCode = rand(100000, 999999);
                $conn->query("UPDATE usuarios SET codigo_verificacion = '$newCode' WHERE id = " . $user['id']);
                @mail($correo, "Codigo Pet Palace", "Tu codigo: $newCode");
                
                echo json_encode([
                    "status" => "verify_required",
                    "message" => "Falta verificar tu cuenta.",
                    "debug_code" => $newCode
                ]);
                exit;
            }

            if (password_verify($pass, $user['password']) || $pass === $user['password']) {
                unset($user['password']); 
                echo json_encode(["status" => "success", "data" => $user]);
            } else {
                echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Correo no registrado."]);
        }
    }

    elseif ($action === 'register') {
        $nombre = $input['nombre'];
        $correo = $input['correo'];
        $pass = password_hash($input['password'], PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $check->bind_param("s", $correo);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Este correo ya está registrado."]);
            exit;
        }

        $codigo = rand(100000, 999999);

        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, estado, codigo_verificacion) VALUES (?, ?, ?, 'usuario', 'pendiente', ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $pass, $codigo);

        if ($stmt->execute()) {
            @mail($correo, "Verifica tu cuenta", "Codigo: $codigo");

            echo json_encode([
                "status" => "success_verify",
                "message" => "Verifica tu correo.",
                "debug_code" => $codigo 
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error SQL."]);
        }
    }

    elseif ($action === 'verify_code') {
        $correo = $input['correo'];
        $codigo = $input['codigo'];

        $stmt = $conn->prepare("SELECT id, nombre, correo, telefono, rol, estado, calle, colonia, ciudad, numeroCasa, codigoPostal, codigo_verificacion FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if ($user['codigo_verificacion'] == $codigo) {
                $conn->query("UPDATE usuarios SET estado = 'activo', codigo_verificacion = NULL WHERE id = " . $user['id']);
                unset($user['codigo_verificacion']); 
                echo json_encode([ "status" => "success", "data" => $user ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Código incorrecto."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Fallo interno en BD: " . $e->getMessage()]);
}
$conn->close();
?>