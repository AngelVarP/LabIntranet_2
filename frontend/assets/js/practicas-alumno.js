document.addEventListener('DOMContentLoaded', () => {
    console.log('Practicas.js cargado para el Alumno. Listo para gestionar prácticas.');

    // --- Elementos del DOM ---
    const tablaPracticasBody = document.getElementById('tabla-practicas').getElementsByTagName('tbody')[0];
    const inputBuscar = document.getElementById('buscar-practica');

    let practicasData = []; 

    // --- Simulación de Datos de Prácticas ---
    function cargarPracticas() {
        // Simulación de datos para el alumno:
        practicasData = [
            { id: 'P001', titulo: 'Termodinámica Básica', fecha: '2025-05-10', estado: 'Publicada' },
            { id: 'P002', titulo: 'Síntesis de Polímeros', fecha: '2025-05-15', estado: 'Borrador' },
            { id: 'P003', titulo: 'Manejo de Reactivos II', fecha: '2025-05-20', estado: 'Archivada' }
        ];

        aplicarFiltros(); 
    }

    function obtenerEstadoHTML(estado) {
        switch (estado.toLowerCase()) {
            case 'publicada':
                return '<span class="status aprobada">PUBLICADA</span>';
            case 'borrador':
                return '<span class="status pendiente">BORRADOR</span>';
            case 'archivada':
                return '<span class="status rechazada">ARCHIVADA</span>';
            default:
                return '<span>-</span>';
        }
    }

    function pintarTabla(practicas) {
        tablaPracticasBody.innerHTML = ''; 
        if (practicas.length === 0) {
            const fila = tablaPracticasBody.insertRow();
            fila.insertCell(0).colSpan = 5;
            fila.cells[0].textContent = 'No se encontraron prácticas con estos criterios.';
            fila.cells[0].style.textAlign = 'center';
            return;
        }

        practicas.forEach(practica => {
            const fila = tablaPracticasBody.insertRow();
            fila.insertCell(0).textContent = practica.id;
            fila.insertCell(1).textContent = practica.titulo;
            fila.insertCell(2).textContent = practica.fecha;
            fila.insertCell(3).innerHTML = obtenerEstadoHTML(practica.estado); 

            const celdaAcciones = fila.insertCell(4);
            celdaAcciones.innerHTML = `
                <button class="btn-detail" onclick="verDetalles('${practica.id}')">📦</button>
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
    
    inputBuscar.addEventListener('input', aplicarFiltros);
    cargarPracticas();
});

// --- Función para ver detalles de la práctica ---
function verDetalles(id) {
    console.log('Viendo detalles de la práctica ID: ' + id);
    alert('Viendo detalles de la práctica ID: ' + id);
}
