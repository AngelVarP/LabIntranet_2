<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Delegado']);

$out = [
    "grupo" => null,
    "total_alumnos" => 0,
    "ultimas_solicitudes" => []
];

/* Buscar grupo del delegado */
$q = $mysqli->prepare("SELECT id, nombre FROM grupos WHERE delegado_id=? LIMIT 1");
$q->bind_param("i", $u['id']);
$q->execute();
$g = $q->get_result()->fetch_assoc();

if(!$g){
    json_ok($out); // No tiene grupo
}
$grupo_id = $g['id'];
$out['grupo'] = $g['nombre'];

/* Contar alumnos del grupo */
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM grupo_alumnos WHERE grupo_id=?");
$stmt->bind_param("i", $grupo_id);
$stmt->execute();
$out['total_alumnos'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* Últimas solicitudes (5 más recientes) */
$stmt = $mysqli->prepare("SELECT p.nombre AS producto, s.cantidad, s.estado, DATE(s.fecha_solicitud) as fecha
                          FROM solicitudes s
                          JOIN productos p ON p.id = s.producto_id
                          WHERE s.grupo_id=?
                          ORDER BY s.fecha_solicitud DESC
                          LIMIT 5");
$stmt->bind_param("i", $grupo_id);
$stmt->execute();
$out['ultimas_solicitudes'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

json_ok($out);
