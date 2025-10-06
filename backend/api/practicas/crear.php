<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol']!=='Profesor' && $u['rol']!=='Administrador') {
    json_error("Solo profesores o administradores pueden crear prácticas", 403);
}

$data = input_json();
$nombre = trim($data['nombre'] ?? '');
$descripcion = trim($data['descripcion'] ?? '');
$materiales = $data['materiales'] ?? [];

if (!$nombre) json_error("Falta el nombre de la práctica");
if (!$descripcion) json_error("Falta la descripción de la práctica");

$stmt = $mysqli->prepare("INSERT INTO practicas (nombre, descripcion, profesor_id) VALUES (?,?,?)");
$stmt->bind_param("ssi", $nombre, $descripcion, $u['id']);
if(!$stmt->execute()) json_error("Error al crear la práctica: ".$stmt->error, 500);
$practica_id = $stmt->insert_id;

if (is_array($materiales)) {
    $mat = $mysqli->prepare("INSERT INTO practicas_materiales (practica_id, producto_id, cantidad) VALUES (?,?,?)");
    foreach($materiales as $m){
        $pid = intval($m['producto_id'] ?? 0);
        $cant = intval($m['cantidad'] ?? 0);
        if($pid>0 && $cant>0){
            $mat->bind_param("iii", $practica_id, $pid, $cant);
            $mat->execute();
        }
    }
}

json_ok(["id"=>$practica_id, "success"=>true]);
