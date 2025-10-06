<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']);

$sql = "SELECT p.id, p.nombre, DATE(p.fecha) as fecha, COUNT(gp.id) AS grupos_asignados
        FROM practicas p
        LEFT JOIN grupo_practicas gp ON gp.practica_id = p.id
        WHERE p.profesor_id = ?
        GROUP BY p.id
        ORDER BY p.fecha DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $u['id']);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
