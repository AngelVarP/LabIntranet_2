<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['code']);
    $contraseña = trim($_POST['password']);
    $rol = trim($_POST['rol']);

    if (empty($rol)) {
        echo json_encode(["success" => false, "message" => "Debe seleccionar un rol"]);
        exit;
    }

    $tabla = ($rol === "delegado") ? "Delegado" :
             (($rol === "instructor") ? "Instructor" : "Administrador");

    try {
        $stmt = $pdo->prepare("SELECT * FROM $tabla WHERE correo_electronico = ? AND contraseña = ?");
        $stmt->execute([$correo, $contraseña]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = $rol;
            echo json_encode(["success" => true, "rol" => $rol]); 
        } else {
            echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error en la consulta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Acceso no permitido"]);
}
