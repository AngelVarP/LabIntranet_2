<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']);

// Listar todos los grupos con su delegado
$sql = "SELECT g.id, g.nombre, u.nombre AS delegado
        FROM grupos g
        JOIN usuarios u ON u.id = g.delegado_id
        ORDER BY g.nombre";
$res = $mysqli->query($sql);

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
