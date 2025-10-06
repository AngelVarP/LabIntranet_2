<?php
    $base_url = "http://localhost/LabIntranet_2/frontend/";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro | Laboratorio</title>

  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/normalize.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/main.css" />
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/auth.css" />
  <style>
    /* Pequeños ajustes visuales para los checkboxes */
    .checkbox-list {
      text-align: left;
      margin-top: 8px;
      padding-left: 10px;
    }
    .checkbox-list label {
      display: block;
      margin-bottom: 5px;
      font-size: 0.95rem;
      cursor: pointer;
    }
    .checkbox-list input[type="checkbox"] {
      margin-right: 8px;
      accent-color: #F6AE2D;
    }
    .input-group select {
        width: 100%;
        padding: 10px 12px 10px 40px; /* ← mismo padding que los inputs */
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        background: rgba(255,255,255,0.08);
        color: #6c6868ff;
        font-size: 0.95rem;
        appearance: none; /* quita el estilo del navegador */
        }

        .input-group select:focus {
        outline: none;
        border-color: #86BBD8;
        box-shadow: 0 0 5px rgba(134,187,216,0.4);
        }
  </style>
</head>
<body>
  <div class="background-deco">
    <span></span><span></span><span></span>
  </div>

  <main class="auth-container">
    <div class="auth-box glass">
      <img src="<?php echo $base_url; ?>assets/img/logoLab.png" alt="Logo del laboratorio" class="logo" />

      <h1>Registro</h1>
      <p class="subtitle">Solicita tu acceso al sistema</p>

      <form id="register-form" method="POST" action="procesar_solicitud.php">
        <!-- Rol -->
        <div class="input-group">
          <svg class="icon" viewBox="0 0 24 24">
            <path d="M3 3h18v2H3V3zm0 6h18v2H3V9zm0 6h18v2H3v-2zm0 6h18v2H3v-2z"/>
          </svg>
          <select id="rol" name="rol" required>
            <option value="">Seleccione rol</option>
            <option value="delegado">Delegado</option>
            <option value="instructor">Instructor</option>
          </select>
        </div>

        <!-- Datos personales -->
        <div class="input-group">
          <input type="text" name="nombres" placeholder="Nombres" required />
        </div>
        <div class="input-group">
          <input type="text" name="apellidos" placeholder="Apellidos" required />
        </div>
        <div class="input-group">
          <input type="email" name="correo_electronico" placeholder="Correo electrónico" required />
        </div>
        <div class="input-group">
          <input type="password" name="contraseña" placeholder="Contraseña" required />
        </div>
        <div class="input-group">
          <input type="text" name="telefono" placeholder="Teléfono" required />
        </div>
        <div class="input-group">
          <input type="date" name="fechaNacimiento" placeholder="Fecha de nacimiento" required />
        </div>
        <div class="input-group">
          <input type="text" name="dni" placeholder="DNI" required />
        </div>

        <!-- Contenedor de secciones -->
        <div class="input-group" id="seccion-container">
          <!-- Aquí se inyectan los checkboxes -->
        </div>

        <button type="submit" class="btn-primary large">Enviar Solicitud</button>
        <a href="<?php echo $base_url; ?>public/login.php" class="forgot-link">¿Ya tienes cuenta? Inicia sesión</a>
      </form>
    </div>
  </main>

  <script>
    let secciones = [];

    fetch("http://localhost/LabIntranet_2/controllers/GetSeccion.php")
      .then(response => response.json())
      .then(data => {
        secciones = data;
      })
      .catch(error => console.error("Error al cargar secciones:", error));

    const rolSelect = document.getElementById('rol');
    const seccionContainer = document.getElementById('seccion-container');

    rolSelect.addEventListener('change', () => {
      const rol = rolSelect.value;
      seccionContainer.innerHTML = '';

      if (!rol) return;

      const label = document.createElement('label');
      label.textContent = rol === 'delegado'
        ? 'Seleccione las secciones donde participará:'
        : 'Seleccione su sección asignada:';
      seccionContainer.appendChild(label);

      const div = document.createElement('div');
      div.classList.add('checkbox-list');

      secciones.forEach(s => {
        const item = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = rol === 'delegado' ? 'checkbox' : 'radio';
        checkbox.name = rol === 'delegado' ? 'id_secciones[]' : 'id_seccion';
        checkbox.value = s.id_seccion;
        item.appendChild(checkbox);
        item.appendChild(document.createTextNode(s.nombre_completo)); // Muestra nombre + letra
        div.appendChild(item);
      });

      seccionContainer.appendChild(div);
    });
  </script>

</body>
</html>
