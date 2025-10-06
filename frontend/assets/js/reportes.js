document.addEventListener("DOMContentLoaded", async () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  const countUsers = document.getElementById("count-users");
  const countProducts = document.getElementById("count-products");
  const countPending = document.getElementById("count-pending");
  const countApproved = document.getElementById("count-approved");

  try {
    const res = await fetch(`${API_BASE}/reportes/resumen.php`, {
      headers: { Authorization: `Bearer ${TOKEN}` }
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const data = await res.json();

    // === Tarjetas ===
    countUsers.textContent = data.usuarios ?? 0;
    countProducts.textContent = data.productos ?? 0;
    countPending.textContent = data.pendientes ?? 0;
    countApproved.textContent = data.aprobadas ?? 0;

    // === Chart: Consumo Inventario ===
    const ctxInv = document.getElementById("chart-inventario").getContext("2d");
    new Chart(ctxInv, {
      type: "bar",
      data: {
        labels: data.consumo.map(x => x.nombre),
        datasets: [{
          label: "Cantidad utilizada",
          data: data.consumo.map(x => x.total),
          backgroundColor: ["#F6AE2D", "#86BBD8", "#2ecc71", "#e74c3c", "#3498db", "#9b59b6"]
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // === Chart: Solicitudes por rol ===
    const ctxRoles = document.getElementById("chart-roles").getContext("2d");
    new Chart(ctxRoles, {
      type: "doughnut",
      data: {
        labels: data.porRol.map(x => x.rol),
        datasets: [{
          data: data.porRol.map(x => x.total),
          backgroundColor: ["#F6AE2D", "#86BBD8", "#2ecc71", "#e74c3c"]
        }]
      },
      options: { responsive: true }
    });

  } catch (err) {
    console.error("Error al cargar reportes:", err);
    alert("No se pudieron cargar los datos de reportes.");
  }

  // === Botones de exportación ===
  document.getElementById("btn-export-pdf").addEventListener("click", () => {
    window.print(); // imprime la página como PDF
  });

  document.getElementById("btn-export-excel").addEventListener("click", () => {
    let csv = [];
    csv.push("Usuarios,Productos,Pendientes,Aprobadas");
    csv.push(`${countUsers.textContent},${countProducts.textContent},${countPending.textContent},${countApproved.textContent}`);
    const blob = new Blob([csv.join("\n")], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "reportes.csv";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  });
});
