<?php
// api/products.php
require 'db.php';
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $queryStr = "SELECT * FROM productos WHERE 1=1 ORDER BY id DESC";
        $result = $conn->query($queryStr);
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        echo json_encode($productos);
        break;

    case 'POST':
        // ACEPTAR TANTO CREACIÓN COMO EDICIÓN AQUÍ PORQUE "PUT" NO SOPORTA ARCHIVOS FÁCILMENTE
        
        // 1. Recoger datos normales
        $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $categoria = $_POST['categoria'];
        $descripcion = $_POST['descripcion'];
        $stock = $_POST['stock'];
        
        // 2. Manejo de la Imagen
        $ruta_final = $_POST['imagen_actual'] ?? 'img/logo6.png'; // Por defecto la que ya tenía o el logo

        // Si se subió un archivo nuevo y no hay errores
        if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
            
            // Nombre único para evitar sobrescribir
            $ext = pathinfo($_FILES['imagen_archivo']['name'], PATHINFO_EXTENSION);
            $nombre_unico = uniqid('prod_') . '.' . $ext;
            
            // Ruta donde se guardará físicamente (relativo a este archivo php en 'api/')
            // Subimos un nivel (..) y entramos a 'img/'
            $directorio_destino = "../img/";
            $archivo_destino = $directorio_destino . $nombre_unico;

            // Mover archivo
            if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $archivo_destino)) {
                // Ruta para la base de datos (relativa al index.html)
                $ruta_final = "img/" . $nombre_unico;
            }
        }

        // 3. Insertar o Actualizar
        if ($id) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE productos SET nombre=?, precio=?, categoria=?, descripcion=?, stock=?, imagen=? WHERE id=?");
            $stmt->bind_param("sdssisi", $nombre, $precio, $categoria, $descripcion, $stock, $ruta_final, $id);
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, categoria, descripcion, stock, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssis", $nombre, $precio, $categoria, $descripcion, $stock, $ruta_final);
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        break;

    case 'DELETE':
        $input = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $input['id']);
        if($stmt->execute()) echo json_encode(["status" => "success"]);
        else echo json_encode(["status" => "error"]);
        break;
}
$conn->close();
?>