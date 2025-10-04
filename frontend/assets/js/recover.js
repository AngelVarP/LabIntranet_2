document.addEventListener("DOMContentLoaded", () => {
  const recoverForm = document.getElementById("recover-form");

  if (recoverForm) {
    recoverForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const code = document.getElementById("recover-code").value.trim().toUpperCase();

      if (code) {
        alert(`Si el código ${code} está registrado, recibirás un correo con un enlace para restablecer tu contraseña.`);
        window.location.href = "login.html";
      } else {
        alert("Por favor, ingresa un código válido.");
      }
    });
  }
});
