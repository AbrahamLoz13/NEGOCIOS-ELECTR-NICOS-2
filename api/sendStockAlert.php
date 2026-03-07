<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);
$producto_id = $input['producto_id'] ?? null;

if (!$producto_id) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
    exit;
}

// Obtener información del producto
$query = "
    SELECT 
        p.id,
        p.nombre,
        p.stock,
        p.stock_minimo,
        p.estrategia_logistica,
        pr.id as proveedor_id,
        pr.nombre as proveedor_nombre,
        pr.correo as proveedor_correo
    FROM productos p
    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
    WHERE p.id = {$producto_id}
";

$result = mysqli_query($conn, $query);
$producto = mysqli_fetch_assoc($result);

if (!$producto) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}

// Verificar que sea PUSH
if ($producto['estrategia_logistica'] != 'PUSH') {
    echo json_encode(['success' => false, 'message' => 'Producto no es de estrategia PUSH']);
    exit;
}

// Verificar proveedor
if (empty($producto['proveedor_correo'])) {
    // Registrar en notificaciones el fallo por falta de correo
    echo json_encode(['success' => false, 'message' => 'Proveedor sin correo asignado']);
    exit;
}

$botToken = "8559404476:AAHPavE_3vyTTUlpGRj-UHhCxlc2673h4jQ";
$chatId = "6428526550";

$mensaje = "🚨 PEDIDO AUTOMÁTICO PUSH 🚨\n\n";
$mensaje .= "Producto: {$producto['nombre']}\n";
$mensaje .= "Stock actual: {$producto['stock']}\n";
$mensaje .= "Stock mínimo: {$producto['stock_minimo']}";
$mensaje .= "Stock requerido: 50";
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$data = [
    'chat_id' => $chatId,
    'text' => $mensaje
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type:application/x-www-form-urlencoded",
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>