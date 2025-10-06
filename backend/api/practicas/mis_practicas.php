<?php
// backend/api/practicas/mis_practicas.php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if($u['rol']==='Delegado'){
  $q=$mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
  $q->bind_param("i",$u['id']); $q->execute();
  $g=$q->get_result()->fetch_assoc();
  if(!$g) json_ok([]);
  $gid=(int)$g['id'];

  $sql="SELECT p.id,p.nombre,p.descripcion,p.fecha, gp.estado, u.nombre AS profesor
        FROM grupo_practicas gp
        JOIN practicas p ON p.id=gp.practica_id
        JOIN usuarios u ON u.id=p.profesor_id
        WHERE gp.grupo_id=? ORDER BY p.fecha";
  $st=$mysqli->prepare($sql);
  $st->bind_param("i",$gid); $st->execute();
  $res=$st->get_result();
  $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
  json_ok($out);
}
elseif($u['rol']==='Alumno'){
  $sql="SELECT p.id,p.nombre,p.descripcion,p.fecha,gp.estado,u.nombre AS profesor
        FROM grupo_alumnos ga
        JOIN grupos g ON g.id=ga.grupo_id
        JOIN grupo_practicas gp ON gp.grupo_id=g.id
        JOIN practicas p ON p.id=gp.practica_id
        JOIN usuarios u ON u.id=p.profesor_id
        WHERE ga.alumno_id=? ORDER BY p.fecha";
  $st=$mysqli->prepare($sql);
  $st->bind_param("i",$u['id']); $st->execute();
  $res=$st->get_result();
  $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
  json_ok($out);
}
else{
  json_error('Solo delegado/alumno',403);
}
