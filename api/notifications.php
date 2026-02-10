<?php
require 'db.php';
header('Content-Type: application/json');

// Permitir acceso CORS completo
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // 1. GUARDAR NUEVA NOTIFICACIÓN / MENSAJE
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['user_id']) || !isset($data['message'])) {
            echo json_encode(["status" => "error", "message" => "Faltan datos"]);
            exit;
        }

        $user_id = $data['user_id'];
        // Si no viene sender_id, asumimos 0 (Sistema)
        $sender_id = isset($data['sender_id']) ? $data['sender_id'] : 0;
        $mensaje = $data['message'];
        $tipo = isset($data['type']) ? $data['type'] : 'mensaje';
        
        // CRÍTICO: Saber quién escribe ('admin' o 'cliente')
        // Si no se especifica, asumimos 'admin' por defecto
        $origen = isset($data['origen']) ? $data['origen'] : 'admin'; 
        
        $stmt = $conn->prepare("INSERT INTO notificaciones (user_id, sender_id, mensaje, tipo, origen, estado, fecha) VALUES (?, ?, ?, ?, ?, 'enviado', NOW())");
        $stmt->bind_param("iisss", $user_id, $sender_id, $mensaje, $tipo, $origen);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Mensaje enviado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error DB: " . $stmt->error]);
        }
        $stmt->close();
        break;

    // 2. OBTENER MENSAJES Y CONTEOS
    case 'GET':
        // A) Obtener conteo de mensajes NO LEÍDOS por cliente (Para el panel de Vendedor)
        if (isset($_GET['get_counts'])) {
            // Cuenta solo los mensajes que vienen del 'cliente' y no han sido leídos
            $sql = "SELECT user_id, COUNT(*) as total FROM notificaciones WHERE origen = 'cliente' AND estado != 'leido' GROUP BY user_id";
            $result = $conn->query($sql);
            $counts = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $counts[$row['user_id']] = $row['total'];
                }
            }
            echo json_encode($counts);
        }
        
        // B) Obtener historial de chat de un usuario específico
        elseif (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            
            // Traemos el nombre del remitente haciendo JOIN con usuarios
            // Ordenamos por fecha ASC (antiguo a nuevo) para que parezca chat
            // O DESC si prefieres lista de notificaciones
            $sql = "SELECT n.*, 
                           COALESCE(u.nombre, 'Sistema') as remitente, 
                           COALESCE(u.rol, 'Admin') as rol 
                    FROM notificaciones n
                    LEFT JOIN usuarios u ON n.sender_id = u.id
                    WHERE n.user_id = ? 
                    ORDER BY n.fecha ASC"; // ASC para chat (historial), DESC para lista
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notificaciones = [];
            while ($row = $result->fetch_assoc()) {
                $notificaciones[] = $row;
            }
            echo json_encode($notificaciones);
        } else {
            echo json_encode([]);
        }
        break;

    // 3. ACTUALIZAR ESTADO (Marcar como LEÍDO)
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        // A) Marcar TODO como leído de un cliente específico (Cuando el Vendedor abre el chat)
        if (isset($data['mark_all_read_for_client'])) {
            $client_id = $data['mark_all_read_for_client'];
            // Solo marcamos como leídos los que envió el CLIENTE
            $sql = "UPDATE notificaciones SET estado = 'leido' WHERE user_id = ? AND origen = 'cliente'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $client_id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Chat marcado como leído"]);
            } else {
                echo json_encode(["status" => "error"]);
            }
        }
        // B) Marcar un mensaje individual por ID
        elseif (isset($data['id']) && isset($data['estado'])) {
            $id = $data['id'];
            $estado = $data['estado'];
            $stmt = $conn->prepare("UPDATE notificaciones SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $estado, $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Faltan datos"]);
        }
        break;

    // 4. ELIMINAR MENSAJE
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            echo json_encode(["status" => "error", "message" => "Falta ID"]);
            exit;
        }
        $id = $data['id'];
        $stmt = $conn->prepare("DELETE FROM notificaciones WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Eliminado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
        break;
}

$conn->close();
?>