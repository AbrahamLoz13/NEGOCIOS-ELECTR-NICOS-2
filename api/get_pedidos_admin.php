<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, PUT");

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Trae todas las compras de la tienda con el nombre del cliente y producto
    $sql = "SELECT 
                p.id AS pedido_id, 
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
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.fecha DESC";
    
    $res = $conn->query($sql);
    $pedidos = [];
    while ($row = $res->fetch_assoc()) {
        $pedidos[] = $row;
    }
    echo json_encode($pedidos);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Actualiza el estado para que el cliente lo vea en su perfil
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