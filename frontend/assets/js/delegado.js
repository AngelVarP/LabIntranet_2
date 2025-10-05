document.addEventListener('DOMContentLoaded', () => {
    // === Cargar info de grupo ===
    fetch('../../backend/delegado/get_grupo.php')
        .then(res => res.json())
        .then(data => {
            if (data) {
                // Dashboard
                const gNombre = document.getElementById('grupo-nombre');
                const gSeccion = document.getElementById('grupo-seccion');
                const dNombre = document.getElementById('delegado-nombre');
                const total = document.getElementById('total-alumnos');

                if (gNombre) gNombre.textContent = data.grupo_nombre || 'N/A';
                if (gSeccion) gSeccion.textContent = data.grupo_seccion || 'N/A';
                if (dNombre) dNombre.textContent = data.delegado_nombre || 'N/A';
                if (total) total.textContent = data.alumnos ? data.alumnos.length : 0;

                // Grupo.html - tabla alumnos
                const tabla = document.getElementById('tabla-alumnos');
                if (tabla && data.alumnos) {
                    tabla.innerHTML = '';
                    data.alumnos.forEach(al => {
                        tabla.innerHTML += `<tr><td>${al.code}</td><td>${al.nombre}</td><td>${al.email}</td></tr>`;
                    });
                }
            }
        });

    // === Cargar últimas solicitudes ===
    fetch('../../backend/delegado/get_peticiones.php')
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById('tabla-solicitudes');
            if (tabla && data.length > 0) {
                tabla.innerHTML = '';
                data.slice(0,5).forEach(p => {
                    tabla.innerHTML += `<tr><td>${p.insumo}</td><td>${p.cantidad}</td><td>${p.estado}</td><td>${p.fecha}</td></tr>`;
                });
            }
        });
});
// === Enviar nueva solicitud ===
const form = document.getElementById('form-peticion');
if (form) {
    form.addEventListener('submit', e => {
        e.preventDefault();
        const datos = new FormData(form);
        fetch('../../backend/delegado/add_peticion.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(resp => {
            const msg = document.getElementById('mensaje-peticion');
            msg.textContent = resp.message || 'Solicitud enviada correctamente';
            msg.className = resp.success ? 'alert alert-success' : 'alert alert-error';
            if (resp.success) form.reset();
        });
    });
}

