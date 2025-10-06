<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol']!=='Profesor' && $u['rol']!=='Administrador') {
    json_error("Solo profesores o administradores pueden eliminar prácticas", 403);
}

$data = input_json();
$id = intval($data['id'] ?? 0);
if($id <= 0) json_error("ID inválido");

$mysqli->begin_transaction();
try {
    $mysqli->query("DELETE FROM practicas_materiales WHERE practica_id=$id");
    $mysqli->query("DELETE FROM grupo_practicas WHERE practica_id=$id");
    $mysqli->query("DELETE FROM practicas WHERE id=$id");
    $mysqli->commit();
    json_ok(["success"=>true]);
} catch(Exception $e) {
    $mysqli->rollback();
    json_error("Error eliminando: ".$e->getMessage(),500);
}
