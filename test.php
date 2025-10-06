<?php
require_once __DIR__ . '/config/database.php';

try {
    // Conexión usando la clase Database
    $conn = Database::connect();

    echo "✅ Conexión exitosa a la base de datos.<br>";

    // Consulta para listar tablas
    $query = $conn->query("SHOW TABLES");
    $tables = $query->fetchAll(PDO::FETCH_NUM);

    if (count($tables) > 0) {
        echo "Tablas en la base de datos:<br><ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table[0]) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ No hay tablas en la base de datos.";
    }

} catch (PDOException $e) {
    echo "❌ No se pudo conectar: " . $e->getMessage();
}
?>