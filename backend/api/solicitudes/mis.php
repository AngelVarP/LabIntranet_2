<?php
// backend/api/solicitudes/mis.php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol'] !== 'Alumno') {
  json_error('Solo alumnos pueden ver sus solicitudes', 403);
}

// Buscar el grupo del alumno
$q = $mysqli->prepare("
  SELECT g.id, g.nombre, u2.nombre AS delegado
  FROM grupo_alumnos ga
  JOIN grupos g ON g.id = ga.grupo_id
  LEFT JOIN usuarios u2 ON u2.id = g.delegado_id
  WHERE ga.alumno_id = ?
  LIMIT 1
");
$q->bind_param("i", $u['id']);
$q->execute();
$g = $q->get_result()->fetch_assoc();

if (!$g) {
  json_ok([]); // el alumno no está en ningún grupo
  exit;
}

// Listar solicitudes de ese grupo
$sql = "SELECT s.id, s.fecha_solicitud, s.estado,
               p.nombre AS producto
        FROM solicitudes s
        JOIN productos p ON p.id = s.producto_id
        WHERE s.grupo_id = ?
        ORDER BY s.fecha_solicitud DESC";
$st = $mysqli->prepare($sql);
$st->bind_param("i", $g['id']);
$st->execute();
$res = $st->get_result();

$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
