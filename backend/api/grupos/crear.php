<?php
// backend/api/grupos/crear.php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Delegado']);

$in = array_merge($_POST, input_json());
$nombre = trim($in['nombre'] ?? '');
if ($nombre==='') json_error('Nombre requerido',422);

// ¿ya tiene grupo?
$q = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
$q->bind_param("i",$u['id']); $q->execute();
$ex = $q->get_result()->fetch_assoc();
if($ex) json_error('Ya tienes un grupo',409);

$ins = $mysqli->prepare("INSERT INTO grupos(nombre,delegado_id) VALUES(?,?)");
$ins->bind_param("si",$nombre,$u['id']);
$ins->execute();

json_ok(['success'=>true,'grupo_id'=>$ins->insert_id],201);
