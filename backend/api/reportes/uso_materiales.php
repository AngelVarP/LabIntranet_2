<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador','Profesor']);

$practica = $_GET['practica'] ?? 'all';
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;

$sql = "SELECT p.nombre AS producto, SUM(pm.cantidad) AS total_usado
        FROM practicas_materiales pm
        JOIN productos p ON p.id = pm.producto_id
        JOIN practicas pr ON pr.id = pm.practica_id
        WHERE pr.profesor_id = ?";
$params = [$u['id']];
$types = "i";

if($practica !== 'all'){
    $sql .= " AND pr.id = ?";
    $params[] = $practica;
    $types .= "i";
}
if($inicio){
    $sql .= " AND pr.fecha >= ?";
    $params[] = $inicio;
    $types .= "s";
}
if($fin){
    $sql .= " AND pr.fecha <= ?";
    $params[] = $fin;
    $types .= "s";
}

$sql .= " GROUP BY p.id, p.nombre ORDER BY total_usado DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
json_ok($out);
