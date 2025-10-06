const API_BASE = "/LabIntranet_2/backend/api";
const TOKEN = localStorage.getItem("token");

async function cargarSolicitudesAlumno() {
  const tabla = document.querySelector("#tabla-solicitudes tbody");
  if (!tabla) return;

  tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">Cargando...</td></tr>`;

  try {
    const res = await fetch(`${API_BASE}/solicitudes/mis.php`, {
      headers: { "Authorization": `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    tabla.innerHTML = "";
    if (!Array.isArray(data) || data.length === 0) {
      tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">No tienes solicitudes registradas</td></tr>`;
      return;
    }

    data.forEach(s => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>#${s.id}</td>
        <td>${s.producto || "-"}</td>
        <td>${s.fecha_solicitud || "-"}</td>
        <td>${s.estado || "-"}</td>
        <td>—</td>
      `;
      tabla.appendChild(tr);
    });
  } catch (err) {
    console.error("Error al cargar solicitudes alumno:", err);
    tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error al cargar solicitudes</td></tr>`;
  }
}

document.addEventListener("DOMContentLoaded", cargarSolicitudesAlumno);
