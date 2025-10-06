const API_BASE = "/LabIntranet_2/backend/api";
const TOKEN = localStorage.getItem("token");

let solicitudes = []; // Guardaremos todas para filtrar luego

async function cargarSolicitudesAlumno() {
  const tabla = document.querySelector("#tabla-solicitudes tbody");
  if (!tabla) return;

  tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">Cargando...</td></tr>`;

  try {
    const res = await fetch(`${API_BASE}/solicitudes/mis.php`, {
      headers: { "Authorization": `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">No tienes solicitudes registradas</td></tr>`;
      solicitudes = [];
      return;
    }

    solicitudes = data;
    renderTabla(); // Pintamos la tabla con todos los datos
  } catch (err) {
    console.error("Error al cargar solicitudes alumno:", err);
    tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error al cargar solicitudes</td></tr>`;
  }
}

function renderTabla() {
  const tabla = document.querySelector("#tabla-solicitudes tbody");
  const filtroTexto = document.getElementById("buscar-solicitud").value.toLowerCase();
  const filtroEstado = document.getElementById("filtrar-estado").value;

  tabla.innerHTML = "";

  // Filtrado
  const filtradas = solicitudes.filter(s => {
    const matchTexto =
      s.producto?.toLowerCase().includes(filtroTexto) ||
      s.id.toString().includes(filtroTexto);
    const matchEstado =
      filtroEstado === "" || (s.estado && s.estado.toLowerCase() === filtroEstado.toLowerCase());

    return matchTexto && matchEstado;
  });

  if (filtradas.length === 0) {
    tabla.innerHTML = `<tr><td colspan="5" style="text-align:center;">No se encontraron solicitudes</td></tr>`;
    return;
  }

  filtradas.forEach(s => {
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
}

document.addEventListener("DOMContentLoaded", () => {
  cargarSolicitudesAlumno();

  // Eventos para filtrar en tiempo real
  document.getElementById("buscar-solicitud")
    ?.addEventListener("input", renderTabla);

  document.getElementById("filtrar-estado")
    ?.addEventListener("change", renderTabla);
});
