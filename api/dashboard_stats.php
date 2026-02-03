<?php
require 'db.php';
header('Content-Type: application/json');

try {
    // Contar usuarios
    $resUsers = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'");
    $users = $resUsers->fetch_assoc()['total'] ?? 0;

    // Contar productos
    $resProds = $conn->query("SELECT COUNT(*) as total FROM productos");
    $prods = $resProds->fetch_assoc()['total'] ?? 0;

    // Contar pedidos pendientes
    $resOrders = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'");
    $orders = $resOrders->fetch_assoc()['total'] ?? 0;

    echo json_encode([
        "usuarios" => $users,
        "productos" => $prods,
        "pedidos" => $orders
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>