/* inventario.js
 * Inventario Admin/Profesor: listar productos reales y filtrarlos
 */
document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  const searchBar = document.getElementById("search-bar");
  const filterCategory = document.getElementById("filter-category");
  const tableBody = document.querySelector("#inventory-table tbody");
  const modal = document.getElementById("product-modal");
  const btnAdd = document.getElementById("btn-add-product");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const productForm = document.getElementById("product-form");

  let productos = []; // datos del backend

  // ==== CARGAR PRODUCTOS DEL BACKEND ====
  async function cargarProductos() {
    tableBody.innerHTML = `<tr><td colspan="9">Cargando productos...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/productos/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();

      if (!Array.isArray(data)) {
        tableBody.innerHTML = `<tr><td colspan="9">Error al cargar productos</td></tr>`;
        return;
      }
      productos = data;
      renderTable();
      fillCategories();
    } catch (e) {
      console.error("Error al cargar productos:", e);
      tableBody.innerHTML = `<tr><td colspan="9">Error de conexión</td></tr>`;
    }
  }

  // ==== RENDERIZAR TABLA ====
  function renderTable() {
    tableBody.innerHTML = "";
    if (!productos.length) {
      tableBody.innerHTML = `<tr><td colspan="9">No hay productos</td></tr>`;
      return;
    }

    productos.forEach(p => {
      const estado = calcEstado(parseInt(p.stock || 0), 10); // mínimo ejemplo
      const tr = document.createElement("tr");
      tr.dataset.catid = p.categoria_id || ""; // guardar el ID de categoría
      tr.innerHTML = `
        <td>${p.id}</td>
        <td>${p.nombre}</td>
        <td>${p.categoria || "-"}</td>
        <td>${p.unidad || "-"}</td>
        <td>-</td>
        <td>${p.stock ?? 0}</td>
        <td>10</td>
        <td><span class="stock ${estado}">${estado === "low" ? "Bajo" : estado === "medium" ? "Medio" : "Alto"}</span></td>
        <td>
          <button class="btn-edit">✏️</button>
          <button class="btn-delete">🗑️</button>
        </td>
      `;
      tableBody.appendChild(tr);
    });
    filterTable();
  }

  function calcEstado(stock, min) {
    if (stock <= min) return "low";
    if (stock <= min * 2) return "medium";
    return "high";
  }

  // ==== FILTRAR Y BUSCAR ====
  function filterTable() {
    const text = searchBar.value.toLowerCase();
    const category = filterCategory.value;

    [...tableBody.children].forEach(row => {
      const name = row.cells[1].textContent.toLowerCase();
      const catId = row.dataset.catid;
      const matchText = name.includes(text);
      const matchCat = category === "" || category === "todos" || catId === category;

      row.style.display = matchText && matchCat ? "" : "none";
    });
  }

  searchBar.addEventListener("input", filterTable);
  filterCategory.addEventListener("change", filterTable);

  // ==== LLENAR COMBO DE CATEGORÍAS ====
  function fillCategories() {
    const cats = [];
    productos.forEach(p => {
      if (p.categoria_id && p.categoria) {
        cats.push({id: p.categoria_id, name: p.categoria});
      }
    });
    const unique = [];
    const seen = new Set();
    cats.forEach(c => {
      if (!seen.has(c.id)) { seen.add(c.id); unique.push(c); }
    });

    filterCategory.innerHTML = `<option value="">Todos</option>` +
      unique.map(c => `<option value="${c.id}">${c.name}</option>`).join("");
  }

  // ==== MODAL ====
  btnAdd.addEventListener("click", () => {
    modal.style.display = "flex";
    productForm.reset();
  });

  btnCloseModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  productForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const id = document.getElementById("product-id").value;
    const nombre = document.getElementById("product-name").value;
    const categoria_id = document.getElementById("product-category").value;
    const unidad = document.getElementById("product-unit").value;
    const ubicacion = document.getElementById("product-location").value;
    const stock = parseInt(document.getElementById("product-stock").value);
    const min = parseInt(document.getElementById("product-min").value);

    try {
      const res = await fetch(`${API_BASE}/productos/crear.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${TOKEN}`
        },
        body: JSON.stringify({
          id,
          nombre,
          categoria_id,
          unidad,
          ubicacion,
          stock,
          minimo: min
        })
      });

      const data = await res.json();
      if (data?.success) {
        alert("Producto agregado correctamente");
        modal.style.display = "none";
        location.reload(); // recarga para mostrar el nuevo producto
      } else {
        alert(data?.message || "No se pudo agregar");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión");
    }
  });

  // ==== EXPORTAR ====
  document.getElementById("btn-export").addEventListener("click", () => {
    let csv = [];
    const rows = document.querySelectorAll("#inventory-table tr");
    rows.forEach(row => {
      const cols = row.querySelectorAll("th,td");
      const line = [...cols].map(c => `"${c.innerText}"`).join(",");
      csv.push(line);
    });
    const blob = new Blob([csv.join("\n")], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "inventario.csv";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  });

  // ==== Cerrar modal si clic fuera ====
  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };

  // ==== INICIO ====
  cargarProductos();
});
