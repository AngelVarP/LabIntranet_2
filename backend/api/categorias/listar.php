<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']); // ahora permite ambos

$sql = "SELECT id, nombre, descripcion FROM categorias ORDER BY nombre";
$res = $mysqli->query($sql);

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
