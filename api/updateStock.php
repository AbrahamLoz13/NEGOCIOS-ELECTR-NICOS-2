<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$producto_id = $input['producto_id'] ?? null;
$nuevo_stock = $input['nuevo_stock'] ?? null;
$motivo = $input['motivo'] ?? 'Restock automático PUSH';

if (!$producto_id || !$nuevo_stock) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Obtener información del producto para el pedido
$query_info = "SELECT precio, proveedor_id FROM productos WHERE id = {$producto_id}";
$result_info = mysqli_query($conn, $query_info);
$producto_info = mysqli_fetch_assoc($result_info);

// =============================================
// 1. CREAR PEDIDO EN pedidos_proveedores
// =============================================
$cantidad_pedido = 50; // Cantidad fija de restock
$costo_total = $producto_info['precio'] * $cantidad_pedido;

$insert_pedido = "INSERT INTO pedidos_proveedores 
                (producto_id, proveedor_id, cantidad, costo_total, estado) 
                VALUES (
                    {$producto_id}, 
                    {$producto_info['proveedor_id']}, 
                    {$cantidad_pedido}, 
                    {$costo_total}, 
                    'completado'
                )";

$pedido_creado = false;
$pedido_id = null;

if (mysqli_query($conn, $insert_pedido)) {
    $pedido_id = mysqli_insert_id($conn);
    $pedido_creado = true;
    $pedido_msg = "Pedido #{$pedido_id} creado";
} else {
    $pedido_msg = "Error al crear pedido: " . mysqli_error($conn);
}

// =============================================
// 2. ACTUALIZAR STOCK DEL PRODUCTO
// =============================================
$query = "UPDATE productos SET stock = {$nuevo_stock} WHERE id = {$producto_id}";

if (mysqli_query($conn, $query)) {
    // =============================================
    // 3. REGISTRAR EN movimientos_inventario
    // =============================================
    $fecha_actual = date('Y-m-d H:i:s');
    $log = "INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, referencia_id, fecha) 
            VALUES (
                {$producto_id}, 
                'entrada', 
                {$nuevo_stock}, 
                'Restock automático PUSH completado. {$motivo} - {$pedido_msg}', 
                {$pedido_id}, 
                '{$fecha_actual}'
            )";
    mysqli_query($conn, $log);
    
    // =============================================
    // 4. RESPUESTA FINAL
    // =============================================
    echo json_encode([
        'success' => true, 
        'message' => 'Stock actualizado correctamente',
        'pedido' => [
            'creado' => $pedido_creado,
            'id' => $pedido_id,
            'producto_id' => $producto_id,
            'proveedor_id' => $producto_info['proveedor_id'],
            'cantidad' => $cantidad_pedido,
            'costo_total' => $costo_total,
            'mensaje' => $pedido_msg
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar stock']);
}
?>