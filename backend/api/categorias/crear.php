<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']); // permite ambos

$in = input_json();
$nombre = trim($in['nombre'] ?? '');
$descripcion = trim($in['descripcion'] ?? '');

if ($nombre==='') json_error('Falta nombre');

$stmt=$mysqli->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
$stmt->bind_param('ss',$nombre,$descripcion);
if(!$stmt->execute()) json_error('Error: '.$stmt->error,500);

json_ok(['success'=>true,'id'=>$stmt->insert_id]);
