<?php
// api/auth_v2.php
// ESTE ES UN ARCHIVO NUEVO, NO REEMPLAZA A LOS ANTERIORES
require 'db.php';
header('Content-Type: application/json');
error_reporting(0);

$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? '';

// --- 1. LOGIN (Compatible con usuarios viejos y nuevos) ---
if ($action === 'login') {
    $correo = $input['correo'];
    $pass = $input['password'];

    $stmt = $conn->prepare("SELECT id, nombre, password, rol, estado FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        // Bloqueo solo si está suspendido o pendiente de verificar
        if ($user['estado'] === 'suspendido') {
            echo json_encode(["status" => "error", "message" => "Cuenta suspendida."]);
            exit;
        }
        
        // Si intenta entrar alguien que se registró con el sistema nuevo pero no verificó
        if ($user['estado'] === 'pendiente') {
            $newCode = rand(100000, 999999);
            $conn->query("UPDATE usuarios SET codigo_verificacion = '$newCode' WHERE id = " . $user['id']);
            // Simulación de envío
            @mail($correo, "Codigo Pet Palace", "Tu codigo: $newCode");
            
            echo json_encode([
                "status" => "verify_required",
                "message" => "Falta verificar tu cuenta.",
                "debug_code" => $newCode
            ]);
            exit;
        }

        // Validación de contraseña (Soporta Hash y Texto Plano para compatibilidad)
        if (password_verify($pass, $user['password']) || $pass === $user['password']) {
            echo json_encode(["status" => "success", "data" => $user]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Correo no registrado."]);
    }
}

// --- 2. REGISTRO NUEVO (Con verificación) ---
elseif ($action === 'register') {
    $nombre = $input['nombre'];
    $correo = $input['correo'];
    $pass = $input['password']; // En prod usa password_hash($pass, PASSWORD_DEFAULT)

    // Validar si existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Este correo ya está registrado."]);
        exit;
    }

    $codigo = rand(100000, 999999);

    // Insertamos como 'pendiente' para obligar a verificar
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol, estado, codigo_verificacion) VALUES (?, ?, ?, 'usuario', 'pendiente', ?)");
    $stmt->bind_param("sssss", $nombre, $correo, $pass, $codigo);

    if ($stmt->execute()) {
        // Enviar correo (usando mail nativo o librería)
        @mail($correo, "Verifica tu cuenta", "Codigo: $codigo");

        echo json_encode([
            "status" => "success_verify",
            "message" => "Verifica tu correo.",
            "debug_code" => $codigo // Para que lo veas en el alert
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error SQL."]);
    }
}

// --- 3. VERIFICAR CÓDIGO ---
elseif ($action === 'verify_code') {
    $correo = $input['correo'];
    $codigo = $input['codigo'];

    $stmt = $conn->prepare("SELECT id, nombre, rol, codigo_verificacion FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        if ($user['codigo_verificacion'] == $codigo) {
            // Activamos la cuenta y limpiamos el código
            $conn->query("UPDATE usuarios SET estado = 'activo', codigo_verificacion = NULL WHERE id = " . $user['id']);
            
            echo json_encode([
                "status" => "success",
                "data" => [
                    "id" => $user['id'],
                    "nombre" => $user['nombre'],
                    "rol" => $user['rol'],
                    "correo" => $correo
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Código incorrecto."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
    }
}
$conn->close();
?>