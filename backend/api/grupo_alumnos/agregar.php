<?php
// backend/api/grupo_alumnos/agregar.php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Delegado']);

$in = array_merge($_POST, input_json());
$alumno_codigo = trim($in['codigo'] ?? '');
if ($alumno_codigo === '') json_error('Código de alumno requerido',422);

// ID del grupo del delegado
$q = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
$q->bind_param("i",$u['id']); $q->execute();
$g = $q->get_result()->fetch_assoc();
if(!$g) json_error("Crea tu grupo primero",409);
$grupo_id = (int)$g['id'];

// Buscar alumno
$qa = $mysqli->prepare("SELECT u.id FROM usuarios u JOIN roles r ON r.id=u.rol_id WHERE u.codigo=? AND r.nombre='Alumno' LIMIT 1");
$qa->bind_param("s",$alumno_codigo);
$qa->execute();
$a = $qa->get_result()->fetch_assoc();
if(!$a) json_error("Alumno no encontrado",404);
$alumno_id = (int)$a['id'];

// Insertar
try {
  $ins = $mysqli->prepare("INSERT INTO grupo_alumnos(grupo_id,alumno_id) VALUES(?,?)");
  $ins->bind_param("ii",$grupo_id,$alumno_id);
  $ins->execute();
  json_ok(['success'=>true],201);
} catch (mysqli_sql_exception $e) {
  if ($e->getCode()==1062) json_error('El alumno ya está en el grupo',409);
  json_error('Error al agregar',500);
}
