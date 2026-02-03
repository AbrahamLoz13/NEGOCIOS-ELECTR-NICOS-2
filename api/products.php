<?php
// api/products.php
require 'db.php';
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $queryStr = "SELECT * FROM productos WHERE 1=1";
        $params = [];
        $types = "";

        // Si hay búsqueda por texto
        if (!empty($_GET['q'])) {
            $queryStr .= " AND nombre LIKE ?";
            $params[] = "%" . $_GET['q'] . "%";
            $types .= "s";
        }

        // Si hay búsqueda por categoría
        if (!empty($_GET['categoria']) && $_GET['categoria'] !== "0") {
            $queryStr .= " AND categoria = ?";
            $params[] = $_GET['categoria'];
            $types .= "s";
        }

        $stmt = $conn->prepare($queryStr);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        echo json_encode($productos);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $sql = "INSERT INTO productos (nombre, precio, categoria, descripcion, stock, imagen) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $img = !empty($input['imagen']) ? $input['imagen'] : 'img/logo6.png';
        $stmt->bind_param("sdssis", $input['nombre'], $input['precio'], $input['categoria'], $input['descripcion'], $input['stock'], $img);
        echo json_encode($stmt->execute() ? ["status" => "success"] : ["status" => "error", "message" => $conn->error]);
        break;

    case 'PUT':
        $input = json_decode(file_get_contents("php://input"), true);
        $sql = "UPDATE productos SET nombre=?, precio=?, categoria=?, descripcion=?, stock=?, imagen=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssisi", $input['nombre'], $input['precio'], $input['categoria'], $input['descripcion'], $input['stock'], $input['imagen'], $input['id']);
        echo json_encode($stmt->execute() ? ["status" => "success"] : ["status" => "error", "message" => $conn->error]);
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $input['id']);
        if($stmt->execute()) echo json_encode(["status" => "success"]);
        break;
}
$conn->close();
?>