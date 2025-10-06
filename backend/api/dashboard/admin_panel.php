<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Administrador']); // Solo admin puede acceder

// Total de usuarios
$usuarios = $mysqli->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'] ?? 0;

// Total de productos
$productos = $mysqli->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'] ?? 0;

// Peticiones pendientes
$pendientes = $mysqli->query("SELECT COUNT(*) AS total FROM solicitudes WHERE estado='Pendiente'")->fetch_assoc()['total'] ?? 0;

// Stock bajo (ejemplo: <=10)
$stock_bajo = $mysqli->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 10")->fetch_assoc()['total'] ?? 0;

// Peticiones por estado para el gráfico
$chart = [];
$res = $mysqli->query("SELECT estado, COUNT(*) AS total FROM solicitudes GROUP BY estado");
while($r = $res->fetch_assoc()) $chart[] = $r;

// Respuesta
json_ok([
  "total_usuarios" => $usuarios,
  "total_productos" => $productos,
  "peticiones_pendientes" => $pendientes,
  "alert_stock" => $stock_bajo,
  "peticiones_chart" => $chart
]);
