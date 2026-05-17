<?php
// ==========================================
// SEGURIDAD: BLOQUEAR ACCESO DIRECTO POR URL
// ==========================================
if (!isset($_SERVER['HTTP_REFERER'])) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "Acceso directo denegado 🚫. Solo el sistema puede hacer peticiones aquí."]));
}
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $r = $conn->query("SELECT * FROM estado_erp ORDER BY id DESC LIMIT 1");
    if ($r->num_rows === 0) {
        // Si no existe, crear registro inicial
        $conn->query("INSERT INTO estado_erp (nivel) VALUES ('Básico')");
        echo json_encode(["id" => 1, "nivel" => "Básico"]);
    } else {
        echo json_encode($r->fetch_assoc());
    }
    exit;
}

if ($method === 'PUT') {
    $data  = json_decode(file_get_contents("php://input"), true);
    $nivel = $conn->real_escape_string($data['nivel'] ?? 'Básico');

    $niveles = ['Básico', 'Integrado', 'Automatizado', 'Optimizado'];
    if (!in_array($nivel, $niveles)) {
        echo json_encode(["ok" => false, "error" => "Nivel inválido"]);
        exit;
    }

    // Verificar si ya existe un registro
    $r = $conn->query("SELECT id FROM estado_erp LIMIT 1");
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $conn->query("UPDATE estado_erp SET nivel='$nivel', fecha_actualizacion=NOW() WHERE id={$row['id']}");
    } else {
        $conn->query("INSERT INTO estado_erp (nivel) VALUES ('$nivel')");
    }

    echo json_encode(["ok" => true, "nivel" => $nivel]);
    exit;
}

echo json_encode(["ok" => false, "error" => "Método no permitido"]);