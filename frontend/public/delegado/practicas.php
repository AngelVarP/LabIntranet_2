<?php
session_start();
$base_url = "http://localhost/LabIntranet_2/frontend/";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Prácticas | Delegado</title>

  <!-- ✅ Rutas dinámicas -->
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/table.css" />
</head>
<body>
  <div class="dashboard-container">
    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Logo del Laboratorio" class="logo">
        <h2>Delegado</h2>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard.php">🏠 Dashboard</a></li>
          <li><a href="solicitudes.php">📝 Solicitudes</a></li>
          <li><a href="practicas.php" class="active">🔬 Prácticas</a></li>
          <li><a href="grupo.php">👥 Mi Grupo</a></li>
          <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">🚪 Cerrar sesión</a></li>
        </ul>
      </nav>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
      <header class="topbar">
        <h1>Mis Prácticas</h1>
      </header>

      <section class="table-section">
        <table class="table" id="practicas-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Fecha</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody id="practicas-body">
            <tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <!-- ✅ Script dinámico -->
  <script src="<?php echo $base_url; ?>assets/js/delegado.js"></script>
</body>
</html>
