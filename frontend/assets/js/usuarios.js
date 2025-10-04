document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("user-modal");
  const btnAddUser = document.getElementById("btn-add-user");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const userForm = document.getElementById("user-form");
  const userTableBody = document.getElementById("user-table-body");
  const searchInput = document.getElementById("search-user");
  const filterRole = document.getElementById("filter-role");

  // === Abrir modal ===
  btnAddUser.addEventListener("click", () => {
    modal.style.display = "flex";
    document.getElementById("modal-title").textContent = "Agregar Usuario";
    userForm.reset();
  });

  // === Cerrar modal ===
  btnCloseModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // === Guardar usuario ===
  userForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("user-id").value;
    const name = document.getElementById("user-name").value;
    const email = document.getElementById("user-email").value;
    const role = document.getElementById("user-role").value;
    const source = document.getElementById("user-source").value;

    const newRow = document.createElement("tr");
    newRow.innerHTML = `
      <td>${id}</td>
      <td>${name}</td>
      <td>${email}</td>
      <td>${role}</td>
      <td>${source}</td>
      <td>
        <button class="btn-reset">🔑</button>
        <button class="btn-edit">✏️</button>
        <button class="btn-delete">🗑️</button>
      </td>
    `;
    userTableBody.appendChild(newRow);

    modal.style.display = "none";
  });

  // === Filtrar usuarios por texto y rol ===
  function filterTable() {
    const text = searchInput.value.toLowerCase();
    const role = filterRole.value;

    [...userTableBody.children].forEach(row => {
      const name = row.cells[1].textContent.toLowerCase();
      const email = row.cells[2].textContent.toLowerCase();
      const userRole = row.cells[3].textContent;

      const matchText = name.includes(text) || email.includes(text);
      const matchRole = role === "todos" || userRole === role;

      row.style.display = matchText && matchRole ? "" : "none";
    });
  }

  searchInput.addEventListener("input", filterTable);
  filterRole.addEventListener("change", filterTable);

  // === Acciones ===
  userTableBody.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-reset")) {
      alert("Se ha enviado una nueva contraseña temporal al correo del usuario.");
    }
    if (e.target.classList.contains("btn-delete")) {
      if (confirm("¿Seguro que quieres eliminar este usuario?")) {
        e.target.closest("tr").remove();
      }
    }
  });

  // === Cerrar modal al hacer click fuera ===
  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
});
