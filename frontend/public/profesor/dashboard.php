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
    <title>Profesor - Panel Principal</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">
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
                    <li><a href="dashboard_profesor.php" class="active">Dashboard</a></li>
                    <li><a href="practicas_profesor.php">Prácticas</a></li>
                    <li><a href="inventario_profesor.php">Inventario</a></li>
                    <li><a href="solicitudes_profesor.php">Peticiones</a></li>
                    <li><a href="reportes_profesor.php">Reportes</a></li>
                    <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="main-content">
            <h1>Panel del Profesor</h1>

            <div class="alerts">
                <div class="alert alert-info">
                    Tienes 2 peticiones aprobadas recientemente.
                </div>
                <div class="alert alert-warning">
                    Tienes 4 prácticas pendientes de finalizar.
                </div>
            </div>

            <div class="cards">
                <div class="card">
                    <h3>Prácticas Publicadas</h3>
                    <p class="big-number" id="kpi-practicas">12</p>
                </div>
                <div class="card">
                    <h3>Peticiones Pendientes</h3>
                    <p class="big-number" id="kpi-peticiones">3</p>
                </div>
                <div class="card">
                    <h3>Material Consumido (Mes)</h3>
                    <p class="big-number" id="kpi-material">85</p>
                </div>
            </div>

            <div class="quick-links">
                <a href="practicas_profesor.php" class="quick-card">
                    Crear Práctica
                </a>
                <a href="solicitudes_profesor.php" class="quick-card">
                    Generar Solicitud
                </a>
                <a href="inventario_profesor.php" class="quick-card">
                    Ver Inventario
                </a>
            </div>

            <div class="chart-section">
                <div class="chart-card">
                    <h3>Estado de Mis Peticiones</h3>
                    <canvas id="peticiones-chart"></canvas>
                    <div id="chart-legend" style="text-align: center; margin-top: 15px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/dashboard.js"></script> 
</body>
</html>
