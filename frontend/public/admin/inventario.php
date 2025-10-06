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
  <title>Inventario | Panel de Administración</title>

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
        <h2>Admin</h2>
      </div>
      <nav>
        <ul>
          <li><a href="dashboard.php">🏠 Dashboard</a></li>
          <li><a href="usuarios.php">👥 Usuarios</a></li>
          <li><a href="inventario.php" class="active">📦 Inventario</a></li>
          <li><a href="peticiones.php">📝 Peticiones</a></li>
          <li><a href="reportes.php">📊 Reportes</a></li>
          <li><a href="<?php echo $base_url; ?>public/login.php" class="logout">🚪 Cerrar sesión</a></li>
        </ul>
      </nav>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">
      <header class="topbar">
        <h1>Inventario</h1>
        <div class="actions">
          <input type="text" id="search-bar" placeholder="Buscar producto...">
          <select id="filter-category">
            <option value="todos">Todas las categorías</option>
            <option value="Reactivos">Reactivos</option>
            <option value="Vidriería">Vidriería</option>
            <option value="Equipos">Equipos</option>
          </select>
          <button class="btn-primary" id="btn-export">⬇️ Exportar</button>
          <button class="btn-primary" id="btn-add-product">+ Agregar producto</button>
        </div>
      </header>

      <section class="table-section">
        <table class="table" id="inventory-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Producto</th>
              <th>Categoría</th>
              <th>Unidad</th>
              <th>Ubicación</th>
              <th>Stock</th>
              <th>Mínimo</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>P001</td>
              <td>Ácido Clorhídrico</td>
              <td>Reactivos</td>
              <td>L</td>
              <td>Almacén Principal</td>
              <td>5</td>
              <td>10</td>
              <td><span class="stock low">Bajo</span></td>
              <td>
                <button class="btn-edit">✏️</button>
                <button class="btn-delete">🗑️</button>
              </td>
            </tr>
            <tr>
              <td>P002</td>
              <td>Matraz Aforado 250ml</td>
              <td>Vidriería</td>
              <td>unid</td>
              <td>Laboratorio Química 1</td>
              <td>25</td>
              <td>15</td>
              <td><span class="stock medium">Medio</span></td>
              <td>
                <button class="btn-edit">✏️</button>
                <button class="btn-delete">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <!-- Modal agregar/editar -->
  <div class="modal" id="product-modal">
    <div class="modal-content">
      <h2 id="modal-title">Agregar producto</h2>
      <form id="product-form" method="POST" action="procesar_producto.php">
        <label>ID:</label>
        <input type="text" id="product-id" name="product_id" placeholder="P003" required>

        <label>Nombre:</label>
        <input type="text" id="product-name" name="product_name" placeholder="Nombre del producto" required>

        <label>Categoría:</label>
        <select id="product-category" name="product_category" required>
          <option value="Reactivos">Reactivos</option>
          <option value="Vidriería">Vidriería</option>
          <option value="Equipos">Equipos</option>
        </select>

        <label>Unidad de medida:</label>
        <input type="text" id="product-unit" name="product_unit" placeholder="Ej: L, ml, g, unid" required>

        <label>Ubicación:</label>
        <input type="text" id="product-location" name="product_location" placeholder="Ej: Almacén Principal" required>

        <label>Stock actual:</label>
        <input type="number" id="product-stock" name="product_stock" min="0" placeholder="Cantidad disponible" required>

        <label>Stock mínimo:</label>
        <input type="number" id="product-min" name="product_min" min="0" placeholder="Cantidad mínima" required>

        <div class="modal-actions">
          <button type="submit" class="btn-primary">Guardar</button>
          <button type="button" class="btn-secondary" id="btn-close-modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?php echo $base_url; ?>assets/js/inventario.js"></script>
</body>
</html>
