<?php
require_once __DIR__ . '/../_init.php';
require_login();

$u = current_user();
if ($u['rol'] !== 'Delegado') json_error('Solo delegados', 403);

// Grupo del delegado
$stmt = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id = ? LIMIT 1");
$stmt->bind_param('i', $u['id']);
$stmt->execute();
$g = $stmt->get_result()->fetch_assoc();
if (!$g) json_error('No tienes grupo', 409);
$grupo_id = (int)$g['id'];

$body = array_merge($_POST, input_json());
$alumno_id = (int)($body['alumno_id'] ?? 0);
if ($alumno_id <= 0) json_error('alumno_id requerido', 422);

$del = $mysqli->prepare("DELETE FROM grupo_alumnos WHERE grupo_id = ? AND alumno_id = ?");
$del->bind_param('ii', $grupo_id, $alumno_id);
$del->execute();

// devolver lista actualizada
$q = $mysqli->prepare("
    SELECT ua.id, ua.codigo, ua.nombre, ua.correo
    FROM grupo_alumnos ga
    JOIN usuarios ua ON ua.id = ga.alumno_id
    WHERE ga.grupo_id = ?
    ORDER BY ua.nombre
");
$q->bind_param('i', $grupo_id);
$q->execute();
$lista = $q->get_result()->fetch_all(MYSQLI_ASSOC);

json_ok(['success'=>true, 'alumnos'=>$lista]);
