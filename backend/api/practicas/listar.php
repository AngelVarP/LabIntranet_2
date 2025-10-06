<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if ($u['rol']==='Profesor' || $u['rol']==='Administrador') {
    // Profesor/Admin ve SOLO las que él creó
    $sql="SELECT p.id, p.nombre, p.descripcion, DATE(p.fecha) AS fecha, u.nombre AS profesor
          FROM practicas p
          JOIN usuarios u ON u.id = p.profesor_id
          WHERE p.profesor_id = ?
          ORDER BY p.fecha DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $u['id']);
    $stmt->execute();
    $res = $stmt->get_result();

} else if ($u['rol']==='Delegado' || $u['rol']==='Alumno') {
    // Delegados y Alumnos ven TODAS las prácticas
    $sql="SELECT p.id, p.nombre, p.descripcion, DATE(p.fecha) AS fecha, u.nombre AS profesor
          FROM practicas p
          JOIN usuarios u ON u.id = p.profesor_id
          ORDER BY p.fecha DESC";
    $res = $mysqli->query($sql);

} else {
    json_ok([]);
}

$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
