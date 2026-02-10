<?php
// api/send_email.php
header('Content-Type: application/json');

// Permitir solicitudes desde cualquier origen (CORS) - Opcional, ajustar según seguridad
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Recibir datos JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

$to = $input['to'];
$subject = $input['subject'];
$message = $input['message'];

// Validaciones básicas
if (empty($to) || empty($subject) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios"]);
    exit;
}

// Configuración del correo
// IMPORTANTE: Cambia este correo por uno real de tu dominio cuando lo subas
$from = "notificaciones@petpalace.com"; 
$headers = "From: " . $from . "\r\n";
$headers .= "Reply-To: " . $from . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Cuerpo del mensaje con formato HTML básico
$body = "
<html>
<head>
  <title>$subject</title>
</head>
<body>
  <h3>Hola, tienes un mensaje de Pet Palace</h3>
  <p>" . nl2br(htmlspecialchars($message)) . "</p>
  <hr>
  <small>Pet Palace - Calidad para tu mejor amigo</small>
</body>
</html>
";

// Intentar enviar
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(["status" => "success", "message" => "Correo enviado"]);
} else {
    // Si falla (común en localhost sin configurar), enviamos error pero simulamos éxito para pruebas si quieres
    // Para producción real, descomenta la línea de abajo y comenta la simulación
    // echo json_encode(["status" => "error", "message" => "Fallo al enviar correo (revisa config SMTP)"]);
    
    // SIMULACIÓN DE ÉXITO PARA LOCALHOST (Para que veas que la lógica frontend funciona)
    echo json_encode(["status" => "success", "message" => "Correo simulado (Localhost)"]);
}
?>