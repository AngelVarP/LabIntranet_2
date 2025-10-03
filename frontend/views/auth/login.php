<?php
$base_url = "http://localhost/LabIntranet/"; // Base URL para enlazar los archivos
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laboratorio Intranet</title>
    <!-- Enlace a los estilos CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/styles.css">
</head>

<body>
    <header>
        <div class="header-container">
            <img src="<?php echo $base_url; ?>assets/images/logo.png" alt="Logo Lab Intranet" class="logo">
            <div class="user-info">
                <h2>Ingreso al Sistema de Inventario</h2>
            </div>
        </div>
    </header>

    <main>
        <section class="login-container">
            <form id="login-form" class="login-form" method="POST" action="controllers/AuthController.php">
                <h1>Login</h1>
                <div class="input-group">
                    <label for="email">Correo</label>
                    <input type="text" id="email" name="email" placeholder="Ingresa tu correo" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
                <div class="role-selection">
                    <label for="role">Selecciona tu rol</label>
                    <select id="role" name="role" required>
                        <option value="delegado">Delegado</option>
                        <option value="instructor">Instructor</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
                <br />
                <button type="submit" class="btn-login">Iniciar Sesión</button>
                <!-- Texto debajo del botón -->
                <p class="register-text">
                    ¿No tienes cuenta?
                    <a href="<?php echo $base_url; ?>views/auth/register.php">Regístrate aquí</a>
                </p>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Laboratorio Intranet</p>
    </footer>

    <!-- Enlace al script JS -->
    <script src="<?php echo $base_url; ?>assets/js/script.js"></script>
</body>

</html>