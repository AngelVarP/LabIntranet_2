document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/LabIntranet_2/backend/api";
  const TOKEN = localStorage.getItem("token");
  const selectPractica = document.getElementById("select-practica");
  const formReporte = document.getElementById("form-generar-reporte");
  const loader = document.getElementById("loader");
  const btnText = document.getElementById("btn-text");
  const msg = document.getElementById("report-message");

  // ===== CARGAR PRÁCTICAS =====
  async function cargarPracticas() {
    try {
      const res = await fetch(`${API_BASE}/reportes/practicas.php`, {
        headers: { Authorization: `Bearer ${TOKEN}` }
      });
      const data = await res.json();
      data.forEach(p => {
        const opt = document.createElement("option");
        opt.value = p.id;
        opt.textContent = `${p.nombre} (${p.grupos_asignados} grupos)`;
        selectPractica.appendChild(opt);
      });
    } catch (err) {
      console.error("Error cargando prácticas:", err);
    }
  }

  // ===== GENERAR REPORTE =====
  formReporte.addEventListener("submit", async (e) => {
    e.preventDefault();
    loader.style.display = "inline-block";
    btnText.textContent = "Generando...";
    msg.style.display = "none";

    const practica = selectPractica.value;
    const inicio = document.getElementById("input-fecha-inicio").value;
    const fin = document.getElementById("input-fecha-fin").value;

    try {
      const url = new URL(`${API_BASE}/reportes/uso_materiales.php`, window.location.origin);
      if(practica) url.searchParams.set("practica", practica);
      if(inicio) url.searchParams.set("inicio", inicio);
      if(fin) url.searchParams.set("fin", fin);

      const res = await fetch(url, { headers:{Authorization:`Bearer ${TOKEN}`}});
      const data = await res.json();

      if(!Array.isArray(data) || !data.length){
        alert("No hay datos para el reporte.");
        loader.style.display = "none";
        btnText.textContent = "Descargar Reporte (CSV/PDF)";
        return;
      }

      // Generar CSV
      let csv = "Producto,Cantidad Usada\n";
      data.forEach(d => {
        csv += `"${d.producto}","${d.total_usado}"\n`;
      });
      const blob = new Blob([csv], {type:"text/csv"});
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "reporte_materiales.csv";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      msg.style.display = "block";
    } catch (err) {
      console.error(err);
      alert("Error generando reporte");
    }

    loader.style.display = "none";
    btnText.textContent = "Descargar Reporte (CSV/PDF)";
  });

  cargarPracticas();
});
