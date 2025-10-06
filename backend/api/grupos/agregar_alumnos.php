<?php
require_once __DIR__ . '/../_init.php';
$u=require_token();

if($u['rol']!=='Delegado') json_error('Solo delegados',403);

$data=input_json();
$alumnos=$data['alumnos']??[];

if(!is_array($alumnos)||!count($alumnos)) json_error('Debe enviar alumnos',422);

// Obtener id de grupo del delegado
$q=$mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
$q->bind_param('i',$u['id']);
$q->execute();
$g=$q->get_result()->fetch_assoc();
if(!$g) json_error('Primero crea un grupo',409);
$grupo_id=$g['id'];

$ins=$mysqli->prepare("INSERT IGNORE INTO grupo_alumnos(grupo_id,alumno_id)VALUES(?,?)");
foreach($alumnos as $a){
    $a=(int)$a;
    $ins->bind_param('ii',$grupo_id,$a);
    $ins->execute();
}

json_ok(['success'=>true]);
