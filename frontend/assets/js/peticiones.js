document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-peticion");
  const filterStatus = document.getElementById("filter-status");
  const tableBody = document.querySelector("#peticiones-table tbody");

  const detailModal = document.getElementById("detail-modal");
  const actionModal = document.getElementById("action-modal");
  const closeDetail = document.getElementById("close-detail");
  const closeAction = document.getElementById("close-action");
  const actionForm = document.getElementById("action-form");
  const actionTitle = document.getElementById("action-title");
  const approvedQtyInput = document.getElementById("approved-qty");

  let currentRow = null;
  let currentAction = null;

  // ==== FILTRO Y BÚSQUEDA ====
  function filterTable() {
    const text = searchInput.value.toLowerCase();
    const status = filterStatus.value;

    [...tableBody.children].forEach(row => {
      const usuario = row.cells[1].textContent.toLowerCase();
      const producto = row.cells[2].textContent.toLowerCase();
      const estado = row.cells[5].textContent;

      const matchText = usuario.includes(text) || producto.includes(text);
      const matchStatus = status === "todos" || estado === status;

      row.style.display = matchText && matchStatus ? "" : "none";
    });
  }

  searchInput.addEventListener("input", filterTable);
  filterStatus.addEventListener("change", filterTable);

  // ==== VER DETALLE ====
  tableBody.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-detail")) {
      const row = e.target.closest("tr");
      document.getElementById("detail-user").textContent = row.cells[1].textContent;
      document.getElementById("detail-product").textContent = row.cells[2].textContent;
      document.getElementById("detail-qty").textContent = row.cells[3].textContent;
      document.getElementById("detail-comments").textContent = "Sin comentarios (demo)";
      detailModal.style.display = "flex";
    }

    if (e.target.classList.contains("btn-approve")) {
      currentRow = e.target.closest("tr");
      currentAction = "Aprobar";
      actionTitle.textContent = "Aprobar Petición";
      approvedQtyInput.value = currentRow.cells[3].textContent;
      approvedQtyInput.parentElement.style.display = "block";
      actionModal.style.display = "flex";
    }

    if (e.target.classList.contains("btn-reject")) {
      currentRow = e.target.closest("tr");
      currentAction = "Rechazar";
      actionTitle.textContent = "Rechazar Petición";
      approvedQtyInput.parentElement.style.display = "none";
      actionModal.style.display = "flex";
    }
  });

  closeDetail.addEventListener("click", () => { detailModal.style.display = "none"; });
  closeAction.addEventListener("click", () => { actionModal.style.display = "none"; });

  // ==== CONFIRMAR ACCIÓN ====
  actionForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const qty = approvedQtyInput.value;
    const statusCell = currentRow.querySelector(".status");
    if (currentAction === "Aprobar") {
      statusCell.textContent = `Aprobada (${qty})`;
      statusCell.className = "status aprobada";
    } else {
      statusCell.textContent = "Rechazada";
      statusCell.className = "status rechazada";
    }
    actionModal.style.display = "none";
    alert(`Petición ${currentAction.toLowerCase()} con éxito.`);
  });

  // ==== EXPORTAR LISTADO ====
  document.getElementById("btn-export-peticiones").addEventListener("click", () => {
    alert("Exportación simulada. Aquí podrías generar un CSV/Excel con el backend.");
  });

  // ==== Cerrar modales si clic fuera ====
  window.onclick = (e) => {
    if (e.target === detailModal) detailModal.style.display = "none";
    if (e.target === actionModal) actionModal.style.display = "none";
  };
});
