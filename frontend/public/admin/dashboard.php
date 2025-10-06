<?php
session_start();
$base_url = "http://localhost/LabIntranet_2/frontend/";

// Bloquear acceso si no está logueado como admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: {$base_url}public/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | Panel de Administración</title>

  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Logo del Laboratorio" class="logo">
        <h2>Admin</h2>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
          <li><a href="usuarios.php">👥 Usuarios</a></li>
          <li><a href="inventario.php">📦 Inventario</a></li>
          <li><a href="peticiones.php">📝 Peticiones</a></li>
          <li><a href="reportes.php">📊 Reportes</a></li>
          <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">🚪 Cerrar sesión</a></li>
        </ul>
      </nav>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
      <header class="topbar">
        <h1>Panel de Administración</h1>
      </header>

      <!-- ALERTAS -->
      <section class="alerts">
        <div class="alert alert-warning" id="alert-stock">⚠️ Hay 3 productos con stock bajo</div>
        <div class="alert alert-info" id="alert-peticiones">🔔 Tienes 5 peticiones pendientes</div>
      </section>

      <!-- TARJETAS KPI -->
      <section class="cards">
        <div class="card">
          <h
