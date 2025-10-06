<?php
require_once __DIR__ . '/../_init.php';
$u = require_roles(['Profesor','Administrador']);
$out = [];

/* ====== Datos principales ====== */
$stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM practicas WHERE profesor_id=?");
$stmt->bind_param("i",$u['id']);
$stmt->execute();
$out['practicas_publicadas'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$res = $mysqli->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado='Pendiente'");
$out['peticiones_pendientes'] = $res->fetch_assoc()['total'] ?? 0;

/* Material consumido este mes */
$res = $mysqli->query("
    SELECT COALESCE(SUM(pm.cantidad),0) as total
    FROM practicas_materiales pm
    JOIN practicas p ON p.id=pm.practica_id
    WHERE p.profesor_id={$u['id']} 
      AND MONTH(p.created_at)=MONTH(CURRENT_DATE()) 
      AND YEAR(p.created_at)=YEAR(CURRENT_DATE())
");
$out['material_mes'] = $res->fetch_assoc()['total'] ?? 0;

/* Peticiones por estado para la gráfica */
$res = $mysqli->query("
    SELECT estado, COUNT(*) as total 
    FROM solicitudes 
    GROUP BY estado
");
$chart = [];
while($r = $res->fetch_assoc()) $chart[]=$r;
$out['peticiones_chart']=$chart;

json_ok($out);
