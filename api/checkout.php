<?php
// api/checkout.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || empty($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Carrito vacío."]);
    exit;
}

$conn->begin_transaction();

try {
    $usuario_id = $data['user_id'];
    $total = $data['total'];
    // Nota: Agregamos el campo 'estado' y 'usuario_id' según tu captura
    
    // 1. INSERTAR EN TABLA 'pedidos'
    $stmt_ped = $conn->prepare("INSERT INTO pedidos (total, fecha, estado, usuario_id) VALUES (?, NOW(), 'pendiente', ?)");
    $stmt_ped->bind_param("di", $total, $usuario_id);
    $stmt_ped->execute();
    
    $pedido_id = $conn->insert_id; // Obtenemos el ID del pedido recién creado

    // 2. PREPARAR QUERIES PARA DETALLE Y STOCK
    // Tabla 'detalle_pedido': id, pedido_id, producto_id, cantidad, precio_unitario, subtotal
    $stmt_det = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    // Tabla 'productos': para restar stock
    $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");
    
    // Tabla 'movimientos_inventario': id, producto_id, tipo, cantidad, motivo, referencia_id, fecha
    $stmt_mov = $conn->prepare("INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, referencia_id, fecha) VALUES (?, 'salida', ?, 'Venta Online', ?, NOW())");

    foreach ($data['cart'] as $item) {
        $p_id = $item['id'];
        $qty = $item['cantidad'];
        $price = $item['precio'];
        $subtotal = $price * $qty;

        // A. Insertar Detalle
        $stmt_det->bind_param("iiidd", $pedido_id, $p_id, $qty, $price, $subtotal);
        $stmt_det->execute();

        // B. Restar Stock
        $stmt_stock->bind_param("iii", $qty, $p_id, $qty);
        $stmt_stock->execute();
        if ($stmt_stock->affected_rows === 0) {
            throw new Exception("No hay suficiente stock para el producto ID: " . $p_id);
        }

        // C. Registrar Movimiento de Inventario
        // Usamos pedido_id como referencia_id para rastrear la venta
        $stmt_mov->bind_param("iii", $p_id, $qty, $pedido_id);
        $stmt_mov->execute();
    }

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Compra procesada. Pedido #" . $pedido_id]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();