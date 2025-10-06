<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();
require_roles(['Administrador']);

$in = array_merge($_POST, input_json());
$id = (int)($in['id'] ?? 0);

if ($id <= 0) {
    json_error("ID inválido", 422);
}

$stmt = $mysqli->prepare("DELETE FROM usuarios WHERE id=? LIMIT 1");
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    json_error("Error SQL: ".$stmt->error, 500);
}

json_ok(['success'=>true,'message'=>'Usuario eliminado']);
