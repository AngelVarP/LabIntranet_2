document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");

  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const code = document.getElementById("code").value.trim().toUpperCase();
      const password = document.getElementById("password").value.trim();

      try {
        const response = await fetch("/LabIntranet_2/backend/general/login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `code=${encodeURIComponent(code)}&password=${encodeURIComponent(password)}`
        });
        const data = await response.json();
        if (data.success) {
          alert(`Bienvenido! Rol detectado: ${data.rol}`);
          sessionStorage.setItem("usuarioRol", data.rol);
          if (data.rol === "admin") window.location.href = "admin/dashboard.html";
          else if (data.rol === "profesor") window.location.href = "profesor/dashboard.html";
          else if (data.rol === "tecnico") window.location.href = "tecnico/dashboard.html";
          else window.location.href = "estudiante/dashboard.html";
        } else {
          alert(data.message || "Código o contraseña incorrectos");
        }
      } catch (error) {
        alert("Error de conexión con el servidor");
      }
    });
  }
});
