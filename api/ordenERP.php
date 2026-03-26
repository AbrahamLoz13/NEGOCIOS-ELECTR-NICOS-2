<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // LISTAR MOVIMIENTOS
    // =========================
    case 'GET':
        
        // Si viene un ID específico
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "
                SELECT 
                    m.*,
                    p.nombre AS producto_nombre,
                    p.precio,
                    pr.nombre AS proveedor_nombre
                FROM movimientos_inventario m
                LEFT JOIN productos p ON m.producto_id = p.id
                LEFT JOIN proveedores pr ON m.referencia_id = pr.id
                WHERE m.id = {$id}
                ORDER BY m.fecha DESC
            ";
        } else {
            // Listar todos los movimientos
            $sql = "
                SELECT 
                    m.id,
                    m.producto_id,
                    m.tipo,
                    m.cantidad,
                    m.motivo,
                    m.referencia_id,
                    m.fecha,
                    p.nombre AS producto_nombre,
                    p.precio,
                    pr.nombre AS proveedor_nombre
                FROM movimientos_inventario m
                LEFT JOIN productos p ON m.producto_id = p.id
                LEFT JOIN proveedores pr ON m.referencia_id = pr.id
                ORDER BY m.fecha DESC
            ";
        }

        $result = $conn->query($sql);
        
        if (!$result) {
            echo json_encode(["status" => "error", "message" => "Error en la consulta: " . $conn->error]);
            exit;
        }

        $movimientos = [];

        while ($row = $result->fetch_assoc()) {
            $movimientos[] = $row;
        }

        echo json_encode($movimientos);
        break;


    // =========================
    // CREAR MOVIMIENTO (SOLO PARA PRUEBAS/ADMIN)
    // =========================
    case 'POST':

        // Verificar si es admin (opcional, puedes comentar si no usas autenticación)
        /*
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(["status" => "error", "message" => "No autorizado"]);
            exit;
        }
        */

        $data = json_decode(file_get_contents("php://input"), true);

        $producto_id = intval($data['producto_id'] ?? 0);
        $tipo = trim($data['tipo'] ?? '');
        $cantidad = intval($data['cantidad'] ?? 0);
        $motivo = trim($data['motivo'] ?? '');
        $referencia_id = isset($data['referencia_id']) ? intval($data['referencia_id']) : null;

        // Validaciones básicas
        if ($producto_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Producto ID inválido"]);
            exit;
        }

        if ($tipo === '') {
            echo json_encode(["status" => "error", "message" => "Tipo de movimiento obligatorio"]);
            exit;
        }

        // Verificar que el producto existe
        $check = $conn->query("SELECT id FROM productos WHERE id = {$producto_id}");
        if ($check->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
            exit;
        }

        // Insertar movimiento
        $stmt = $conn->prepare("
            INSERT INTO movimientos_inventario 
            (producto_id, tipo, cantidad, motivo, referencia_id, fecha) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param("isisi", $producto_id, $tipo, $cantidad, $motivo, $referencia_id);

        if ($stmt->execute()) {
            $movimiento_id = $stmt->insert_id;
            
            echo json_encode([
                "status" => "success",
                "message" => "Movimiento registrado",
                "id" => $movimiento_id
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Error al registrar: " . $stmt->error
            ]);
        }

        break;


    // =========================
    // ACTUALIZAR MOVIMIENTO (NO PERMITIDO - SOLO VER)
    // =========================
    case 'PUT':
        echo json_encode([
            "status" => "error", 
            "message" => "No se permite editar movimientos de inventario"
        ]);
        break;


    // =========================
    // ELIMINAR MOVIMIENTO (SOLO ADMIN, OPCIONAL)
    // =========================
    case 'DELETE':

        // Verificar si es admin (opcional)
        /*
        session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'admin') {
            echo json_encode(["status" => "error", "message" => "No autorizado"]);
            exit;
        }
        */

        $input = json_decode(file_get_contents("php://input"), true);
        $id = intval($input['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(["status" => "error", "message" => "ID inválido"]);
            exit;
        }

        // Verificar que existe
        $check = $conn->query("SELECT id FROM movimientos_inventario WHERE id = {$id}");
        if ($check->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Movimiento no encontrado"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM movimientos_inventario WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Movimiento eliminado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar: " . $stmt->error]);
        }

        break;

    default:
        echo json_encode(["status" => "error", "message" => "Método no permitido"]);
}

$conn->close();
?>