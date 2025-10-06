document.addEventListener("DOMContentLoaded", async () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  try {
    const res = await fetch(`${API_BASE}/dashboard/admin_panel.php`, {
      headers: { Authorization: `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    // ==== Actualizar KPIs ====
    document.getElementById("kpi-users").textContent = data.total_usuarios ?? 0;
    document.getElementById("kpi-products").textContent = data.total_productos ?? 0;
    document.getElementById("kpi-pending").textContent = data.peticiones_pendientes ?? 0;

    // ==== Actualizar alertas ====
    if (data.alert_stock > 0) {
      document.getElementById("alert-stock").textContent = `⚠️ Hay ${data.alert_stock} productos con stock bajo`;
    } else {
      document.getElementById("alert-stock").style.display = "none";
    }

    if (data.peticiones_pendientes > 0) {
      document.getElementById("alert-peticiones").textContent = `🔔 Tienes ${data.peticiones_pendientes} peticiones pendientes`;
    } else {
      document.getElementById("alert-peticiones").style.display = "none";
    }

    // ==== Gráfico de peticiones ====
    if (data.peticiones_chart && Array.isArray(data.peticiones_chart)) {
      const ctx = document.getElementById("chart-peticiones").getContext("2d");
      const labels = data.peticiones_chart.map(item => item.estado);
      const valores = data.peticiones_chart.map(item => parseInt(item.total));

      new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: labels,
          datasets: [{
            label: "Peticiones",
            data: valores,
            backgroundColor: ["#f39c12", "#27ae60", "#e74c3c", "#2980b9"]
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: true, position: "bottom" } }
        }
      });
    }
  } catch (err) {
    console.error("Error cargando dashboard admin:", err);
    alert("No se pudo cargar la información del panel de administración");
  }
});
