/* solicitudes.js
 * Listar y crear solicitudes (Delegado) usando endpoints correctos
 */

const API_BASE = "/LabIntranet_2/backend/api";
const TOKEN = localStorage.getItem("token");

/* ====== CARGAR PRODUCTOS ====== */
async function cargarProductos() {
  const sel = document.getElementById("producto_id");
  if (!sel) return;

  sel.innerHTML = '<option value="">Cargando productos...</option>';

  try {
    const res = await fetch(`${API_BASE}/productos/listar.php`, {
      headers: { "Authorization": `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    sel.innerHTML = '<option value="">Selecciona un producto</option>';
    if (Array.isArray(data) && data.length) {
      data.forEach(p => {
        const opt = document.createElement("option");
        opt.value = p.id;
        opt.textContent = p.nombre + (p.stock ? ` (Stock: ${p.stock})` : "");
        sel.appendChild(opt);
      });
    } else {
      sel.innerHTML = '<option value="">No hay productos disponibles</option>';
    }
  } catch (e) {
    console.error("Error al cargar productos:", e);
    sel.innerHTML = '<option value="">Error al cargar productos</option>';
  }
}

/* ====== CARGAR SOLICITUDES ====== */
async function cargarSolicitudes() {
  const tabla = document.getElementById("tabla-solicitudes");
  if (!tabla) return;

  tabla.innerHTML = "<tr><td colspan='4'>Cargando...</td></tr>";

  try {
    const res = await fetch(`${API_BASE}/solicitudes/listar.php`, {
      headers: { "Authorization": `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    tabla.innerHTML = "";
    if (!Array.isArray(data) || data.length === 0) {
      tabla.innerHTML = "<tr><td colspan='4'>Sin solicitudes</td></tr>";
      return;
    }

    data.forEach(s => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>#${s.id}</td>
        <td>${s.producto || "-"}</td>
        <td>${s.cantidad ?? 0}</td>
        <td>${s.estado || "-"}</td>
      `;
      tabla.appendChild(tr);
    });
  } catch (err) {
    console.error("Error al listar solicitudes:", err);
    tabla.innerHTML = "<tr><td colspan='4'>Error al cargar</td></tr>";
  }
}

/* ====== INICIO ====== */
document.addEventListener("DOMContentLoaded", () => {
  // ⚡️Llamamos a ambas funciones solo si existe el elemento correspondiente
  if (document.getElementById("tabla-solicitudes")) {
    cargarSolicitudes();
  }

  if (document.getElementById("producto_id")) {
    cargarProductos();
  }

  const form = document.getElementById("form-crear-solicitud");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const prod = parseInt(document.getElementById("producto_id")?.value || "0", 10);
      const cant = parseInt(document.getElementById("cantidad")?.value || "0", 10);
      if (!prod || !cant) {
        alert("Completa producto y cantidad");
        return;
      }

      try {
        const res = await fetch(`${API_BASE}/solicitudes/crear.php`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${TOKEN}`
          },
          body: JSON.stringify({ producto_id: prod, cantidad: cant }),
        });
        const data = await res.json();

        if (data?.success) {
          alert("Solicitud enviada correctamente");
          cargarSolicitudes();
          form.reset();
          const modal = document.getElementById("modal-solicitud");
          if (modal?.close) modal.close();
        } else {
          alert(data?.message || "No se pudo enviar la solicitud");
        }
      } catch (err) {
        console.error("Error al enviar solicitud:", err);
        alert("Error de conexión con el servidor");
      }
    });
  }
});
