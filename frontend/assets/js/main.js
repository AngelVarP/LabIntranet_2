document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");

  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const code = document.getElementById("code").value.trim();
      const password = document.getElementById("password").value.trim();
      const rol = document.getElementById("rol").value.trim();

      try {
        const response = await fetch("http://localhost/LabIntranet_2/Controllers/procesoLogin.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `code=${encodeURIComponent(code)}&password=${encodeURIComponent(password)}&rol=${encodeURIComponent(rol)}`
        });

        const data = await response.json();

        if (data.success) {
          if (data.rol === "Administrador") {
            window.location.href = "../frontend/public/admin/dashboard.php";
          } else if (data.rol === "delegado") {
            window.location.href = "../frontend/public/delegado/dashboard.php";
          } else if (data.rol === "instructor") {
            window.location.href = "../frontend/public/instructor/dashboard.php";
          }
        } else {
          alert(data.message || "Usuario o contraseña incorrectos");
        }
      } catch (error) {
        console.error(error);
        alert("Error de conexión con el servidor");
      }
    });
  }
});
