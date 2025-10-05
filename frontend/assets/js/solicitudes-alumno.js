document.addEventListener('DOMContentLoaded', () => {
    console.log('Solicitudes.js cargado para el Alumno. Listo para gestionar peticiones de materiales.');

    // --- Elementos del DOM ---
    const tablaSolicitudesBody = document.getElementById('tabla-solicitudes').getElementsByTagName('tbody')[0];
    const inputBuscar = document.getElementById('buscar-solicitud');
    const selectFiltroEstado = document.getElementById('filtrar-estado');

    let solicitudesData = []; // Datos de solicitudes históricas

    // --- Funciones de Utilidad (Asegura el estilo de los inputs) ---

    // --- 1. Lógica para Cargar y Filtrar Solicitudes ---
    function cargarSolicitudes() {
        // Simulación de datos:
        solicitudesData = [
            { id: 'S001', practica: 'Termodinámica Básica', fecha_uso: '2025-06-01', estado: 'Pendiente' },
            { id: 'S002', practica: 'Síntesis de Polímeros', fecha_uso: '2025-05-25', estado: 'Aprobada' },
            { id: 'S003', practica: 'Manejo de Reactivos II', fecha_uso: '2025-06-15', estado: 'Rechazada' },
            { id: 'S004', practica: 'Termodinámica Básica', fecha_uso: '2025-05-05', estado: 'Aprobada' }
        ];
        aplicarFiltros();
    }

    function obtenerEstadoHTML(estado) {
        switch (estado.toLowerCase()) {
            case 'aprobada':
                return '<span class="status aprobada">APROBADA</span>';
            case 'pendiente':
                return '<span class="status pendiente">PENDIENTE</span>';
            case 'rechazada':
                return '<span class="status rechazada">RECHAZADA</span>';
            default:
                return '<span>-</span>';
        }
    }

    function pintarTabla(solicitudes) {
        tablaSolicitudesBody.innerHTML = '';
        if (solicitudes.length === 0) {
            const fila = tablaSolicitudesBody.insertRow();
            fila.insertCell(0).colSpan = 5;
            fila.cells[0].textContent = 'No se encontraron peticiones con estos criterios.';
            fila.cells[0].style.textAlign = 'center';
            return;
        }

        solicitudes.forEach(s => {
            const fila = tablaSolicitudesBody.insertRow();
            fila.insertCell(0).textContent = s.id;
            fila.insertCell(1).textContent = s.practica;
            fila.insertCell(2).textContent = s.fecha_uso;
            fila.insertCell(3).innerHTML = obtenerEstadoHTML(s.estado); 

            const celdaAcciones = fila.insertCell(4);
            // Solo permitir ver detalles para los alumnos
            celdaAcciones.innerHTML = `
                <button class="btn-detail" onclick="verDetalleSolicitud('${s.id}')">🔍</button>
            `;
        });
    }

    function aplicarFiltros() {
        const textoBusqueda = inputBuscar.value.toLowerCase();
        const estadoSeleccionado = selectFiltroEstado.value;

        const resultadosFiltrados = solicitudesData.filter(solicitud => {
            const matchText = solicitud.practica.toLowerCase().includes(textoBusqueda) ||
                              solicitud.id.toLowerCase().includes(textoBusqueda);
            const matchEstado = estadoSeleccionado === '' || solicitud.estado === estadoSeleccionado;
            return matchText && matchEstado;
        });

        pintarTabla(resultadosFiltrados);
    }

    // --- Event Listeners para Filtros ---
    inputBuscar.addEventListener('input', aplicarFiltros);
    selectFiltroEstado.addEventListener('change', aplicarFiltros);
    
    // --- Inicialización ---
    cargarSolicitudes();
});

// --- Funciones de Acciones (Disponibles globalmente) ---
function verDetalleSolicitud(id) {
    alert('Ver detalle de la Solicitud ID: ' + id + ' (Aquí se mostraría un modal con la lista de materiales).');
}
