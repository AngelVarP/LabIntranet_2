<?php
// backend/api/solicitudes/listar.php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol'] === 'Delegado') {
    // Buscar el grupo del delegado
    $q = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id=? LIMIT 1");
    $q->bind_param("i", $u['id']);
    $q->execute();
    $g = $q->get_result()->fetch_assoc();
    if (!$g) json_ok([]); // No tiene grupo

    // Solicitudes solo de su grupo
    $sql = "SELECT s.id, p.nombre AS producto, s.cantidad, s.estado, s.fecha_solicitud
            FROM solicitudes s
            JOIN productos p ON p.id = s.producto_id
            WHERE s.grupo_id = ?
            ORDER BY s.fecha_solicitud DESC";
    $st = $mysqli->prepare($sql);
    $st->bind_param("i", $g['id']);
    $st->execute();
    $res = $st->get_result();

} else {
    // Admin / Profesor ven todas las solicitudes
    $sql = "SELECT s.id, g.nombre AS grupo, p.nombre AS producto, s.cantidad, s.estado, s.fecha_solicitud
            FROM solicitudes s
            JOIN grupos g ON g.id = s.grupo_id
            JOIN productos p ON p.id = s.producto_id
            ORDER BY s.fecha_solicitud DESC";
    $res = $mysqli->query($sql);
}

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = $r;
}

json_ok($out);
