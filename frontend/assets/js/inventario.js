document.addEventListener("DOMContentLoaded", () => {
  const searchBar = document.getElementById("search-bar");
  const filterCategory = document.getElementById("filter-category");
  const tableBody = document.querySelector("#inventory-table tbody");
  const modal = document.getElementById("product-modal");
  const btnAdd = document.getElementById("btn-add-product");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const productForm = document.getElementById("product-form");

  // ==== FILTRO Y BÚSQUEDA ====
  function filterTable() {
    const text = searchBar.value.toLowerCase();
    const category = filterCategory.value;

    [...tableBody.children].forEach(row => {
      const name = row.cells[1].textContent.toLowerCase();
      const cat = row.cells[2].textContent;
      const matchText = name.includes(text);
      const matchCat = category === "todos" || cat === category;

      row.style.display = matchText && matchCat ? "" : "none";
    });
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
    const unit = document.getElementById("product-unit").value;
    const location = document.getElementById("product-location").value;
    const stock = parseInt(document.getElementById("product-stock").value);
    const min = parseInt(document.getElementById("product-min").value);

    const estado = stock <= min ? "low" : stock <= min * 2 ? "medium" : "high";

    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${id}</td>
      <td>${name}</td>
      <td>${category}</td>
      <td>${unit}</td>
      <td>${location}</td>
      <td>${stock}</td>
      <td>${min}</td>
      <td><span class="stock ${estado}">${estado === "low" ? "Bajo" : estado === "medium" ? "Medio" : "Alto"}</span></td>
      <td>
        <button class="btn-edit">✏️</button>
        <button class="btn-delete">🗑️</button>
      </td>
    `;
    tableBody.appendChild(row);

    modal.style.display = "none";
  });

  // ==== EXPORTAR ====
  document.getElementById("btn-export").addEventListener("click", () => {
    alert("Exportación simulada. Aquí podrías generar un CSV/Excel con el backend.");
  });

  // ==== Cerrar modal si clic fuera ====
  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
});
