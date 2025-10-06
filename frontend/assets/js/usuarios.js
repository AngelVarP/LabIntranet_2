document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");

  const modal = document.getElementById("user-modal");
  const btnAddUser = document.getElementById("btn-add-user");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const userForm = document.getElementById("user-form");
  const userTableBody = document.getElementById("user-table-body");
  const searchInput = document.getElementById("search-user");
  const filterRole = document.getElementById("filter-role");

  // ==== CARGAR USUARIOS DEL BACKEND ====
  async function cargarUsuarios() {
    userTableBody.innerHTML = `<tr><td colspan="6">Cargando...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/usuarios/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();

      if (!Array.isArray(data)) {
        userTableBody.innerHTML = `<tr><td colspan="6">Error al cargar usuarios</td></tr>`;
        console.error("Respuesta inesperada:", data);
        return;
      }

      userTableBody.innerHTML = "";
      data.forEach(u => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${u.codigo}</td>
          <td>${u.nombre}</td>
          <td>${u.correo}</td>
          <td>${u.rol}</td>
          <td>${u.fuente || "-"}</td>
          <td>
            <button class="btn-reset" data-id="${u.id}">🔑</button>
            <button class="btn-delete" data-id="${u.id}">🗑️</button>
          </td>
        `;
        userTableBody.appendChild(tr);
      });
    } catch (err) {
      console.error("Error al cargar usuarios:", err);
      userTableBody.innerHTML = `<tr><td colspan="6">Error de conexión</td></tr>`;
    }
  }

  // ==== ABRIR MODAL ====
  btnAddUser.addEventListener("click", () => {
    modal.style.display = "flex";
    document.getElementById("modal-title").textContent = "Agregar Usuario";
    userForm.reset();
  });

  // ==== CERRAR MODAL ====
  btnCloseModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // ==== GUARDAR NUEVO USUARIO EN BACKEND ====
  userForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const codigo = document.getElementById("user-id").value;
    const nombre = document.getElementById("user-name").value;
    const correo = document.getElementById("user-email").value;
    const rol = document.getElementById("user-role").value;

    try {
      const res = await fetch(`${API_BASE}/usuarios/crear.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${TOKEN}`
        },
        body: JSON.stringify({ codigo, nombre, correo, rol })
      });

      const data = await res.json();
      if (data?.success) {
        alert("Usuario agregado correctamente");
        modal.style.display = "none";
        cargarUsuarios();
      } else {
        alert(data?.message || "No se pudo agregar");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión");
    }
  });

  // ==== FILTRAR POR TEXTO Y ROL ====
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

  // ==== BOTONES RESET / DELETE ====
  userTableBody.addEventListener("click", async (e) => {
    const id = e.target.dataset.id;
    if (e.target.classList.contains("btn-reset")) {
      alert("Aquí podrías implementar reset de contraseña para id " + id);
    }
    if (e.target.classList.contains("btn-delete")) {
      if (confirm("¿Seguro que quieres eliminar este usuario?")) {
        try {
          const res = await fetch(`${API_BASE}/usuarios/eliminar.php`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${TOKEN}`
            },
            body: JSON.stringify({ id })
          });
          const data = await res.json();
          if (data?.success) {
            alert("Usuario eliminado");
            cargarUsuarios();
          } else {
            alert(data?.message || "No se pudo eliminar");
          }
        } catch (err) {
          console.error(err);
          alert("Error de conexión");
        }
      }
    }
  });

  // ==== INICIO ====
  cargarUsuarios();
});
