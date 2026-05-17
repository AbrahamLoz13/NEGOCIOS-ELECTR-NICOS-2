<?php
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
require 'db.php';
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // GET - LISTAR PRODUCTOS
    // =========================
    case 'GET':

        $queryStr = "
            SELECT p.*, pr.nombre AS proveedor_nombre
            FROM productos p
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            ORDER BY p.id DESC
        ";

        $result = $conn->query($queryStr);
        $productos = [];

        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        echo json_encode($productos);
        break;


    // =========================
    // POST - CREAR / EDITAR
    // =========================
    case 'POST':

        $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;

        // Campos básicos
        $nombre = $_POST['nombre'] ?? '';
        $precio = floatval($_POST['precio'] ?? 0);
        $categoria = $_POST['categoria'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $stock = intval($_POST['stock'] ?? 0);

        // NUEVOS CAMPOS SCM
        $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
        $estrategia_logistica = $_POST['estrategia_logistica'] ?? 'PULL';
        $proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : null;

        // Imagen
        $ruta_final = $_POST['imagen_actual'] ?? 'img/logo6.png';

        if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {

            $ext = pathinfo($_FILES['imagen_archivo']['name'], PATHINFO_EXTENSION);
            $nombre_unico = uniqid('prod_') . '.' . $ext;

            $directorio_destino = "../img/";
            $archivo_destino = $directorio_destino . $nombre_unico;

            if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $archivo_destino)) {
                $ruta_final = "img/" . $nombre_unico;
            }
        }

        if ($id) {

            // PRIMERO: Obtener el stock actual ANTES de actualizar
            $query_select = "SELECT stock FROM productos WHERE id = ?";
            $stmt_select = $conn->prepare($query_select);
            $stmt_select->bind_param("i", $id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();

            $stock_anterior = null;
            if ($row = $result->fetch_assoc()) {
                $stock_anterior = $row['stock'];
            }

            // SEGUNDO: Realizar el UPDATE
            $stmt = $conn->prepare("
                            UPDATE productos SET 
                                nombre=?, 
                                precio=?, 
                                categoria=?, 
                                descripcion=?, 
                                stock=?, 
                                stock_minimo=?,
                                estrategia_logistica=?,
                                proveedor_id=?,
                                imagen=? 
                            WHERE id=?
                        ");

                        $stmt->bind_param(
                            "sdssiisisi",
                            $nombre,
                            $precio,
                            $categoria,
                            $descripcion,
                            $stock,
                            $stock_minimo,
                            $estrategia_logistica,
                            $proveedor_id,
                            $ruta_final,
                            $id
                        );

                // TERCERO: Si el stock cambió, registrar el movimiento
                if ($stock_anterior !== null && $stock_anterior != $stock) {
                    // Verificar cuántos campos tiene tu tabla movimientos_inventario
                    // Según tu imagen: producto_id, tipo, cantidad, motivo, referencia_id, fecha
                    $stmt_insert = $conn->prepare("
                        INSERT INTO movimientos_inventario 
                        (producto_id, tipo, cantidad, motivo, referencia_id, fecha) 
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    
                    // tipos: i=integer, s=string
                    // producto_id(i), tipo(s), cantidad(i), motivo(s), referencia_id(i)
                    $tipo_movimiento = 'ajuste';
                    $motivo = 'Cambio realizado por administrador';
                    
                    $stmt_insert->bind_param(
                        "isisi", 
                        $id,                    // producto_id (i)
                        $tipo_movimiento,        // tipo (s)
                        $stock,                  // cantidad (i)
                        $motivo,                 // motivo (s)
                        $proveedor_id            // referencia_id (i)
                    );
                    
                    $stmt_insert->execute(); 
                }
                

        } else {

            // INSERT
            $stmt = $conn->prepare("
                INSERT INTO productos 
                (nombre, precio, categoria, descripcion, stock, stock_minimo, estrategia_logistica, proveedor_id, imagen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sdssiisis",
                $nombre,
                $precio,
                $categoria,
                $descripcion,
                $stock,
                $stock_minimo,
                $estrategia_logistica,
                $proveedor_id,
                $ruta_final
            );
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => $stmt->error
            ]);
        }

        break;


    // =========================
    // DELETE
    // =========================
    case 'DELETE':

        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['id'])) {
            echo json_encode(["status" => "error", "message" => "ID requerido"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $input['id']);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }

        break;
}

$conn->close();
?>
