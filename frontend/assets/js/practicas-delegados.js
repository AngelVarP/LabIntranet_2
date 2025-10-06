/* practicas-delegados.js
 * Muestra todas las prácticas para Delegado / Alumno
 */
document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");
  const tablaBody = document.getElementById("practicas-body");

  if (!TOKEN) {
    alert("No tienes sesión iniciada");
    window.location.href = "../login.html";
    return;
  }

  async function cargarPracticas() {
    tablaBody.innerHTML = `<tr><td colspan="4">Cargando prácticas...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/practicas/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();
      console.log("Practicas recibidas:", data);

      if (!Array.isArray(data)) {
        tablaBody.innerHTML = `<tr><td colspan="4">Error al cargar prácticas</td></tr>`;
        return;
      }

      if (data.length === 0) {
        tablaBody.innerHTML = `<tr><td colspan="4">No hay prácticas disponibles</td></tr>`;
        return;
      }

      tablaBody.innerHTML = "";
      data.forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.id}</td>
          <td>${p.nombre}</td>
          <td>${p.fecha || "-"}</td>
          <td>${p.estado || "Publicado"}</td>
        `;
        tablaBody.appendChild(tr);
      });
    } catch (err) {
      console.error("Error cargando prácticas:", err);
      tablaBody.innerHTML = `<tr><td colspan="4">Error de conexión</td></tr>`;
    }
  }

  cargarPracticas();
});
