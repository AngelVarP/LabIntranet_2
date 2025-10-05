document.addEventListener('DOMContentLoaded', () => {
    console.log('Practicas.js cargado. Listo para gestionar prácticas y sus materiales.');

    // --- Elementos del DOM ---
    const formPractica = document.getElementById('form-crear-practica');
    const btnAgregarMaterial = document.getElementById('btn-agregar-material');
    const materialesContainer = document.getElementById('materiales-container');
    const tablaPracticasBody = document.getElementById('tabla-practicas').getElementsByTagName('tbody')[0];
    const inputBuscar = document.getElementById('buscar-practica');

    let practicasData = []; 

    // --- Estilos oscuros que faltan en form-control (si es necesario) ---
    // NOTA: Estos estilos deberían estar en main.css/profesor.css, pero los ponemos aquí 
    // en el HTML para forzar el color oscuro en los inputs dinámicos.
    const darkInputStyle = "background: rgba(255,255,255,0.08); color: #fff; border: 1px solid rgba(255,255,255,0.2);";


    // --- 1. Lógica para Agregar Materiales Dinámicamente ---
    if (btnAgregarMaterial) {
        btnAgregarMaterial.addEventListener('click', () => {
            const nuevoMaterialHTML = `
                <div class="material-item form-group" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 3;">
                        <label>Nombre del material</label>
                        <input type="text" placeholder="Vaso de precipitado" required class="form-control" style="margin-bottom: 0; ${darkInputStyle}"> 
                    </div>
                    <div style="flex: 1;">
                        <label>Cantidad</label>
                        <input type="number" placeholder="1" min="1" required class="form-control" style="margin-bottom: 0; ${darkInputStyle}">
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

    // ... (El resto de la función cargarPracticas, obtenerEstadoHTML, pintarTabla, aplicarFiltros, etc., sigue igual) ...

    function cargarPracticas() {
        // Simulación de datos:
        practicasData = [
            { id: 'P001', titulo: 'Termodinámica Básica', fecha: '2025-05-10', estado: 'Publicada' },
            { id: 'P002', titulo: 'Síntesis de Polímeros', fecha: '2025-05-15', estado: 'Borrador' },
            { id: 'P003', titulo: 'Manejo de Reactivos II', fecha: '2025-05-20', estado: 'Archivada' }
        ];

        aplicarFiltros(); 
    }

    function obtenerEstadoHTML(estado) {
        // Simulación de clases de estado basadas en el patrón de table.css
        switch (estado.toLowerCase()) {
            case 'publicada':
                return '<span class="status aprobada">PUBLICADA</span>'; // Usamos aprobada como similar a publicada
            case 'borrador':
                return '<span class="status pendiente">BORRADOR</span>'; // Usamos pendiente para algo "en espera"
            case 'archivada':
                return '<span class="status rechazada">ARCHIVADA</span>'; // Usamos rechazada como similar a archivada/no activa
            default:
                return '<span>-</span>';
        }
    }

    function pintarTabla(productos) {
        tablaPracticasBody.innerHTML = ''; 
        if (productos.length === 0) {
            const fila = tablaPracticasBody.insertRow();
            fila.insertCell(0).colSpan = 5;
            fila.cells[0].textContent = 'No se encontraron prácticas con estos criterios.';
            fila.cells[0].style.textAlign = 'center';
            return;
        }

        productos.forEach(practica => {
            const fila = tablaPracticasBody.insertRow();
            fila.insertCell(0).textContent = practica.id;
            fila.insertCell(1).textContent = practica.titulo;
            fila.insertCell(2).textContent = practica.fecha;
            fila.insertCell(3).innerHTML = obtenerEstadoHTML(practica.estado); 

            const celdaAcciones = fila.insertCell(4);
            celdaAcciones.innerHTML = `
                <button class="btn-edit" onclick="editarPractica('${practica.id}')">✏️</button>
                <button class="btn-detail" onclick="solicitarInsumos('${practica.id}')">📦</button>
            `;
        });
    }

    function aplicarFiltros() {
        const textoBusqueda = inputBuscar.value.toLowerCase();

        const resultadosFiltrados = practicasData.filter(practica => {
            return practica.titulo.toLowerCase().includes(textoBusqueda);
        });

        pintarTabla(resultadosFiltrados);
    }
    
    // --- Event Listeners y Lógica de Envío ---
    if (formPractica) {
        formPractica.addEventListener('submit', (e) => {
            e.preventDefault();
            const titulo = document.getElementById('titulo').value;
            alert(`Práctica "${titulo}" creada con éxito (Simulado).`);
            formPractica.reset(); 
            materialesContainer.innerHTML = ''; 
            cargarPracticas();
        });
    }

    inputBuscar.addEventListener('input', aplicarFiltros);
    cargarPracticas();
});

// --- Funciones de Acciones (Disponibles globalmente) ---
function editarPractica(id) {
    console.log('Función de Editar Práctica ID: ' + id);
    alert('Función de Editar Práctica ID: ' + id);
}

function solicitarInsumos(id) {
    console.log('Redirigiendo a Solicitudes para la Práctica ID: ' + id);
    alert('Redirigiendo a Solicitudes para la Práctica ID: ' + id);
}