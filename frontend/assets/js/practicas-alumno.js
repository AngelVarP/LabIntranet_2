// assets/js/practicas-alumno.js

document.addEventListener('DOMContentLoaded', async () => {
  console.log('practicas-alumno.js cargado');

  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");
  const tablaBody = document.querySelector('#tabla-practicas tbody');
  const inputBuscar = document.getElementById('buscar-practica');

  if (!TOKEN) {
    alert('No tienes sesión iniciada');
    window.location.href = '../login.html';
    return;
  }

  let practicas = [];

  async function cargarPracticas() {
    tablaBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Cargando prácticas...</td></tr>`;
    try {
      const res = await fetch(`${API_BASE}/practicas/listar.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();

      if (!Array.isArray(data)) {
        tablaBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error al cargar prácticas</td></tr>`;
        return;
      }

      practicas = data.map(p => ({
        id: p.id,
        titulo: p.nombre,
        fecha: p.fecha || '-',
        estado: p.estado || 'Publicada'
      }));
      renderTable(practicas);
    } catch (err) {
      console.error(err);
      tablaBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">Error de conexión</td></tr>`;
    }
  }

  function renderTable(data) {
    tablaBody.innerHTML = '';
    if (!data.length) {
      tablaBody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No hay prácticas</td></tr>`;
      return;
    }
    data.forEach(practica => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${practica.id}</td>
        <td>${practica.titulo}</td>
        <td>${practica.fecha}</td>
        <td>${practica.estado}</td>
        <td><button class="btn-detail" onclick="verDetalles('${practica.id}')">📦</button></td>
      `;
      tablaBody.appendChild(tr);
    });
  }

  inputBuscar.addEventListener('input', () => {
    const term = inputBuscar.value.toLowerCase();
    const filtradas = practicas.filter(p => p.titulo.toLowerCase().includes(term));
    renderTable(filtradas);
  });

  cargarPracticas();
});

function verDetalles(id) {
  alert('Detalles de práctica ID: ' + id);
}
