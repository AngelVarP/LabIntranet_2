document.addEventListener("DOMContentLoaded", () => {
  const recoverForm = document.getElementById("recover-form");
  const API_BASE = "/LabIntranet_2/backend/api";

  if (recoverForm) {
    recoverForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const code = document.getElementById("recover-code").value.trim().toUpperCase();

      if (!code) return alert("Por favor, ingresa un código válido.");

      try {
        const res = await fetch(`${API_BASE}/auth/recover.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ codigo: code }),
        });

        const data = await res.json();
        alert(data.message || "Solicitud enviada");
        window.location.href = "login.html";
      } catch (err) {
        alert("Error de conexión con el servidor.");
      }
    });
  }
});
