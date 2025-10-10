<?php
require_once __DIR__ . '/../_init.php';

// ====== Importar PHPMailer ======
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/SMTP.php';

// ====== Cargar configuración SMTP ======
$configFile = __DIR__ . '/../../config/mail_config.php';
if (!file_exists($configFile)) {
  json_error('Falta archivo de configuración de correo', 500);
}
$config = require $configFile;

// ====== Leer el código enviado ======
$in = input_json();
$codigo = trim($in['codigo'] ?? '');
if ($codigo === '') json_error('Código requerido', 422);

// ====== Buscar usuario ======
$stmt = $mysqli->prepare("SELECT id, correo, nombre FROM usuarios WHERE codigo=? LIMIT 1");
$stmt->bind_param('s', $codigo);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  json_ok(['success'=>true, 'message'=>'Si la cuenta existe, se enviará un correo.']);
  exit; // ← IMPORTANTE
}


// ====== Crear token de recuperación ======
$token = bin2hex(random_bytes(16));
$expira = date('Y-m-d H:i:s', time() + 900); // 15 min

// Asegurar que la tabla existe
$mysqli->query("CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  expira_en DATETIME NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
)");

$q = $mysqli->prepare("INSERT INTO password_resets (usuario_id, token, expira_en) VALUES (?,?,?)");
$q->bind_param("iss", $user['id'], $token, $expira);
$q->execute();

// ====== Enlace de restablecimiento ======
$link = "http://localhost:8000/LabIntranet_2/frontend/public/reset_password.html?token=$token";

// ====== Enviar correo con PHPMailer ======
$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host       = $config['host'];
  $mail->SMTPAuth   = true;
  $mail->Username   = $config['username'];
  $mail->Password   = $config['password'];
  $mail->SMTPSecure = 'tls';
  $mail->Port       = $config['port'];
  $mail->CharSet    = 'UTF-8';

  $mail->setFrom($config['username'], $config['from_name']);
  $mail->addAddress($user['correo'], $user['nombre']);

  $mail->isHTML(true);
  $mail->Subject = "Recuperación de contraseña - Laboratorio";
  $mail->Body = "
    <p>Hola <b>{$user['nombre']}</b>,</p>
    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
    <p>Puedes hacerlo en el siguiente enlace (válido por 15 minutos):</p>
    <p><a href='$link'>$link</a></p>
    <br>
    <p>Si no hiciste esta solicitud, puedes ignorar este mensaje.</p>
    <p>Atentamente,<br><b>Laboratorio Intranet</b></p>
  ";

  $mail->send();

  json_ok([
    'success' => true,
    'message' => 'Correo de recuperación enviado correctamente a ' . $user['correo']
  ]);
} catch (Exception $e) {
  // Fallback: guardar enlace para pruebas
  $log = __DIR__ . '/../../_last_recover.txt';
  file_put_contents($log, "[{$user['correo']}] $link (Error: {$mail->ErrorInfo})\n", FILE_APPEND);
  json_ok([
    'success' => false,
    'message' => 'No se pudo enviar el correo, revisa _last_recover.txt para probar el enlace.',
    'error' => $mail->ErrorInfo
  ]);
}
