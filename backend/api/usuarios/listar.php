<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

// Solo administradores pueden ver usuarios
require_roles(['Administrador']);

$sql = "SELECT u.id, u.codigo, u.nombre, u.correo, r.nombre AS rol
        FROM usuarios u
        LEFT JOIN roles r ON r.id = u.rol_id
        ORDER BY u.nombre";
$res = $mysqli->query($sql);

if (!$res) {
    json_error("Error SQL: " . $mysqli->error, 500);
}

$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = $row;
}

json_ok($out);
