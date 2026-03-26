<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$orden_id = intval($data['orden_id']);

// Obtener productos de la orden
$detalles = $conn->query("SELECT * FROM orden_detalle WHERE orden_id=$orden_id");

$conn->begin_transaction();
try {
    while ($item = $detalles->fetch_assoc()) {
        $pid = $item['producto_id'];
        $qty = $item['cantidad'];

        // 1. Descontar stock
        $conn->query("UPDATE productos SET stock = stock - $qty WHERE id=$pid AND stock >= $qty");
        if ($conn->affected_rows === 0) throw new Exception("Stock insuficiente para producto $pid");

        // 2. Registrar movimiento en inventario
        $conn->query("INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, referencia_id)
                      VALUES ($pid, 'salida', $qty, 'Orden ERP #$orden_id', $orden_id)");
    }

    // 3. Cambiar estado de la orden
    $conn->query("UPDATE ordenes SET estado='procesada' WHERE id=$orden_id");

    $conn->commit();
    echo json_encode(["ok" => true, "mensaje" => "Orden procesada correctamente"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}