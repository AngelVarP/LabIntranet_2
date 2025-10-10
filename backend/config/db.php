<?php
// backend/config/db.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';            // XAMPP por defecto
$DB_NAME = 'labintranet'; // la BD que creaste

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success'=>false,'message'=>'Error de conexión BD']);
    exit;
}
$mysqli->set_charset('utf8mb4');
