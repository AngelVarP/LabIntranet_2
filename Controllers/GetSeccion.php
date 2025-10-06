<?php
require_once __DIR__ . '/../models/Seccion.php';

header('Content-Type: application/json');

$secciones = Seccion::obtenerSecciones();

echo json_encode($secciones);