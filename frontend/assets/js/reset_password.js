document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const form = document.getElementById("reset-form");

  // toma el token de la URL
  const token = new URLSearchParams(window.location.search).get("token");
  if (!token) {
    alert("Enlace inválido o incompleto.");
    window.location.href = "login.html";
    return;
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const newPass = document.getElementById("new-password").value.trim();
    const confirmPass = document.getElementById("confirm-password").value.trim();

    if (newPass.length < 6) { alert("Mínimo 6 caracteres."); return; }
    if (newPass !== confirmPass) { alert("Las contraseñas no coinciden."); return; }

    try {
      const res = await fetch(`${API_BASE}/auth/reset_password.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        // 👇 ENVÍA token y password (nombre exacto)
        body: JSON.stringify({ token, password: newPass }),
      });
      const data = await res.json();
      alert(data.message || "Respuesta recibida.");
      if (data.success) window.location.href = "login.html";
    } catch (err) {
      console.error(err);
      alert("Error de conexión con el servidor.");
    }
  });
});
