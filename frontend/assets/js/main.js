document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");

  const usuarios = {
    "A001": { pass: "admin123", rol: "admin" },
    "P123": { pass: "prof123", rol: "profesor" },
    "T456": { pass: "tec456", rol: "tecnico" },
    "E789": { pass: "est789", rol: "estudiante" }
  };

  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const code = document.getElementById("code").value.trim().toUpperCase();
      const password = document.getElementById("password").value.trim();

      if (usuarios[code] && usuarios[code].pass === password) {
        const rol = usuarios[code].rol;
        alert(`Bienvenido! Rol detectado: ${rol}`);
        sessionStorage.setItem("usuarioRol", rol);

        if (rol === "admin") window.location.href = "admin/dashboard.html";
        else if (rol === "profesor") window.location.href = "profesor/dashboard.html";
        else if (rol === "tecnico") window.location.href = "tecnico/dashboard.html";
        else window.location.href = "estudiante/dashboard.html";
      } else {
        alert("Código o contraseña incorrectos");
      }
    });
  }
});
