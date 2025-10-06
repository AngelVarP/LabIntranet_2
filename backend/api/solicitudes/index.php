<?php
// ===============================
// backend/api/solicitudes/index.php
// ===============================
require_once __DIR__ . '/../_init.php';

$user = current_user();
if (!$user) {
    json_error('No autenticado', 401);
}

$method = $_SERVER['REQUEST_METHOD'];

/**
 * GET  → listar solicitudes del grupo del delegado (o por ?grupo_id= para admin/profesor)
 * POST → crear nueva solicitud (solo delegado)
 */

if ($method === 'GET') {
    $grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;

    if ($grupo_id === 0 && $user['rol'] === 'Delegado') {
        // obtener grupo del delegado
        $stmt = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id = ? LIMIT 1");
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $g = $stmt->get_result()->fetch_assoc();
        if ($g) $grupo_id = (int)$g['id'];
    }

    if ($grupo_id === 0) json_ok([]); // sin grupo definido

    $sql = "
    SELECT s.id, p.nombre AS producto, s.cantidad, s.estado, DATE(s.fecha_solicitud) AS fecha
    FROM solicitudes s
    JOIN productos p ON p.id = s.producto_id
    WHERE s.grupo_id = ?
    ORDER BY s.fecha_solicitud DESC, s.id DESC
    ";
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $grupo_id);
    $st->execute();
    $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    json_ok($rows);
}

if ($method === 'POST') {
    if ($user['rol'] !== 'Delegado') json_error('Solo delegados pueden crear', 403);

    // obtener grupo del delegado
    $stmt = $mysqli->prepare("SELECT id FROM grupos WHERE delegado_id = ? LIMIT 1");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $g = $stmt->get_result()->fetch_assoc();
    if (!$g) json_error('Debes crear tu grupo primero', 409);
    $grupo_id = (int)$g['id'];

    $data = array_merge($_POST, input_json());
    $producto_id = (int)($data['producto_id'] ?? 0);
    $cantidad    = (int)($data['cantidad'] ?? 0);

    if ($producto_id <= 0 || $cantidad <= 0) json_error('Datos inválidos', 422);

    $ins = $mysqli->prepare("INSERT INTO solicitudes (grupo_id, producto_id, cantidad) VALUES (?,?,?)");
    $ins->bind_param('iii', $grupo_id, $producto_id, $cantidad);
    if (!$ins->execute()) json_error('No se pudo crear la solicitud', 500);

    json_ok(['success'=>true, 'id'=>(int)$ins->insert_id], 201);
}

json_error('Método no permitido', 405);
