<?php
// backend/api/solicitudes/mis_solicitudes.php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol'] !== 'Alumno') {
    json_error('Solo alumnos pueden acceder', 403);
}

// Buscar el grupo del alumno
$sql = "SELECT g.id, g.nombre, u2.nombre AS delegado
        FROM grupos g
        JOIN grupo_alumnos ga ON ga.grupo_id = g.id
        JOIN usuarios u2 ON u2.id = g.delegado_id
        WHERE ga.alumno_id = ?
        LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $u['id']);
$stmt->execute();
$grupo = $stmt->get_result()->fetch_assoc();

if (!$grupo) {
    json_ok([]); // El alumno no pertenece a ningún grupo
}

// Listar solicitudes del grupo
$sql2 = "SELECT s.id, s.fecha_solicitud, s.estado,
                p.nombre AS producto
         FROM solicitudes s
         JOIN productos p ON p.id = s.producto_id
         WHERE s.grupo_id = ?
         ORDER BY s.fecha_solicitud DESC";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param("i", $grupo['id']);
$stmt2->execute();
$res = $stmt2->get_result();

$out = [];
while ($row = $res->fetch_assoc()) $out[] = $row;

json_ok([
    "grupo" => $grupo,
    "solicitudes" => $out
]);
