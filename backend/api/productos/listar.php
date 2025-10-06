<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']); // Permite admin y profesor

$sql = "SELECT p.id, p.nombre, p.descripcion, p.stock, p.unidad,
               p.categoria_id,
               c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        ORDER BY p.nombre";

$res = $mysqli->query($sql);
if(!$res){
    json_error('Error SQL: '.$mysqli->error,500);
}

$out = [];
while($r = $res->fetch_assoc()){
    $out[] = $r;
}

json_ok($out);
