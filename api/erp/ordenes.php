<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php'; // ajusta la ruta a tu conexión

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $cliente_id  = $data['cliente_id'];
    $usuario_id  = $data['usuario_id'];
    $productos   = $data['productos']; // array [{producto_id, cantidad, precio}]

    $total = array_sum(array_map(fn($p) => $p['cantidad'] * $p['precio'], $productos));

    $stmt = $conn->prepare("INSERT INTO ordenes (cliente_id, usuario_id, total) VALUES (?,?,?)");
    $stmt->bind_param("iid", $cliente_id, $usuario_id, $total);
    $stmt->execute();
    $orden_id = $conn->insert_id;

    foreach ($productos as $p) {
        $s = $conn->prepare("INSERT INTO orden_detalle (orden_id, producto_id, cantidad, precio_unitario) VALUES (?,?,?,?)");
        $s->bind_param("iiid", $orden_id, $p['producto_id'], $p['cantidad'], $p['precio']);
        $s->execute();
    }

    echo json_encode(["ok" => true, "orden_id" => $orden_id]);

} elseif ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $r = $conn->query("SELECT o.*, u.nombre as cliente FROM ordenes o JOIN usuarios u ON o.cliente_id = u.id WHERE o.id=$id");
        echo json_encode($r->fetch_assoc());
    } else {
        $r = $conn->query("SELECT o.*, u.nombre as cliente FROM ordenes o JOIN usuarios u ON o.cliente_id = u.id ORDER BY o.fecha DESC");
        $rows = [];
        while ($row = $r->fetch_assoc()) $rows[] = $row;
        echo json_encode($rows);
    }

} elseif ($method === 'PUT') {
    $id = intval($_GET['id']);
    $data = json_decode(file_get_contents("php://input"), true);
    $estado = $data['estado'];
    $conn->query("UPDATE ordenes SET estado='$estado' WHERE id=$id");
    echo json_encode(["ok" => true]);
}