/* practicas-profesor.js
 * Gestión de prácticas para Profesor
 */
document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  const formCrear = document.getElementById("form-crear-practica");
  const tabla = document.getElementById("tabla-practicas").querySelector("tbody");
  const buscar = document.getElementById("buscar-practica");
  const btnAgregarMat = document.getElementById("btn-agregar-material");
  const matContainer = document.getElementById("materiales-container");

  let practicas = [];

  // ==== CARGAR PRÁCTICAS EXISTENTES ====
  async function cargarPracticas() {
    tabla.innerHTML = `<tr><td colspan="5">Cargando prácticas...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/practicas/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();
      if (!Array.isArray(data)) {
        tabla.innerHTML = `<tr><td colspan="5">Error al cargar</td></tr>`;
        return;
      }
      practicas = data;
      renderTabla();
    } catch (e) {
      console.error("Error al cargar prácticas:", e);
      tabla.innerHTML = `<tr><td colspan="5">Error de conexión</td></tr>`;
    }
  }

  // ==== RENDER TABLA ====
  function renderTabla() {
    tabla.innerHTML = "";
    if (!practicas.length) {
      tabla.innerHTML = `<tr><td colspan="5">No hay prácticas</td></tr>`;
      return;
    }

    const texto = buscar.value.toLowerCase();
    practicas
      .filter(p => p.nombre.toLowerCase().includes(texto) || (p.descripcion||"").toLowerCase().includes(texto))
      .forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${p.id}</td>
          <td>${p.nombre}</td>
          <td>${p.fecha || "-"}</td>
          <td>${p.estado || "Publicado"}</td>
          <td>
            <button class="btn-delete" data-id="${p.id}">🗑️</button>
          </td>
        `;
        tabla.appendChild(tr);
      });
  }

  buscar.addEventListener("input", renderTabla);

  tabla.addEventListener("click", async (e) => {
    if (e.target.matches(".btn-delete")) {
      const id = e.target.dataset.id;
      if (!confirm("¿Eliminar esta práctica?")) return;

      try {
        const res = await fetch(`${API_BASE}/practicas/eliminar.php`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${TOKEN}`
          },
          body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data?.success) {
          alert("Práctica eliminada");
          cargarPracticas();
        } else {
          alert(data?.message || "Error al eliminar");
        }
      } catch (err) {
        console.error(err);
        alert("Error de conexión");
      }
    }
  });

  // ==== AGREGAR MATERIAL AL FORM ====
  btnAgregarMat.addEventListener("click", () => {
    const div = document.createElement("div");
    div.className = "material-item";
    div.innerHTML = `
      <input type="number" class="mat-producto" placeholder="ID producto" style="width:100px" required>
      <input type="number" class="mat-cantidad" placeholder="Cantidad" style="width:100px" min="1" required>
      <button type="button" class="btn-remove-mat">❌</button>
    `;
    div.querySelector(".btn-remove-mat").addEventListener("click", () => div.remove());
    matContainer.appendChild(div);
  });

  // ==== CREAR PRÁCTICA ====
  formCrear.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nombre = document.getElementById("titulo").value.trim();
    const descripcion = document.getElementById("descripcion").value.trim();

    const materiales = [...matContainer.querySelectorAll(".material-item")].map(m => ({
      producto_id: parseInt(m.querySelector(".mat-producto").value || 0),
      cantidad: parseInt(m.querySelector(".mat-cantidad").value || 0)
    })).filter(m => m.producto_id && m.cantidad);

    if (!nombre) return alert("Ingresa un título");
    if (!descripcion) return alert("Ingresa una descripción");

    try {
      const res = await fetch(`${API_BASE}/practicas/crear.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${TOKEN}`
        },
        body: JSON.stringify({ nombre, descripcion, materiales })
      });
      const data = await res.json();
      if (data?.success) {
        alert("Práctica creada correctamente");
        formCrear.reset();
        matContainer.innerHTML = "";
        cargarPracticas();
      } else {
        alert(data?.message || "No se pudo crear");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión");
    }
  });

  // ==== INICIO ====
  cargarPracticas();
});
