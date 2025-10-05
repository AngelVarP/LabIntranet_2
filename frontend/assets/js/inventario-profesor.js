document.addEventListener('DOMContentLoaded', () => {
    console.log('Inventario-profesor.js cargado. (Solo Lectura)');

    const tablaInventarioBody = document.getElementById('tabla-inventario').getElementsByTagName('tbody')[0];
    const inputBuscar = document.getElementById('buscar-producto');
    const selectCategoria = document.getElementById('filtrar-categoria');
    
    let inventarioData = []; 

    // --- Lógica para determinar el estado de stock usando las clases de table.css ---
    function obtenerEstadoStock(stock, minimo) {
        // Clases de table.css: .stock.low, .stock.medium, .stock.high
        if (stock <= minimo) {
            return '<span class="stock low">BAJO</span>';
        } else if (stock <= minimo * 1.5) {
            return '<span class="stock medium">MEDIO</span>';
        } else {
            return '<span class="stock high">ALTO</span>';
        }
    }

    // --- Lógica para cargar los datos del inventario (Simulación de Fetch) ---
    function cargarInventario() {
        // **TODO:** Código real: Fetch al endpoint del servidor
        
        // Simulación de datos 
        inventarioData = [
            { id: 'P001', producto: 'Ácido Clorhídrico', categoria: 'Reactivos', unidad: 'L', ubicacion: 'Almacén Principal', stock: 5, minimo: 10 },
            { id: 'P002', producto: 'Matraz Aforado 250ml', categoria: 'Vidriería', unidad: 'unid', ubicacion: 'Laboratorio Química 1', stock: 25, minimo: 15 },
            { id: 'P003', producto: 'Guantes de Nitrilo', categoria: 'EPP', unidad: 'caja', ubicacion: 'Almacén Principal', stock: 150, minimo: 50 },
            { id: 'P004', producto: 'Pipeta Automática', categoria: 'Instrumental', unidad: 'unid', ubicacion: 'Laboratorio Física', stock: 8, minimo: 5 }
        ];

        cargarFiltros(inventarioData); 
        aplicarFiltros(); 
    }
    
    // --- Llenar las opciones de filtro de categoría ---
    function cargarFiltros(productos) {
        const categorias = ['Todas las categorías', ...new Set(productos.map(p => p.categoria))];
        selectCategoria.innerHTML = '';
        
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat === 'Todas las categorías' ? '' : cat;
            option.textContent = cat;
            selectCategoria.appendChild(option);
        });
    }

    // --- Función para pintar la tabla con los datos filtrados/buscados ---
    function pintarTabla(productos) {
        tablaInventarioBody.innerHTML = ''; // Limpiar
        
        if (productos.length === 0) {
            const fila = tablaInventarioBody.insertRow();
            fila.insertCell(0).colSpan = 8;
            fila.cells[0].textContent = 'No se encontraron productos con estos criterios.';
            fila.cells[0].style.textAlign = 'center';
            return;
        }

        productos.forEach(p => {
            const fila = tablaInventarioBody.insertRow();
            fila.insertCell(0).textContent = p.id;
            fila.insertCell(1).textContent = p.producto;
            fila.insertCell(2).textContent = p.categoria;
            fila.insertCell(3).textContent = p.unidad;
            fila.insertCell(4).textContent = p.ubicacion;
            fila.insertCell(5).textContent = p.stock;
            fila.insertCell(6).textContent = p.minimo;
            fila.insertCell(7).innerHTML = obtenerEstadoStock(p.stock, p.minimo);
        });
    }

    // --- Aplicar Búsqueda y Filtro ---
    function aplicarFiltros() {
        const textoBusqueda = inputBuscar.value.toLowerCase();
        const categoriaSeleccionada = selectCategoria.value;

        const resultadosFiltrados = inventarioData.filter(producto => {
            const matchText = producto.producto.toLowerCase().includes(textoBusqueda);
            const matchCat = categoriaSeleccionada === '' || producto.categoria === categoriaSeleccionada;
            return matchText && matchCat;
        });

        pintarTabla(resultadosFiltrados);
    }

    // --- Event Listeners para Búsqueda/Filtro ---
    inputBuscar.addEventListener('input', aplicarFiltros);
    selectCategoria.addEventListener('change', aplicarFiltros);

    // --- Inicialización ---
    cargarInventario();
});