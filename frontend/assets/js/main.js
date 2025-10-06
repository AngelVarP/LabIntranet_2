document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("login-form");
  if (!form) return;

  const API_BASE = "/LabIntranet_2/backend/api";

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const code = (document.getElementById("code") || document.getElementById("codigo"))?.value.trim() || "";
    const password = document.getElementById("password")?.value.trim() || "";

    if (!code || !password) {
      alert("Debes ingresar usuario y contraseña");
      return;
    }

    try {
      const res = await fetch(`${API_BASE}/auth/login.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codigo: code, password }),
      });

      // Verificar que la respuesta sea JSON válida
      if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
      const data = await res.json();

      if (data?.success && data?.token && data?.user?.rol) {
        localStorage.setItem("token", data.token);
        localStorage.setItem("rol", data.user.rol);

        alert(`Bienvenido, ${data.user.nombre} (${data.user.rol})`);

        switch (data.user.rol) {
          case "Administrador":
            window.location.href = "admin/dashboard.html";
            break;
          case "Profesor":
            window.location.href = "profesor/dashboard.html";
            break;
          case "Delegado":
            window.location.href = "delegado/dashboard.html";
            break;
          case "Alumno":
            window.location.href = "alumno/dashboard.html";
            break;
          default:
            window.location.href = "dashboard.html";
            break;
        }
      } else {
        alert(data?.message || "Credenciales inválidas");
      }
    } catch (err) {
      console.error(err);
      alert("Error de conexión con el servidor");
    }
  });
});
