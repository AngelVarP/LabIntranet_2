
document.addEventListener("DOMContentLoaded", () => {
  const resetForm = document.getElementById("reset-form");

  if (resetForm) {
    resetForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const newPass = document.getElementById("new-password").value.trim();
      const confirmPass = document.getElementById("confirm-password").value.trim();

      if (newPass.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return;
      }

      if (newPass !== confirmPass) {
        alert("Las contraseñas no coinciden.");
        return;
      }

      // 🔥 Simulación: Aquí iría el envío al backend con el token recibido en la URL
      alert("¡Contraseña actualizada con éxito!");
      window.location.href = "login.html";
    });
  }
});
