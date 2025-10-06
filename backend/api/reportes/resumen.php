<?php
// backend/api/reportes/resumen.php
require_once __DIR__ . '/../_init.php';
$u = require_token();

if (!in_array($u['rol'], ['Administrador', 'Profesor'])) {
    json_error('No autorizado', 403);
}

// ===== 1. Contadores =====
$usuarios = $mysqli->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$productos = $mysqli->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
$pendientes = $mysqli->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado='Pendiente'")->fetch_assoc()['total'];
$aprobadas = $mysqli->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado='Aprobada'")->fetch_assoc()['total'];

// ===== 2. Consumo inventario (productos más pedidos) =====
$consumo = [];
$res = $mysqli->query("SELECT p.nombre, SUM(s.cantidad) as total
                       FROM solicitudes s
                       JOIN productos p ON p.id=s.producto_id
                       GROUP BY p.id
                       ORDER BY total DESC
                       LIMIT 6");
while($r = $res->fetch_assoc()) $consumo[] = $r;

// ===== 3. Solicitudes por rol =====
$porRol = [];
$res2 = $mysqli->query("SELECT u.rol_id, r.nombre as rol, COUNT(s.id) as total
                        FROM solicitudes s
                        JOIN grupos g ON g.id = s.grupo_id
                        JOIN usuarios u ON u.id = g.delegado_id
                        JOIN roles r ON r.id = u.rol_id
                        GROUP BY u.rol_id");
while($r = $res2->fetch_assoc()) $porRol[] = $r;

json_ok([
    'usuarios' => (int)$usuarios,
    'productos' => (int)$productos,
    'pendientes' => (int)$pendientes,
    'aprobadas' => (int)$aprobadas,
    'consumo' => $consumo,
    'porRol' => $porRol
]);
