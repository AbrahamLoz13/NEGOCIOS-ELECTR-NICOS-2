<?php
require 'db.php';
header('Content-Type: application/json');

$user_nombre = $_GET['nombre'] ?? '';

if (!$user_nombre) {
    echo json_encode(["status" => "error", "message" => "Nombre requerido"]);
    exit;
}

/* ================= OBTENER USUARIO ================= */

$stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ?");
$stmtUser->bind_param("s", $user_nombre);
$stmtUser->execute();
$resUser = $stmtUser->get_result();

if ($resUser->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

$user = $resUser->fetch_assoc();
$user_id = $user['id'];

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

$conn->close();
?>
