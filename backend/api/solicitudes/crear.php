<?php
// backend/api/solicitudes/crear.php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Delegado']);

$in = array_merge($_POST, input_json());
$producto_id = (int)($in['producto_id'] ?? 0);
$cantidad    = (int)($in['cantidad'] ?? 0);
if ($producto_id<=0 || $cantidad<=0) json_error('Datos inválidos',422);

// grupo del delegado
$q = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
$q->bind_param("i",$u['id']); $q->execute();
$g=$q->get_result()->fetch_assoc();
if(!$g) json_error('Crea tu grupo primero',409);

$ins = $mysqli->prepare("INSERT INTO solicitudes(grupo_id,producto_id,cantidad) VALUES(?,?,?)");
$ins->bind_param("iii",$g['id'],$producto_id,$cantidad);
$ins->execute();

json_ok(['success'=>true,'solicitud_id'=>$ins->insert_id],201);
