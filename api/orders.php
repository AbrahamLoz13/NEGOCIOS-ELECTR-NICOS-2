<?php
require 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {

    // =========================
    // GET - LISTAR PEDIDOS A PROVEEDORES
    // =========================
    case 'GET':
        
        // Si viene un ID específico
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "
                SELECT 
                    pp.*,
                    p.nombre AS producto_nombre,
                    p.precio AS precio_unitario,
                    pr.nombre AS proveedor_nombre,
                    pr.contacto AS proveedor_contacto,
                    pr.correo AS proveedor_correo,
                    pr.telefono AS proveedor_telefono
                FROM pedidos_proveedores pp
                JOIN productos p ON pp.producto_id = p.id
                JOIN proveedores pr ON pp.proveedor_id = pr.id
                WHERE pp.id = {$id}
            ";
        } else {
            // Listar todos los pedidos
            $sql = "
                SELECT 
                    pp.id,
                    pp.cantidad,
                    pp.costo_total,
                    pp.fecha_pedido,
                    pp.estado,
                    p.nombre AS producto,
                    p.precio AS precio_unitario,
                    pr.nombre AS proveedor,
                    pr.contacto AS contacto_proveedor
                FROM pedidos_proveedores pp
                JOIN productos p ON pp.producto_id = p.id
                JOIN proveedores pr ON pp.proveedor_id = pr.id
                ORDER BY pp.fecha_pedido DESC
            ";
        }

        $result = $conn->query($sql);
        $pedidos = [];

        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }

        echo json_encode($pedidos);
        break;


    // =========================
    // POST - CREAR PEDIDO A PROVEEDOR
    // =========================
    case 'POST':

$producto_id = intval($input['producto_id'] ?? 0);
$proveedor_id = intval($input['proveedor_id'] ?? 0);
$cantidad = intval($input['cantidad'] ?? 0);
$estado = $input['estado'] ?? 'pendiente';

    // 1️⃣ Obtener producto completo
    $stmt = $conn->prepare("
        SELECT nombre, precio, stock, stock_minimo
        FROM productos
        WHERE id = ?
    ");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $res_producto = $stmt->get_result();

    if ($res_producto->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
        break;
    }

    $producto = $res_producto->fetch_assoc();

    $stock_actual = $producto['stock'];
    $stock_minimo = $producto['stock_minimo'];
    $precio_unitario = $producto['precio'];

    // 2️⃣ Calcular cantidad automática PUSH
    if ($stock_actual <= $stock_minimo) {
        $cantidad = ($stock_minimo * 2) - $stock_actual; // ejemplo estrategia PUSH
    } else {
        echo json_encode(["status" => "error", "message" => "No requiere reposición"]);
        break;
    }

    $costo_total = $precio_unitario * $cantidad;
    $estado = "pendiente";

    // 3️⃣ Insertar pedido proveedor
    $stmt = $conn->prepare("
        INSERT INTO pedidos_proveedores 
        (producto_id, proveedor_id, cantidad, costo_total, estado)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiids", $producto_id, $proveedor_id, $cantidad, $costo_total, $estado);

    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al crear pedido"]);
        break;
    }

    $pedido_id = $stmt->insert_id;

    // 4️⃣ Registrar movimiento inventario
    $stmt = $conn->prepare("
        INSERT INTO movimientos_inventario
        (producto_id, tipo, cantidad, motivo, referencia_id, fecha)
        VALUES (?, 'entrada', ?, 'Pedido automático PUSH', ?, NOW())
    ");
    $stmt->bind_param("iii", $producto_id, $cantidad, $pedido_id);
    $stmt->execute();

    // 5️⃣ Enviar Telegram
    $botToken = "8559404476:AAHPavE_3vyTTUlpGRj-UHhCxlc2673h4jQ";
    $chatId = "6428526550";

    $mensaje = "🚨 PEDIDO AUTOMÁTICO PULL 🚨\n\n";
    $mensaje .= "Producto: {$producto['nombre']}\n";
    $mensaje .= "Stock actual: {$stock_actual}\n";
    $mensaje .= "Stock mínimo: {$stock_minimo}\n";
    $mensaje .= "Cantidad solicitada: {$cantidad}\n";
    $mensaje .= "Costo total: $ {$costo_total}";

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $dataTelegram = [
        'chat_id' => $chatId,
        'text' => $mensaje
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type:application/x-www-form-urlencoded",
            'content' => http_build_query($dataTelegram)
        ]
    ];

    $context = stream_context_create($options);
    file_get_contents($url, false, $context);

    echo json_encode([
        "status" => "success",
        "message" => "Pedido automático creado",
        "id" => $pedido_id,
        "cantidad" => $cantidad,
        "costo_total" => $costo_total
    ]);

break;

    // =========================
    // PUT - ACTUALIZAR ESTADO DEL PEDIDO
    // =========================
    case 'PUT':

        $data = json_decode(file_get_contents("php://input"), true);

        $id = intval($data['id']);
        $estado = $data['estado']; // pendiente, enviado, entregado, cancelado
        $observaciones = $data['observaciones'] ?? '';

        // Obtener estado anterior para el historial
        $res_actual = $conn->query("SELECT estado FROM pedidos_proveedores WHERE id = {$id}");
        $estado_anterior = $res_actual->fetch_assoc()['estado'];

        $stmt = $conn->prepare("UPDATE pedidos_proveedores SET estado=?, observaciones=? WHERE id=?");
        $stmt->bind_param("ssi", $estado, $observaciones, $id);
        
        if ($stmt->execute()) {
            // Si el estado cambia a 'entregado', podrías actualizar el stock automáticamente
            if ($estado == 'entregado' && $estado_anterior != 'entregado') {
                // Obtener datos del pedido
                $res_pedido = $conn->query("
                    SELECT producto_id, cantidad 
                    FROM pedidos_proveedores 
                    WHERE id = {$id}
                ");
                $pedido = $res_pedido->fetch_assoc();
                
                // Actualizar stock del producto
                $conn->query("
                    UPDATE productos 
                    SET stock = stock + {$pedido['cantidad']} 
                    WHERE id = {$pedido['producto_id']}
                ");
                
                // Registrar en movimientos_inventario
                $fecha_actual = date('Y-m-d H:i:s');
                $log = "INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, motivo, referencia_id, fecha) 
                        VALUES (
                            {$pedido['producto_id']}, 
                            'recepcion_pedido', 
                            {$pedido['cantidad']}, 
                            'Recepción de pedido a proveedor #{$id}', 
                            {$id}, 
                            '{$fecha_actual}'
                        )";
                $conn->query($log);
            }
            
            echo json_encode([
                "status" => "success", 
                "message" => "Pedido actualizado",
                "estado_anterior" => $estado_anterior,
                "estado_nuevo" => $estado
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar"]);
        }
        break;


    // =========================
    // DELETE - ELIMINAR PEDIDO
    // =========================
    case 'DELETE':

        $data = json_decode(file_get_contents("php://input"), true);
        $id = intval($data['id']);

        // Verificar que el pedido existe
        $res = $conn->query("SELECT id FROM pedidos_proveedores WHERE id = {$id}");
        if ($res->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Pedido no encontrado"]);
            break;
        }

        // Eliminar pedido (los movimientos_inventario quedan como historial)
        if ($conn->query("DELETE FROM pedidos_proveedores WHERE id = {$id}")) {
            echo json_encode(["status" => "success", "message" => "Pedido eliminado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar"]);
        }
        break;
}

$conn->close();
?>