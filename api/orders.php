<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'GET') {
    $sql = "SELECT o.id, o.producto_id, o.proveedor_id, o.cantidad, o.costo_total, o.fecha_pedido, o.estado,
                    p.nombre as producto, pr.nombre as proveedor 
             FROM pedidos_proveedores o 
             LEFT JOIN productos p ON o.producto_id = p.id 
             LEFT JOIN proveedores pr ON o.proveedor_id = pr.id 
             ORDER BY o.fecha_pedido DESC";
                
    $r = $conn->query($sql);
    $rows = [];
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);

} elseif ($method === 'POST') {
    $producto_id = $data['producto_id'];
    $proveedor_id = $data['proveedor_id'];
    $cantidad = $data['cantidad'];
    $costo_total = $data['costo_total'];
    $estado = $data['estado'] ?? 'pendiente';

    $stmt = $conn->prepare("INSERT INTO pedidos_proveedores (producto_id, proveedor_id, cantidad, costo_total, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiids", $producto_id, $proveedor_id, $cantidad, $costo_total, $estado);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    $stmt->close();

} elseif ($method === 'PUT') {
    $id = $data['id'];
    $producto_id = $data['producto_id'];
    $proveedor_id = $data['proveedor_id'];
    $cantidad = $data['cantidad'];
    $costo_total = $data['costo_total'];
    $estado = $data['estado'];

    // Obtenemos el estado actual para validar si se requiere actualizar el inventario
    $r = $conn->query("SELECT estado FROM pedidos_proveedores WHERE id=$id");
    $current = $r->fetch_assoc();

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE pedidos_proveedores SET producto_id=?, proveedor_id=?, cantidad=?, costo_total=?, estado=? WHERE id=?");
        $stmt->bind_param("iiidsi", $producto_id, $proveedor_id, $cantidad, $costo_total, $estado, $id);
        $stmt->execute();

        // Validar que el pedido cambie a completado para sumar el stock y registrar el movimiento
        if ($current['estado'] !== 'completado' && $estado === 'completado') {
            
            // 1. Sumamos el stock
            $conn->query("UPDATE productos SET stock = stock + $cantidad WHERE id=$producto_id");
            
            // 2. EL BUG ESTABA AQUÍ: Registramos el movimiento AÑADIENDO NOW() para la fecha
            $conn->query("INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, referencia_id, fecha) VALUES ($producto_id, 'entrada', $cantidad, 'Reabastecimiento de Proveedor', $id, NOW())");
        }

        $conn->commit();
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} elseif ($method === 'DELETE') {
    $id = $data['id'];
    if ($conn->query("DELETE FROM pedidos_proveedores WHERE id=$id")) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>