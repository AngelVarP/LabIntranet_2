<?php
require_once __DIR__ . '/../_init.php';
require_login();

$u = current_user();
if ($u['rol'] !== 'Delegado' && $u['rol'] !== 'Administrador' && $u['rol'] !== 'Profesor') {
    json_error('No autorizado', 403);
}

// Alumnos que no están en NINGÚN grupo
$sql = "
SELECT u.id, u.codigo, u.nombre, u.correo
FROM usuarios u
JOIN roles r ON r.id = u.rol_id AND r.nombre = 'Alumno'
LEFT JOIN grupo_alumnos ga ON ga.alumno_id = u.id
WHERE ga.id IS NULL
ORDER BY u.nombre
";
$res = $mysqli->query($sql);
$out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
json_ok($out);
