<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado. Solo el sistema puede hacer peticiones aquí."]));
}
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, PUT");

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    $sql = "SELECT 
                p.id AS pedido_id, 
                p.usuario_id,
                p.total, 
                p.fecha, 
                p.estado, 
                pr.nombre AS producto_nombre, 
                dp.cantidad, 
                dp.subtotal,
                u.nombre AS cliente_nombre
            FROM pedidos p
            JOIN detalle_pedido dp ON p.id = dp.pedido_id
            JOIN productos pr ON dp.producto_id = pr.id
            JOIN usuarios u ON p.usuario_id = u.id";
            
    if ($user_id > 0) {
        $sql .= " WHERE p.usuario_id = $user_id";
    }
    
    $sql .= " ORDER BY p.fecha DESC";
    
    $res = $conn->query($sql);
    $pedidos = [];
    while ($row = $res->fetch_assoc()) {
        $pedidos[] = $row;
    }
    echo json_encode($pedidos);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = intval($_GET['id']);
    $data = json_decode(file_get_contents("php://input"), true);
    $estado = $conn->real_escape_string($data['estado']);
    
    $stmt = $conn->prepare("UPDATE pedidos SET estado=? WHERE id=?");
    $stmt->bind_param("si", $estado, $id);
    if($stmt->execute()){
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>