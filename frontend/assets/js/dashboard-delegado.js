/* dashboard-delegado.js
 * Dashboard principal del Delegado
 */
document.addEventListener("DOMContentLoaded", async () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  if (!TOKEN) {
    alert("No tienes sesión iniciada");
    window.location.href = "../login.html";
    return;
  }

  try {
    const res = await fetch(`${API_BASE}/dashboard/delegado_resumen.php`, {
      headers: { Authorization: `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    // === Resumen grupo ===
    document.getElementById("grupo-nombre").textContent = data.grupo || "Sin grupo asignado";
    document.getElementById("grupo-seccion").textContent = data.grupo ? "Sección A" : "-"; // puedes personalizar
    document.getElementById("total-alumnos").textContent = data.total_alumnos || 0;

    // === Solicitudes recientes ===
    const tbody = document.getElementById("tabla-solicitudes");
    tbody.innerHTML = "";
    if (data.ultimas_solicitudes && data.ultimas_solicitudes.length) {
      data.ultimas_solicitudes.forEach(s => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${s.producto}</td>
          <td>${s.cantidad}</td>
          <td>${s.estado}</td>
          <td>${s.fecha}</td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No tienes solicitudes recientes</td></tr>`;
    }

  } catch (err) {
    console.error("Error cargando dashboard delegado:", err);
    document.getElementById("tabla-solicitudes").innerHTML =
      `<tr><td colspan="4" style="text-align:center;">Error al cargar datos</td></tr>`;
  }
});
