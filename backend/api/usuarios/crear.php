<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();
require_roles(['Administrador']);

$in = array_merge($_POST, input_json());
$codigo = trim($in['codigo'] ?? '');
$nombre = trim($in['nombre'] ?? '');
$correo = trim($in['correo'] ?? '');
$rol    = trim($in['rol'] ?? '');

if ($codigo === '' || $nombre === '' || $correo === '' || $rol === '') {
    json_error("Faltan datos", 422);
}

// Buscar rol_id
$stmt = $mysqli->prepare("SELECT id FROM roles WHERE nombre=? LIMIT 1");
$stmt->bind_param('s', $rol);
$stmt->execute();
$rolRow = $stmt->get_result()->fetch_assoc();
$rol_id = $rolRow['id'] ?? null;

if (!$rol_id) {
    json_error("Rol no válido", 400);
}

// Insertar usuario con contraseña por defecto "123456"
$passHash = password_hash("123456", PASSWORD_BCRYPT);
$stmt = $mysqli->prepare("INSERT INTO usuarios (codigo,nombre,correo,password,rol_id) VALUES (?,?,?,?,?)");
$stmt->bind_param('ssssi', $codigo, $nombre, $correo, $passHash, $rol_id);
if (!$stmt->execute()) {
    json_error("Error SQL: ".$stmt->error, 500);
}

json_ok(['success'=>true,'message'=>'Usuario creado']);
