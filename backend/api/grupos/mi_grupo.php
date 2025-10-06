<?php
require_once __DIR__ . '/../_init.php';
$u = require_token();

header('Content-Type: application/json; charset=utf-8');

if ($u['rol'] === 'Delegado') {
    $sql = "SELECT g.id, g.nombre, u.nombre AS delegado
            FROM grupos g
            JOIN usuarios u ON u.id = g.delegado_id
            WHERE g.delegado_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $u['id']);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc();
} else {
    $sql = "SELECT g.id, g.nombre, u.nombre AS delegado
            FROM grupo_alumnos ga
            JOIN grupos g ON g.id = ga.grupo_id
            JOIN usuarios u ON u.id = g.delegado_id
            WHERE ga.alumno_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $u['id']);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc();
}

if (!$grupo) {
    echo json_encode(null);
    exit;
}

$sql = "SELECT usu.id, usu.codigo, usu.nombre, usu.correo
        FROM grupo_alumnos ga
        JOIN usuarios usu ON usu.id = ga.alumno_id
        WHERE ga.grupo_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $grupo['id']);
$stmt->execute();
$integrantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$grupo['miembros'] = $integrantes; // 👈 AHORA el nombre coincide con delegado.js

echo json_encode($grupo);
