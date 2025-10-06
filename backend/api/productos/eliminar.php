<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador']);

$in = input_json();
$id = (int)($in['id'] ?? 0);
if($id <= 0) json_error('ID inválido');

$stmt = $mysqli->prepare("DELETE FROM productos WHERE id=?");
$stmt->bind_param('i', $id);

if($stmt->execute()) {
    json_ok(['success' => true]);
} else {
    json_error('No se pudo eliminar el producto');
}
