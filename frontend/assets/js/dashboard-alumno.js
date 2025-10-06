// assets/js/dashboard-alumno.js
document.addEventListener('DOMContentLoaded', async () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  if (!TOKEN) {
    alert("No tienes sesión iniciada");
    window.location.href = "../login.html";
    return;
  }

  // --- Referencias DOM ---
  const alertInfo = document.querySelector(".alert-info");
  const alertWarning = document.querySelector(".alert-warning");
  const cardAsignadas = document.querySelector(".cards .card:nth-child(1) .big-number");
  const cardCompletadas = document.querySelector(".cards .card:nth-child(2) .big-number");
  const cardSolicitudes = document.querySelector(".cards .card:nth-child(3) .big-number");
  const chartCanvas = document.getElementById("practicas-chart");
  const chartLegend = document.getElementById("chart-legend");

  let practicas = [];

  async function cargarPracticas() {
    try {
      const res = await fetch(`${API_BASE}/practicas/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();

      if (!Array.isArray(data)) {
        console.warn("Error al cargar prácticas", data);
        return [];
      }
      return data;
    } catch (err) {
      console.error("Error al cargar prácticas", err);
      return [];
    }
  }

  async function cargarSolicitudes() {
    try {
      const res = await fetch(`${API_BASE}/solicitudes/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();
      if (Array.isArray(data)) return data;
      return [];
    } catch (err) {
      console.error("Error al cargar solicitudes", err);
      return [];
    }
  }

  async function initDashboard() {
    practicas = await cargarPracticas();
    const solicitudes = await cargarSolicitudes();

    // --- Contadores ---
    const totalAsignadas = practicas.length;
    const completadas = practicas.filter(p => p.estado && p.estado.toLowerCase() === "completada").length;
    const pendientes = practicas.filter(p => !p.estado || p.estado.toLowerCase() !== "completada").length;

    cardAsignadas.textContent = totalAsignadas;
    cardCompletadas.textContent = completadas;
    cardSolicitudes.textContent = solicitudes.length;

    // --- Alerts ---
    alertInfo.textContent = `Tienes ${pendientes} práctica(s) pendiente(s) de entrega.`;
    alertWarning.textContent = `Tienes ${solicitudes.length} solicitudes pendientes por revisar.`;

    // --- Gráfico ---
    renderChart(completadas, pendientes);
  }

  function renderChart(completadas, pendientes) {
    if (!window.Chart) {
      console.warn("Chart.js no está cargado, se omitirá el gráfico");
      return;
    }

    const ctx = document.getElementById("practicas-chart").getContext("2d");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Completadas", "Pendientes"],
        datasets: [
          {
            data: [completadas, pendientes],
            backgroundColor: ["#4CAF50", "#FFC107"]
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    chartLegend.innerHTML = `
      <span style="color:#4CAF50;">● Completadas (${completadas})</span> &nbsp;&nbsp;
      <span style="color:#FFC107;">● Pendientes (${pendientes})</span>
    `;
  }

  initDashboard();
});
