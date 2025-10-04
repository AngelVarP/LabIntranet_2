document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("user-modal");
  const btnAddUser = document.getElementById("btn-add-user");
  const btnCloseModal = document.getElementById("btn-close-modal");
  const userForm = document.getElementById("user-form");
  const userTableBody = document.getElementById("user-table-body");

  btnAddUser.addEventListener("click", () => {
    modal.style.display = "flex";
    document.getElementById("modal-title").textContent = "Agregar Usuario";
    userForm.reset();
  });

  btnCloseModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  userForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("user-id").value;
    const name = document.getElementById("user-name").value;
    const email = document.getElementById("user-email").value;
    const role = document.getElementById("user-role").value;

    const newRow = document.createElement("tr");
    newRow.innerHTML = `
      <td>${id}</td>
      <td>${name}</td>
      <td>${email}</td>
      <td>${role}</td>
      <td>
        <button class="btn-edit">✏️</button>
        <button class="btn-delete">🗑️</button>
      </td>
    `;
    userTableBody.appendChild(newRow);

    modal.style.display = "none";
  });

  // Cerrar modal si clic fuera de él
  window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
});
