<?php
// backend/api/solicitudes/profesor.php
require_once __DIR__ . '/../_init.php';
require_login();

$u = current_user();
if ($u['rol'] !== 'Profesor' && $u['rol'] !== 'Administrador') {
    json_error('No autorizado', 403);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "
        SELECT s.id, g.nombre AS grupo, p.nombre AS producto, s.cantidad, s.estado, DATE(s.fecha_solicitud) AS fecha
        FROM solicitudes s
        JOIN grupos g ON g.id = s.grupo_id
        JOIN productos p ON p.id = s.producto_id
        ORDER BY s.fecha_solicitud DESC, s.id DESC
    ";
    $res = $mysqli->query($sql);
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_ok($rows);
}

if ($method === 'POST') {
    $data = array_merge($_POST, input_json());
    $id = (int)($data['id'] ?? 0);
    $estado = $data['estado'] ?? '';

    if (!$id || !in_array($estado, ['Aprobada','Rechazada'])) {
        json_error('Datos inválidos', 422);
    }

    $stmt = $mysqli->prepare("UPDATE solicitudes SET estado=? WHERE id=?");
    $stmt->bind_param('si', $estado, $id);
    if (!$stmt->execute()) json_error('No se pudo actualizar', 500);

    json_ok(['success'=>true]);
}

json_error('Método no permitido', 405);
