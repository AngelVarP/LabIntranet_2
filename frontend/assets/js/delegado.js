/* delegado.js
 * Manejo de grupo y alumnos para el rol Delegado
 */

const API_BASE = "/LabIntranet_2/backend/api";
const TOKEN = localStorage.getItem("token");

if (!TOKEN) {
  alert("No tienes sesión iniciada");
  window.location.href = "../login.html";
}

const secCrear = document.getElementById("crear-grupo");
const secInfo = document.getElementById("info-grupo");
const secTabla = document.getElementById("alumnos-grupo");
const secSelector = document.getElementById("selector-alumnos");

const nombreActual = document.getElementById("nombre-grupo-actual");
const codigoGrupo = document.getElementById("codigo-grupo");
const tablaIntegrantes = document.getElementById("tabla-integrantes");
const listaDisponibles = document.getElementById("lista-alumnos-disponibles");

let grupoId = null;

async function cargarGrupo() {
  secCrear.style.display = "none";
  secInfo.style.display = "none";
  secTabla.style.display = "none";
  secSelector.style.display = "none";

  try {
    const res = await fetch(`${API_BASE}/grupos/mi_grupo.php`, {
      headers: { Authorization: `Bearer ${TOKEN}` }
    });
    const data = await res.json();

    if (!data) {
      // No hay grupo
      secCrear.style.display = "block";
      return;
    }

    grupoId = data.id;
    nombreActual.textContent = data.nombre;
    codigoGrupo.textContent = data.id;
    secInfo.style.display = "block";
    secTabla.style.display = "block";

    tablaIntegrantes.innerHTML = "";
    if (Array.isArray(data.miembros) && data.miembros.length > 0) {
      data.miembros.forEach(m => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${m.codigo}</td>
          <td>${m.nombre}</td>
          <td>${m.correo || "-"}</td>
          <td><button class="btn-danger" data-id="${m.id}">Eliminar</button></td>
        `;
        tablaIntegrantes.appendChild(tr);
      });
    } else {
      tablaIntegrantes.innerHTML = "<tr><td colspan='4'>Sin integrantes</td></tr>";
    }

  } catch (e) {
    console.error(e);
    alert("Error al cargar el grupo");
  }
}

async function crearGrupo(e) {
  e.preventDefault();
  const nombre = document.getElementById("nombre-grupo").value.trim();
  if (!nombre) return alert("Escribe un nombre para el grupo");

  try {
    const res = await fetch(`${API_BASE}/grupos/crear.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${TOKEN}` },
      body: JSON.stringify({ nombre })
    });
    const data = await res.json();
    if (data.success) {
      alert("Grupo creado correctamente");
      cargarGrupo();
    } else {
      alert(data.message || "No se pudo crear el grupo");
    }
  } catch (e) {
    console.error(e);
    alert("Error de conexión");
  }
}

async function eliminarAlumno(id) {
  if (!confirm("¿Eliminar este alumno del grupo?")) return;
  try {
    const res = await fetch(`${API_BASE}/grupo_alumnos/eliminar.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${TOKEN}` },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      cargarGrupo();
    } else {
      alert(data.message || "No se pudo eliminar");
    }
  } catch (e) {
    console.error(e);
    alert("Error al eliminar");
  }
}

function mostrarSelector() {
  secSelector.style.display = "block";
  listaDisponibles.innerHTML = `
    <p>Escribe el código del alumno y presiona "Agregar seleccionados".</p>
    <input type="text" id="codigo-alumno" placeholder="Código del alumno" />
  `;
}

async function agregarAlumno() {
  const codigo = document.getElementById("codigo-alumno").value.trim();
  if (!codigo) return alert("Ingrese un código");
  try {
    const res = await fetch(`${API_BASE}/grupo_alumnos/agregar.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Authorization: `Bearer ${TOKEN}` },
      body: JSON.stringify({ codigo })
    });
    const data = await res.json();
    if (data.success) {
      alert("Alumno agregado");
      secSelector.style.display = "none";
      cargarGrupo();
    } else {
      alert(data.message || "No se pudo agregar");
    }
  } catch (e) {
    console.error(e);
    alert("Error al agregar alumno");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("form-crear-grupo")
    ?.addEventListener("submit", crearGrupo);

  document.getElementById("btn-agregar-alumnos")
    ?.addEventListener("click", mostrarSelector);

  document.getElementById("btn-confirmar-agregar")
    ?.addEventListener("click", agregarAlumno);

  document.getElementById("btn-cancelar-agregar")
    ?.addEventListener("click", () => secSelector.style.display = "none");

  tablaIntegrantes.addEventListener("click", e => {
    if (e.target.matches(".btn-danger")) {
      eliminarAlumno(e.target.dataset.id);
    }
  });

  cargarGrupo();
});
