<?php
  // Puedes ajustar esta base URL según tu estructura
  $base_url = "http://localhost/LabIntranet_2/frontend/";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delegado - Dashboard</title>

  <!-- ✅ Estilos con rutas dinámicas -->
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css">
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css">
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/table.css">
</head>

<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Logo" class="logo">
        <h3>Delegado</h3>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard.php" class="active">Dashboard</a></li>
          <li><a href="practicas.php">Mis Prácticas</a></li>
          <li><a href="solicitudes.php">Solicitudes</a></li>
          <li><a href="grupo.php">Mi Grupo</a></li>
          <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">Cerrar Sesión</a></li>
        </ul>
      </nav>
    </div>

    <!-- Main content -->
    <div class="main-content">
      <h1>Bienvenido, 
        <?php 
          // Ejemplo: podrías mostrar el nombre del delegado logueado
          echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Delegado';
        ?>
      </h1>

      <!-- Resumen rápido -->
      <div class="alerts">
        <div class="alert alert-info">
          Este es tu panel de control. Desde aquí puedes ver un resumen de tu grupo y gestionar solicitudes.
        </div>
      </div>

      <section class="card">
        <h2>Resumen de mi Grupo</h2>
        <p><strong>Grupo:</strong> <span id="grupo-nombre">Cargando...</span></p>
        <p><strong>Sección:</strong> <span id="grupo-seccion">Cargando...</span></p>
        <p><strong>Alumnos inscritos:</strong> <span id="total-alumnos">0</span></p>
        <a href="grupo.php" class="btn-primary">Ir a gestión completa</a>
      </section>

      <section class="card">
        <h2>Últimas solicitudes</h2>
        <table class="data-table">
          <thead>
            <tr>
              <th>Insumo</th>
              <th>Cantidad</th>
              <th>Estado</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody id="tabla-solicitudes">
            <tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>
          </tbody>
        </table>
        <a href="solicitudes.php" class="btn-primary">Ver todas las solicitudes</a>
      </section>
    </div>
  </div>

  <!-- ✅ Script dinámico -->
  <script src="<?php echo $base_url; ?>assets/js/delegado.js"></script>
</body>
</html>
