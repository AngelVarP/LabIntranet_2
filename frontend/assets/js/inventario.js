document.addEventListener("DOMContentLoaded", () => {
  const searchBar = document.getElementById("search-bar");
  const filterCategory = document.getElementById("filter-category");
  const table = document.getElementById("inventory-table");
  const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

  const modal = document.getElementById("product-modal");
  const btnAdd = document.getElementById("btn-add-product");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const productForm = document.getElementById("product-form");

  // ==== FILTRO Y BÚSQUEDA ====
  function filterTable() {
    const text = searchBar.value.toLowerCase();
    const category = filterCategory.value;

    for (let row of rows) {
      const name = row.cells[1].textContent.toLowerCase();
      const cat = row.cells[2].textContent;
      const matchText = name.includes(text);
      const matchCat = category === "todos" || cat === category;

      row.style.display = matchText && matchCat ? "" : "none";
    }
  }

  searchBar.addEventListener("input", filterTable);
  filterCategory.addEventListener("change", filterTable);

  // ==== MODAL ====
  btnAdd.addEventListener("click", () => {
    modal.style.display = "flex";
    productForm.reset();
  });

  btnCloseModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  productForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("product-id").value;
    const name = document.getElementById("product-name").value;
    const category = document.getElementById("product-category").value;
    const stock = parseInt(document.getElementById("product-stock").value);

    const tbody = table.querySelector("tbody");
    const row = document.createElement("tr");
    const estado = stock <= 10 ? "low" : stock <= 30 ? "medium" : "high";

    row.innerHTML = `
      <td>${id}</td>
      <td>${name}</td>
      <td>${category}</td>
      <td>${stock}</td>
      <td><span class="stock ${estado}">${estado === "low" ? "Bajo" : estado === "medium" ? "Medio" : "Alto"}</span></td>
      <td>
        <button class="btn-edit">✏️</button>
        <button class="btn-delete">🗑️</button>
      </td>
    `;
    tbody.appendChild(row);
    modal.style.display = "none";
  });

  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
});
