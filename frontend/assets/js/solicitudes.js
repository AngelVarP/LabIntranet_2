document.addEventListener('DOMContentLoaded', () => {
    console.log('Solicitudes.js cargado. Listo para gestionar peticiones de materiales.');

    // --- Elementos del DOM ---
    const formSolicitud = document.getElementById('form-crear-solicitud');
    const btnAgregarMaterial = document.getElementById('btn-agregar-material-solicitud');
    const materialesContainer = document.getElementById('materiales-solicitud-container');
    const tablaSolicitudesBody = document.getElementById('tabla-solicitudes').getElementsByTagName('tbody')[0];
    const selectPractica = document.getElementById('practica-select');
    const inputBuscar = document.getElementById('buscar-solicitud');
    const selectFiltroEstado = document.getElementById('filtrar-estado');

    let solicitudesData = []; // Datos de solicitudes históricas

    // --- Funciones de Utilidad (Asegura el estilo de los inputs) ---

    // Función para llenar el select de prácticas (Simulación)
    function cargarPracticasEnSelect() {
        const practicas = [
            { id: 'P001', titulo: 'Termodinámica Básica' },
            { id: 'P002', titulo: 'Síntesis de Polímeros' },
            { id: 'P003', titulo: 'Manejo de Reactivos II' }
        ];

        practicas.forEach(p => {
            const option = document.createElement('option');
            option.value = p.id;
            option.textContent = p.titulo;
            selectPractica.appendChild(option);
        });
    }

    // --- 1. Lógica para Agregar Materiales Dinámicamente ---
    if (btnAgregarMaterial) {
        btnAgregarMaterial.addEventListener('click', () => {
            const nuevoMaterialHTML = `
                <div class="material-item form-group" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 3;">
                        <label>Nombre del material/reactivo</label>
                        <input type="text" placeholder="Gafas de seguridad" required class="form-control" style="margin-bottom: 0;"> 
                    </div>
                    <div style="flex: 1;">
                        <label>Cantidad</label>
                        <input type="number" placeholder="5" min="1" required class="form-control" style="margin-bottom: 0;">
                    </div>
                    <button type="button" class="btn btn-danger btn-quitar-material" style="padding: 8px 10px; height: 38px;">X</button>
                </div>
            `;
            materialesContainer.insertAdjacentHTML('beforeend', nuevoMaterialHTML);
        });
    }

    // --- Lógica para Quitar Materiales Dinámicamente ---
    materialesContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-quitar-material')) {
            e.target.closest('.material-item').remove();
        }
    });

    // --- 2. Lógica para Enviar Solicitud ---
    if (formSolicitud) {
        formSolicitud.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const practicaId = selectPractica.value;
            const fechaRequerida = document.getElementById('fecha-requerida').value;
            const materiales = [];
            
            document.querySelectorAll('#materiales-solicitud-container .material-item').forEach(item => {
                const nombre = item.querySelector('input[type="text"]').value;
                const cantidad = item.querySelector('input[type="number"]').value;
                if (nombre && cantidad) {
                    materiales.push({ nombre, cantidad: parseInt(cantidad) });
                }
            });

            if (materiales.length === 0) {
                alert('Debe agregar al menos un material para la solicitud.');
                return;
            }

            const nuevaSolicitud = {
                practicaId,
                fechaRequerida,
                materiales,
                estado: 'Pendiente' // Estado inicial
            };

            console.log('Nueva solicitud a enviar:', nuevaSolicitud);
            
            // **TODO:** Aquí iría el fetch POST al endpoint del servidor
            
            // Simulación
            alert(`Petición para la práctica ${practicaId} enviada con éxito (Simulado).`);
            formSolicitud.reset();
            materialesContainer.innerHTML = '';
            cargarSolicitudes(); // Recargar la tabla
        });
    }

    // --- 3. Lógica para Cargar y Filtrar Solicitudes ---
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
        // Clases de estado basadas en el patrón de table.css (aprobada, pendiente, rechazada)
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
            // Botones de acción (Detalle)
            celdaAcciones.innerHTML = `
                <button class="btn-detail" onclick="verDetalleSolicitud('${s.id}')">🔍</button>
            `;
            
            // Solo permitir editar/eliminar si está Pendiente (Simulación)
            if (s.estado === 'Pendiente') {
                 celdaAcciones.innerHTML += `
                    <button class="btn-edit" onclick="editarSolicitud('${s.id}')">✏️</button>
                    <button class="btn-delete" onclick="cancelarSolicitud('${s.id}')">🗑️</button>
                `;
            }
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
    cargarPracticasEnSelect();
    cargarSolicitudes();
});

// --- Funciones de Acciones (Disponibles globalmente) ---
function verDetalleSolicitud(id) {
    alert('Ver detalle de la Solicitud ID: ' + id + ' (Aquí se mostraría un modal con la lista de materiales).');
}

function editarSolicitud(id) {
    alert('Editar la Solicitud ID: ' + id + ' (Solo si está Pendiente).');
}

function cancelarSolicitud(id) {
    if(confirm('¿Está seguro de que desea cancelar la Solicitud ID: ' + id + '?')) {
        // **TODO**: Llamada al servidor para cancelar
        alert('Solicitud ' + id + ' cancelada (Simulado).');
        // Recargar tabla
        document.dispatchEvent(new Event('DOMContentLoaded')); 
    }
}