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
    <title>Profesor - Solicitudes de Materiales</title>
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
                    <li><a href="practicas_profesor.php">Prácticas</a></li>
                    <li><a href="inventario_profesor.php">Inventario</a></li>
                    <li><a href="solicitudes_profesor.php" class="active">Peticiones</a></li>
                    <li><a href="reportes_profesor.php">Reportes</a></li>
                    <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="main-content">
            <h1>Generar y Revisar Peticiones de Materiales</h1>
            
            <div class="form-panel">
                <h2>Crear Nueva Petición</h2>
                <form id="form-crear-solicitud" method="POST" action="procesar_solicitud.php">
                    
                    <div class="form-group">
                        <label for="practica-select">Práctica Asociada:</label>
                        <select id="practica-select" name="practica_id" class="form-control" required>
                            <option value="">-- Seleccione una práctica --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha-requerida">Fecha de Uso Requerida:</label>
                        <input type="date" id="fecha-requerida" name="fecha_requerida" class="form-control" required>
                    </div>

                    <hr>
                    <h3>Materiales a Solicitar</h3>
                    <div id="materiales-solicitud-container"></div>

                    <button type="button" id="btn-agregar-material-solicitud" class="btn btn-secondary" style="margin-top: 15px;">
                        + Agregar Material
                    </button>

                    <button type="submit" class="btn-primary" style="margin-top: 30px;">
                        Enviar Petición al Administrador
                    </button>
                </form>
            </div>

            <div style="margin-top: 40px;">
                <h2>Historial de Mis Peticiones</h2>
                <div class="topbar">
                    <div class="actions"> 
                        <input type="text" id="buscar-solicitud" placeholder="Buscar por práctica o ID..." style="width: 300px;">
                        <select id="filtrar-estado" style="width: 180px;"> 
                            <option value="">Todos los Estados</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Aprobada">Aprobada</option>
                            <option value="Rechazada">Rechazada</option>
                        </select>
                    </div>
                </div>

                <table id="tabla-solicitudes" class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Práctica</th>
                            <th>Fecha Uso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center;">Cargando historial de peticiones...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/solicitudes-profesor.js"></script>
</body>
</html>
