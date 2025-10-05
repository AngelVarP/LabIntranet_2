document.addEventListener('DOMContentLoaded', () => {
    console.log('Reportes-profesor.js cargado. Listo para generar reportes.');

    const formReporte = document.getElementById('form-generar-reporte');
    const selectPractica = document.getElementById('select-practica');
    const reportMessage = document.getElementById('report-message');
    const btnText = document.getElementById('btn-text');
    const loader = document.getElementById('loader');

    // --- Lógica para cargar el listado de prácticas en el select ---
    function cargarListaPracticas() {
        // **TODO:** Llamada al servidor (fetch) para obtener la lista de prácticas del profesor
        
        // Simulación de datos:
        const practicas = [
            { id: 'P001', titulo: 'Termodinámica Básica' },
            { id: 'P002', titulo: 'Síntesis de Polímeros' },
            { id: 'P003', titulo: 'Manejo de Reactivos II' }
        ];

        // Aseguramos que la opción de "Todas" se mantenga y añadimos las dinámicas
        practicas.forEach(p => {
            const option = document.createElement('option');
            option.value = p.id;
            option.textContent = p.titulo;
            selectPractica.appendChild(option);
        });
    }

    // --- Lógica para generar y descargar reportes ---
    if (formReporte) {
        formReporte.addEventListener('submit', (e) => {
            e.preventDefault();

            // Deshabilitar formulario y mostrar "cargando"
            formReporte.querySelector('button[type="submit"]').disabled = true;
            btnText.textContent = 'Generando...';
            reportMessage.style.display = 'block';

            const practicaId = selectPractica.value;
            const fechaInicio = document.getElementById('input-fecha-inicio').value;
            const fechaFin = document.getElementById('input-fecha-fin').value;

            console.log(`Solicitando reporte para Práctica ID: ${practicaId}, Desde: ${fechaInicio}, Hasta: ${fechaFin}`);

            // SIMULACIÓN DE RETARDO Y DESCARGA (se ejecuta después de 2 segundos)
            setTimeout(() => {
                
                // Revertir el estado del botón
                formReporte.querySelector('button[type="submit"]').disabled = false;
                btnText.textContent = 'Descargar Reporte (CSV/PDF)';
                reportMessage.style.display = 'none';

                alert('Descarga de reporte simulada completada. Revisa tus descargas.');

            }, 2000); // Retardo de 2 segundos
        });
    }
    
    // --- Inicialización ---
    cargarListaPracticas();
});