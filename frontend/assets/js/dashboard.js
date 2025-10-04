document.addEventListener("DOMContentLoaded", () => {
  // === Datos simulados (para demo, luego vendrán del backend)
  const stockLow = 3; // productos con stock bajo
  const pendingRequests = 5;
  const totalUsers = 128;
  const totalProducts = 345;
  const totalPending = 17;

  // === Insertar datos en tarjetas y alertas
  document.getElementById("alert-stock").textContent = `⚠️ Hay ${stockLow} productos con stock bajo`;
  document.getElementById("alert-peticiones").textContent = `🔔 Tienes ${pendingRequests} peticiones pendientes`;
  document.getElementById("kpi-users").textContent = totalUsers;
  document.getElementById("kpi-products").textContent = totalProducts;
  document.getElementById("kpi-pending").textContent = totalPending;

  // === Chart Peticiones por estado
  const ctx = document.getElementById("chart-peticiones").getContext("2d");
  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Pendiente", "Aprobada", "Rechazada"],
      datasets: [{
        data: [pendingRequests, 58, 10],
        backgroundColor: ["#f39c12", "#2ecc71", "#e74c3c"]
      }]
    },
    options: { responsive: true }
  });
});
