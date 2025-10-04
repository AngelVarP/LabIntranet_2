document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("search-peticion");
  const filterStatus = document.getElementById("filter-status");
  const table = document.getElementById("peticiones-table");
  const rows = table.querySelectorAll("tbody tr");

  function filterTable() {
    const text = searchInput.value.toLowerCase();
    const status = filterStatus.value;

    rows.forEach(row => {
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

  // Acciones de aprobar y rechazar (simuladas)
  table.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-approve")) {
      const statusCell = e.target.closest("tr").querySelector(".status");
      statusCell.textContent = "Aprobada";
      statusCell.className = "status aprobada";
    }

    if (e.target.classList.contains("btn-reject")) {
      const statusCell = e.target.closest("tr").querySelector(".status");
      statusCell.textContent = "Rechazada";
      statusCell.className = "status rechazada";
    }
  });
});
