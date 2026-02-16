<?php
require 'db.php';
header('Content-Type: application/json');

$user_nombre = $_GET['nombre'] ?? '';

if (!$user_nombre) {
    echo json_encode(["status" => "error"]);
    exit;
}

/* ================= PEDIDOS ================= */
$pedidos = [];

$stmt = $conn->prepare("
    SELECT id, producto_nombre, cantidad, total, fecha, estado 
    FROM pedidos 
    WHERE cliente_nombre = ?
    ORDER BY fecha DESC
");

$stmt->bind_param("s", $user_nombre);
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
    WHERE user_id = (
        SELECT id FROM usuarios WHERE nombre = ?
    )
    ORDER BY fecha DESC
");

$stmt2->bind_param("s", $user_nombre);
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
