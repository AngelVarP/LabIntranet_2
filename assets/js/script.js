// Validación simple para asegurarse de que los campos no estén vacíos
document.getElementById("login-form").addEventListener("submit", function(event) {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!email || !password) {
        event.preventDefault(); // Previene el envío del formulario si los campos están vacíos
        alert("Por favor, completa todos los campos.");
    }
});
