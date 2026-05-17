<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
require 'db.php';
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // RECIBIMOS EL ID EXACTO DEL USUARIO
    $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($user_id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID de usuario inválido o no recibido."]);
        exit;
    }

    /* ================= PEDIDOS ================= */
    $pedidos = [];
    $stmt = $conn->prepare("
        SELECT 
            p.id AS pedido_id,
            p.total,
            p.fecha,
            p.estado,
            pr.nombre AS producto,
            dp.cantidad,
            dp.subtotal
        FROM pedidos p
        JOIN detalle_pedido dp ON p.id = dp.pedido_id
        JOIN productos pr ON dp.producto_id = pr.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }

    /* ================= CHATS ================= */
    $chats = [];
    $stmt2 = $conn->prepare("
        SELECT mensaje, fecha, estado, origen
        FROM notificaciones
        WHERE user_id = ?
        ORDER BY fecha DESC
    ");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $chats[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "pedidos" => $pedidos,
        "chats" => $chats
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $e->getMessage()]);
}

$conn->close();
?>