/* peticiones.js
 * Panel de Peticiones para Admin
 */

document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  const tableBody = document.querySelector("#peticiones-table tbody");
  const searchInput = document.getElementById("search-peticion");
  const filterStatus = document.getElementById("filter-status");

  const detailModal = document.getElementById("detail-modal");
  const actionModal = document.getElementById("action-modal");
  const closeDetail = document.getElementById("close-detail");
  const closeAction = document.getElementById("close-action");

  const detailUser = document.getElementById("detail-user");
  const detailProduct = document.getElementById("detail-product");
  const detailQty = document.getElementById("detail-qty");
  const detailComments = document.getElementById("detail-comments");

  const actionTitle = document.getElementById("action-title");
  const actionForm = document.getElementById("action-form");
  const approvedQty = document.getElementById("approved-qty");
  const adminComment = document.getElementById("admin-comment");

  let peticiones = [];
  let peticionSeleccionada = null;
  let modoAccion = ""; // "aprobar" o "rechazar"

  // ==== CARGAR PETICIONES ====
  async function cargarPeticiones() {
    tableBody.innerHTML = `<tr><td colspan="7">Cargando peticiones...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/solicitudes/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();

      if (!Array.isArray(data)) {
        tableBody.innerHTML = `<tr><td colspan="7">Error al cargar peticiones</td></tr>`;
        return;
      }
      peticiones = data;
      renderTable();
    } catch (err) {
      console.error(err);
      tableBody.innerHTML = `<tr><td colspan="7">Error de conexión</td></tr>`;
    }
  }

  // ==== RENDERIZAR TABLA ====
  function renderTable() {
    tableBody.innerHTML = "";
    if (!peticiones.length) {
      tableBody.innerHTML = `<tr><td colspan="7">No hay peticiones</td></tr>`;
      return;
    }

    peticiones.forEach(p => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${p.id}</td>
        <td>${p.usuario}</td>
        <td>${p.producto}</td>
        <td>${p.cantidad}</td>
        <td>${p.fecha}</td>
        <td><span class="status ${p.estado.toLowerCase()}">${p.estado}</span></td>
        <td>
          <button class="btn-detail" data-id="${p.id}">👁️</button>
          ${p.estado === "Pendiente" ? `
            <button class="btn-approve" data-id="${p.id}">✔️</button>
            <button class="btn-reject" data-id="${p.id}">❌</button>
          ` : ""}
        </td>
      `;
      tableBody.appendChild(tr);
    });

    filterTable();
  }

  // ==== FILTRAR TABLA ====
  function filterTable() {
    const text = searchInput.value.toLowerCase();
    const estado = filterStatus.value;

    [...tableBody.children].forEach(row => {
      const usuario = row.cells[1].textContent.toLowerCase();
      const producto = row.cells[2].textContent.toLowerCase();
      const est = row.cells[5].textContent;
      const matchText = usuario.includes(text) || producto.includes(text);
      const matchStatus = estado === "todos" || est === estado;

      row.style.display = matchText && matchStatus ? "" : "none";
    });
  }

  searchInput.addEventListener("input", filterTable);
  filterStatus.addEventListener("change", filterTable);

  // ==== EVENTOS EN LA TABLA ====
  tableBody.addEventListener("click", async (e) => {
    const id = e.target.dataset.id;
    if (!id) return;

    if (e.target.classList.contains("btn-detail")) {
      const pet = peticiones.find(x => x.id == id);
      if (!pet) return;
      peticionSeleccionada = pet;

      detailUser.textContent = pet.usuario;
      detailProduct.textContent = pet.producto;
      detailQty.textContent = pet.cantidad;
      detailComments.textContent = pet.comentarios || "-";

      detailModal.style.display = "flex";
    }

    if (e.target.classList.contains("btn-approve")) {
      modoAccion = "aprobar";
      peticionSeleccionada = peticiones.find(x => x.id == id);
      actionTitle.textContent = `Aprobar petición #${id}`;
      approvedQty.value = peticionSeleccionada.cantidad;
      actionModal.style.display = "flex";
    }

    if (e.target.classList.contains("btn-reject")) {
      modoAccion = "rechazar";
      peticionSeleccionada = peticiones.find(x => x.id == id);
      actionTitle.textContent = `Rechazar petición #${id}`;
      approvedQty.value = peticionSeleccionada.cantidad;
      actionModal.style.display = "flex";
    }
  });

  // ==== CERRAR MODALES ====
  closeDetail.addEventListener("click", () => detailModal.style.display = "none");
  closeAction.addEventListener("click", () => actionModal.style.display = "none");

  window.onclick = (e) => {
    if (e.target === detailModal) detailModal.style.display = "none";
    if (e.target === actionModal) actionModal.style.display = "none";
  };

  // ==== CONFIRMAR APROBAR / RECHAZAR ====
  actionForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!peticionSeleccionada) return;

    try {
      const res = await fetch(`${API_BASE}/solicitudes/cambiar_estado.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${TOKEN}`
        },
        body: JSON.stringify({
          id: peticionSeleccionada.id,
          estado: modoAccion === "aprobar" ? "Aprobada" : "Rechazada",
          cantidad_aprobada: approvedQty.value,
          comentario_admin: adminComment.value
        })
      });

      const data = await res.json();
      if (data?.success) {
        alert(`Petición ${modoAccion === "aprobar" ? "aprobada" : "rechazada"} correctamente`);
        actionModal.style.display = "none";
        cargarPeticiones();
      } else {
        alert(data?.message || "No se pudo cambiar el estado");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión");
    }
  });

  // ==== EXPORTAR ====
  document.getElementById("btn-export-peticiones").addEventListener("click", () => {
    let csv = [];
    const rows = document.querySelectorAll("#peticiones-table tr");
    rows.forEach(row => {
      const cols = row.querySelectorAll("th,td");
      const line = [...cols].map(c => `"${c.innerText}"`).join(",");
      csv.push(line);
    });
    const blob = new Blob([csv.join("\n")], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "peticiones.csv";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  });

  // ==== INICIO ====
  cargarPeticiones();
});
