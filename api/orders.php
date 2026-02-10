<?php
require 'db.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM pedidos ORDER BY id DESC";
        $result = $conn->query($sql);
        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) { $pedidos[] = $row; }
        }
        echo json_encode($pedidos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO pedidos (cliente, producto, cantidad, total, fecha, estado, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidsss", $data['cliente'], $data['producto'], $data['cantidad'], $data['total'], $data['fecha'], $data['estado'], $data['direccion']);
        
        if ($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error", "message" => $stmt->error]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE pedidos SET cliente=?, producto=?, cantidad=?, total=?, estado=?, direccion=? WHERE id=?");
        $stmt->bind_param("ssidssi", $data['cliente'], $data['producto'], $data['cantidad'], $data['total'], $data['estado'], $data['direccion'], $data['id']);
        
        if ($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error", "message" => $stmt->error]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        
        if ($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error", "message" => $stmt->error]);
        break;
}
$conn->close();
?>