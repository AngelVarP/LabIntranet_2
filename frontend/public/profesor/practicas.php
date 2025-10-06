<?php
session_start();
$base_url = "http://localhost/LabIntranet_2/frontend/";

// Bloquear acceso si no está logueado como profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: {$base_url}public/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesor - Gestión de Prácticas</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/table.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profesor.css">
</head>
<body>

    <div class="dashboard-container">
        <!-- ===== SIDEBAR ===== -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Intranet Logo" class="logo">
                <h3>Profesor</h3>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard_profesor.php">Dashboard</a></li>
                    <li><a href="practicas_profesor.php" class="active">Prácticas</a></li>
                    <li><a href="inventario_profesor.php">Inventario</a></li>
                    <li><a href="solicitudes_profesor.php">Peticiones</a></li>
                    <li><a href="reportes_profesor.php">Reportes</a></li>
                    <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="main-content">
            <h1>Gestión de Prácticas de Laboratorio</h1>
            
            <div class="form-panel">
                <h2>Crear Nueva Práctica</h2>
                <form id="form-crear-practica" method="POST" action="procesar_practica.php">
                    
                    <div class="form-group">
                        <label for="titulo">Título de la Práctica:</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción y Objetivo:</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>

                    <hr>
                    <h3>Materiales Requeridos</h3>
                    <div id="materiales-container"></div>

                    <button type="button" id="btn-agregar-material" class="btn btn-secondary" style="margin-top: 15px;">
                        + Agregar Material
                    </button>

                    <button type="submit" class="btn-primary" style="margin-top: 30px;">
                        Guardar y Publicar Práctica
                    </button>
                </form>
            </div>

            <div style="margin-top: 40px;">
                <h2>Mis Prácticas</h2>
                <div class="topbar">
                    <div class="actions"> 
                        <input type="text" id="buscar-practica" placeholder="Buscar práctica..." style="width: 300px;">
                    </div>
                </div>

                <table id="tabla-practicas" class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center;">Cargando prácticas...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/practicas-profesor.js"></script>
</body>
</html>
