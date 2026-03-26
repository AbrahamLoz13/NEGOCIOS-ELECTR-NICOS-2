<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../db.php';

$metricas = [];

// Ventas totales (órdenes ERP procesadas)
$r = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM ordenes WHERE estado='procesada'");
$metricas['ventas_totales'] = floatval($r->fetch_assoc()['total']);

// Órdenes procesadas
$r = $conn->query("SELECT COUNT(*) AS total FROM ordenes WHERE estado='procesada'");
$metricas['ordenes_procesadas'] = intval($r->fetch_assoc()['total']);

// Órdenes pendientes
$r = $conn->query("SELECT COUNT(*) AS total FROM ordenes WHERE estado='pendiente'");
$metricas['ordenes_pendientes'] = intval($r->fetch_assoc()['total']);

// Productos vendidos (suma de cantidades en órdenes procesadas)
$r = $conn->query("
    SELECT COALESCE(SUM(od.cantidad), 0) AS total
    FROM orden_detalle od
    JOIN ordenes o ON od.orden_id = o.id
    WHERE o.estado = 'procesada'
");
$metricas['productos_vendidos'] = intval($r->fetch_assoc()['total']);

// Clientes activos
$r = $conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado='activo'");
$metricas['clientes_activos'] = intval($r->fetch_assoc()['total']);

// Inventario disponible (stock total)
$r = $conn->query("SELECT COALESCE(SUM(stock), 0) AS total FROM productos");
$metricas['inventario_disponible'] = intval($r->fetch_assoc()['total']);

// Productos con stock bajo (menor o igual al stock_minimo)
$r = $conn->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= stock_minimo");
$metricas['stock_critico'] = intval($r->fetch_assoc()['total']);

// Nivel ERP actual
$r = $conn->query("SELECT nivel FROM estado_erp ORDER BY id DESC LIMIT 1");
$metricas['nivel_erp'] = $r->num_rows > 0 ? $r->fetch_assoc()['nivel'] : 'Básico';

// Ventas por mes (últimos 6 meses) para gráfica
$r = $conn->query("
    SELECT DATE_FORMAT(fecha, '%b %Y') AS mes,
           MONTH(fecha) AS mes_num,
           YEAR(fecha) AS anio,
           COALESCE(SUM(total), 0) AS total
    FROM ordenes
    WHERE estado='procesada'
      AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(fecha), MONTH(fecha)
    ORDER BY YEAR(fecha), MONTH(fecha)
");
$ventas_mes = [];
while ($row = $r->fetch_assoc()) $ventas_mes[] = $row;
$metricas['ventas_por_mes'] = $ventas_mes;

// Top 5 productos más vendidos
$r = $conn->query("
    SELECT p.nombre, SUM(od.cantidad) AS vendidos
    FROM orden_detalle od
    JOIN productos p ON od.producto_id = p.id
    JOIN ordenes o ON od.orden_id = o.id
    WHERE o.estado = 'procesada'
    GROUP BY p.id
    ORDER BY vendidos DESC
    LIMIT 5
");
$top_productos = [];
while ($row = $r->fetch_assoc()) $top_productos[] = $row;
$metricas['top_productos'] = $top_productos;

// Movimientos recientes
$r = $conn->query("
    SELECT m.tipo, m.cantidad, m.motivo, m.fecha, p.nombre AS producto
    FROM movimientos_inventario m
    JOIN productos p ON m.producto_id = p.id
    ORDER BY m.fecha DESC
    LIMIT 5
");
$movimientos = [];
while ($row = $r->fetch_assoc()) $movimientos[] = $row;
$metricas['movimientos_recientes'] = $movimientos;

echo json_encode($metricas);