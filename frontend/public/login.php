<?php
    $base_url = "http://localhost/LabIntranet_2/frontend/";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión | Laboratorio</title>

  <!-- ✅ Enlaces usando PHP -->
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/auth.css" />
</head>
<body>
  <div class="background-deco">
    <span></span><span></span><span></span>
  </div>

  <main class="auth-container">
    <div class="auth-box glass">
      <!-- ✅ Logo con PHP -->
      <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Logo del laboratorio" class="logo" />

      <h1>Bienvenido</h1>
      <p class="subtitle">Accede al sistema de gestión del laboratorio</p>

      <!-- ✅ Formulario con acción definida -->
      <form id="login-form" method="POST" action="procesar_login.php">
        <div class="input-group">
          <svg class="icon" viewBox="0 0 24 24">
            <path d="M3 17h2v-2H3v2zm0-4h2v-2H3v2zm0-4h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z"/>
          </svg>
          <input type="text" id="code" name="code" placeholder="Correo electronico" required />
        </div>

        <div class="input-group">
          <svg class="icon" viewBox="0 0 24 24">
            <path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm6-7V7a6 6 0 0 0-12 0v3H4v12h16V10h-2zM8 7a4 4 0 0 1 8 0v3H8V7z"/>
          </svg>
          <input type="password" id="password" name="password" placeholder="Contraseña" required />
        </div>

        <button type="submit" class="btn-primary large">Ingresar</button>
        <!-- ✅ Enlace corregido -->
        <a href="<?php echo $base_url; ?>public/register.php" class="forgot-link">Registrate aquí</a>
      </form>
    </div>
  </main>

  <!-- ✅ Script con PHP -->
  <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
</body>
</html>
