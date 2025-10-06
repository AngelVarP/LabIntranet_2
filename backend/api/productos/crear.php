<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador']); // o ['Administrador','Profesor'] si quieres que profesor cree

$in = input_json();

// Datos que vienen del front
$nombre       = trim($in['nombre'] ?? '');
$unidad       = trim($in['unidad'] ?? '');
$stock        = (int)($in['stock'] ?? 0);
$categoria_in = $in['categoria_id'] ?? null;
$descripcion  = trim($in['descripcion'] ?? '');

// Validaciones mínimas
if ($nombre === '' || $unidad === '') {
  json_error('Faltan datos obligatorios: nombre y unidad', 422);
}

// Resolver categoria_id real
$categoria_id = null;
if ($categoria_in !== null && $categoria_in !== '' && $categoria_in !== 'todos') {
  if (is_numeric($categoria_in)) {
    $categoria_id = (int)$categoria_in;
  } else {
    // vino como nombre, buscar o crear
    $stmt = $mysqli->prepare("SELECT id FROM categorias WHERE nombre=? LIMIT 1");
    $stmt->bind_param('s', $categoria_in);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
      $categoria_id = (int)$row['id'];
    } else {
      $ins = $mysqli->prepare("INSERT INTO categorias (nombre) VALUES (?)");
      $ins->bind_param('s', $categoria_in);
      if (!$ins->execute()) {
        json_error('Error al crear categoría: '.$ins->error, 500);
      }
      $categoria_id = $ins->insert_id;
    }
  }
}

// Insertar producto
$sql = "INSERT INTO productos (nombre, descripcion, categoria_id, stock, unidad)
        VALUES (?,?,?,?,?)";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  json_error('Error de preparación SQL: '.$mysqli->error, 500);
}
$stmt->bind_param('ssiss', $nombre, $descripcion, $categoria_id, $stock, $unidad);

if (!$stmt->execute()) {
  json_error('Error SQL: '.$stmt->error, 500);
}

json_ok(['success'=>true, 'id'=>$stmt->insert_id]);
