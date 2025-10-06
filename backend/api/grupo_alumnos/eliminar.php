<?php
// backend/api/grupo_alumnos/eliminar.php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Delegado']);

$in = array_merge($_POST, input_json());
$id = (int)($in['id'] ?? 0);
if($id<=0) json_error('ID inválido',422);

// Confirmar que el miembro pertenece al grupo del delegado
$sql = "SELECT ga.id FROM grupo_alumnos ga 
        JOIN grupos g ON g.id=ga.grupo_id
        WHERE ga.id=? AND g.delegado_id=?";
$st=$mysqli->prepare($sql);
$st->bind_param("ii",$id,$u['id']);
$st->execute();
$ok=$st->get_result()->fetch_assoc();
if(!$ok) json_error('No autorizado',403);

$del=$mysqli->prepare("DELETE FROM grupo_alumnos WHERE id=?");
$del->bind_param("i",$id);
$del->execute();
json_ok(['success'=>true]);
