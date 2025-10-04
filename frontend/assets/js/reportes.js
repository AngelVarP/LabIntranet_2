document.addEventListener("DOMContentLoaded", () => {
  // === Datos de ejemplo (luego vendrán de tu backend)
  const countUsers = 128;
  const countProducts = 345;
  const countPending = 17;
  const countApproved = 58;

  // Insertar datos en las tarjetas
  document.getElementById("count-users").textContent = countUsers;
  document.getElementById("count-products").textContent = countProducts;
  document.getElementById("count-pending").textContent = countPending;
  document.getElementById("count-approved").textContent = countApproved;

  // === Chart: Consumo Inventario ===
  const ctxInv = document.getElementById("chart-inventario").getContext("2d");
  new Chart(ctxInv, {
    type: "bar",
    data: {
      labels: ["Reactivos", "Vidriería", "Equipos", "Otros"],
      datasets: [{
        label: "Cantidad utilizada",
        data: [120, 80, 45, 30],
        backgroundColor: ["#F6AE2D", "#86BBD8", "#2ecc71", "#e74c3c"]
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  // === Chart: Solicitudes por rol ===
  const ctxRoles = document.getElementById("chart-roles").getContext("2d");
  new Chart(ctxRoles, {
    type: "doughnut",
    data: {
      labels: ["Profesor", "Delegado", "Alumno"],
      datasets: [{
        data: [45, 25, 30],
        backgroundColor: ["#F6AE2D", "#86BBD8", "#2ecc71"]
      }]
    },
    options: { responsive: true }
  });

  // === Botones de exportación (simulados) ===
  document.getElementById("btn-export-pdf").addEventListener("click", () => {
    alert("Exportación a PDF simulada (aquí se integrará con backend).");
  });
  document.getElementById("btn-export-excel").addEventListener("click", () => {
    alert("Exportación a Excel simulada (aquí se integrará con backend).");
  });
});
