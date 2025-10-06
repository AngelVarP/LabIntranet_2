<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol'] !== 'Alumno') {
    json_error('Solo alumnos', 403);
}

$sql = "SELECT g.id, g.nombre, u2.nombre AS delegado_nombre, u2.codigo AS delegado_codigo
        FROM grupo_alumnos ga
        JOIN grupos g ON g.id = ga.grupo_id
        JOIN usuarios u2 ON u2.id = g.delegado_id
        WHERE ga.alumno_id = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $u['id']);
$stmt->execute();
$g = $stmt->get_result()->fetch_assoc();

if (!$g) json_ok(null);

// Obtener integrantes del grupo
$integrantes = [];
if ($g) {
    $st = $mysqli->prepare("SELECT u.id, u.codigo, u.nombre FROM grupo_alumnos ga JOIN usuarios u ON u.id=ga.alumno_id WHERE ga.grupo_id=?");
    $st->bind_param("i", $g['id']);
    $st->execute();
    $res = $st->get_result();
    while($row = $res->fetch_assoc()) $integrantes[] = $row;
    $g['integrantes'] = $integrantes;
}

json_ok($g);
