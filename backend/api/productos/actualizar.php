<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador']);

$in = input_json();
$id = (int)($in['id'] ?? 0);
$nombre = trim($in['nombre'] ?? '');
$descripcion = trim($in['descripcion'] ?? '');
$stock = (int)($in['stock'] ?? 0);
$unidad = trim($in['unidad'] ?? '');
$categoria_id = (int)($in['categoria_id'] ?? 0);

if($id <= 0) json_error('ID inválido');
if($nombre === '' || $unidad === '') json_error('Nombre y unidad son obligatorios');

$stmt = $mysqli->prepare(
    "UPDATE productos SET nombre=?, descripcion=?, stock=?, unidad=?, categoria_id=? WHERE id=?"
);
$stmt->bind_param('ssisii', $nombre, $descripcion, $stock, $unidad, $categoria_id, $id);

if($stmt->execute()) {
    json_ok(['success' => true]);
} else {
    json_error('No se pudo actualizar el producto');
}
