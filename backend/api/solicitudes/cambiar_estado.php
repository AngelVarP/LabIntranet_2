<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol'] !== 'Administrador') {
    json_error("No autorizado", 403);
}

$data = json_input();
$id = intval($data['id'] ?? 0);
$estado = $mysqli->real_escape_string($data['estado'] ?? '');
$cant = intval($data['cantidad_aprobada'] ?? 0);
$coment = $mysqli->real_escape_string($data['comentario_admin'] ?? '');

if (!$id || !$estado) {
    json_error("Datos incompletos",400);
}

$sql = "UPDATE solicitudes
        SET estado='$estado', cantidad_aprobada=$cant, comentario_admin='$coment'
        WHERE id=$id";
if(!$mysqli->query($sql)){
    json_error("Error SQL: ".$mysqli->error,500);
}

json_ok(["id"=>$id,"estado"=>$estado]);
