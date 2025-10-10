<?php
require_once __DIR__ . '/../_init.php';

$in     = input_json();
$token  = trim($in['token'] ?? ($_GET['token'] ?? ''));                 // acepta token por JSON o GET
$pass   = trim($in['password'] ?? ($in['new_password'] ?? ''));         // acepta 'password' o 'new_password'

if ($token === '' || $pass === '') {
  json_error('Token y nueva contraseña son requeridos.', 422);
}

// Busca token vigente
$stmt = $mysqli->prepare("
  SELECT usuario_id
  FROM password_resets
  WHERE token = ? AND expira_en > NOW()
  LIMIT 1
");
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
  // Log de ayuda para depurar en local
  file_put_contents(__DIR__ . '/../../_reset_debug.txt',
    "[".date('c')."] TOKEN NO ENCONTRADO/EXPIRADO: $token\n", FILE_APPEND);
  json_error('El enlace de restablecimiento es inválido o ha expirado.', 400);
}

$uid  = (int)$row['usuario_id'];
$hash = password_hash($pass, PASSWORD_BCRYPT);

// Actualiza contraseña
$up = $mysqli->prepare("UPDATE usuarios SET password=? WHERE id=?");
$up->bind_param('si', $hash, $uid);
$up->execute();

// Elimina SOLO el token usado (así no invalidas otros si generaste varios)
$del = $mysqli->prepare("DELETE FROM password_resets WHERE token=?");
$del->bind_param('s', $token);
$del->execute();

json_ok(['success'=>true,'message'=>'Contraseña actualizada correctamente.']);
