<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Profesor','Administrador']);

$in = input_json();
$grupo_id = intval($in['grupo_id'] ?? 0);
$practica_id = intval($in['practica_id'] ?? 0);

if ($grupo_id <= 0 || $practica_id <= 0) {
    json_error("Faltan datos para asignar la práctica", 400);
}

// Validar que la práctica sea del profesor actual
$stmt = $mysqli->prepare("SELECT id FROM practicas WHERE id=? AND profesor_id=?");
$stmt->bind_param('ii', $practica_id, $u['id']);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    json_error("No puedes asignar una práctica que no es tuya", 403);
}

// Insertar o actualizar relación
$stmt = $mysqli->prepare("INSERT INTO grupo_practicas (grupo_id, practica_id)
                          VALUES (?,?)
                          ON DUPLICATE KEY UPDATE estado=estado");
$stmt->bind_param('ii', $grupo_id, $practica_id);
if(!$stmt->execute()) json_error("Error al asignar práctica: ".$stmt->error,500);

json_ok(['success'=>true]);
