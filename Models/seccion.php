<?php
require_once __DIR__ . '/../config/database.php';

class Seccion {
    public static function obtenerSecciones() {
        try {
            $conn = Database::connect();

            $stmt = $conn->query("SELECT id_seccion, nombre, letra FROM Seccion ORDER BY nombre, letra ASC");
            $secciones = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['nombre_completo'] = $row['nombre'] . " - " . $row['letra'];
                $secciones[] = $row;
            }

            return $secciones;

        } catch (PDOException $e) {
            return [];
        }
    }
}
