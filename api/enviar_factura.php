<?php
// Cargar los archivos de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$correoDestino = isset($data['correo_destino']) ? $data['correo_destino'] : '';
$asunto = isset($data['asunto']) ? $data['asunto'] : 'Factura Pet Palace';
$htmlContent = isset($data['html_content']) ? base64_decode($data['html_content']) : '';

if (empty($correoDestino) || empty($htmlContent)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos."]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'lozanitodel@gmail.com'; // <--- TU CORREO AQUÍ
    $mail->Password   = 'sjzi diyk wztu pmwr'; // <--- LA DE 16 LETRAS AQUÍ
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- CONFIGURACIÓN DEL CORREO ---
    $mail->setFrom('petpalace@gmail.com', 'Pet Palace Tienda');
    $mail->addAddress($correoDestino); 
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = $htmlContent;
    $mail->CharSet = 'UTF-8';

    $mail->send();
    echo json_encode(["status" => "success", "message" => "¡Factura enviada por correo real!"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al enviar: {$mail->ErrorInfo}"]);
}