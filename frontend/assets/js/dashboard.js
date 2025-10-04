document.addEventListener("DOMContentLoaded", () => {
  const rol = sessionStorage.getItem("usuarioRol");
  if (rol) {
    document.getElementById("user-role").textContent =
      rol.charAt(0).toUpperCase() + rol.slice(1);
  }
});
