<?php
// Archivo: backend/general/login.php
session_start();

// Configuración de la base de datos
$host = 'localhost';
$db = 'labintranet';
$user = 'root';
$pass = '';

// Conexión a la base de datos
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}

// Obtener datos del POST
$code = $_POST['code'] ?? '';
$password = $_POST['password'] ?? '';

// Validar datos
if (empty($code) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

// Consulta segura
$stmt = $conn->prepare('SELECT id, nombre, password, rol FROM usuarios WHERE code = ?');
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verifica la contraseña en texto plano (sin hash)
    if ($password === $row['password']) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['rol'] = $row['rol'];
        echo json_encode(['success' => true, 'message' => 'Login correcto', 'rol' => $row['rol']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
}
$stmt->close();
$conn->close();
?>
