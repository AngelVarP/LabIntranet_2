@extends('layouts.lab')
@section('title','Tablón de Pedidos')

@section('content')
<h1 class="text-xl font-semibold mb-4">Tablón de Pedidos</h1>

<div class="bg-white rounded-xl shadow p-4 mb-4">
  <form id="filtros" class="grid sm:grid-cols-4 gap-3">
    <input name="q" type="text" placeholder="Buscar…" class="border rounded px-3 py-2" />
    <select name="estado" class="border rounded px-3 py-2">
      <option value="">Estado (todos)</option>
      <option>PENDIENTE</option><option>APROBADO</option><option>RECHAZADO</option>
      <option>PREPARADO</option><option>ENTREGADO</option><option>CERRADO</option>
    </select>
    <input name="curso_id" type="number" step="1" placeholder="Curso ID" class="border rounded px-3 py-2" />
    <input name="laboratorio_id" type="number" step="1" placeholder="Lab ID" class="border rounded px-3 py-2" />
    <div class="sm:col-span-4 flex gap-2">
      <button class="bg-[#0c4a6e] text-white px-4 py-2 rounded">Aplicar</button>
      <button type="button" id="limpiar" class="px-4 py-2 rounded border">Limpiar</button>
    </div>
  </form>
</div>

<div id="tablaWrap" class="bg-white rounded-xl shadow overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-100">
      <tr>
        <th class="text-left p-2">Solicitud</th>
        <th class="text-left p-2">Laboratorio</th>
        <th class="text-left p-2">Curso / Sección</th>
        <th class="text-left p-2">Práctica</th>
        <th class="text-left p-2">Grupo</th>
        <th class="text-left p-2">Estado</th>
        <th class="text-left p-2">Actualizado</th>
      </tr>
    </thead>
    <tbody id="tbody"></tbody>
  </table>
  <div id="paginacion" class="p-3 flex justify-between items-center text-sm"></div>
</div>

<script>
const tbody = document.querySelector('#tbody');
const pagDiv = document.querySelector('#paginacion');
const form   = document.querySelector('#filtros');
const btnLim = document.querySelector('#limpiar');

function renderRows(data){
  tbody.innerHTML = '';
  data.forEach(v => {
    const tr = document.createElement('tr');
    tr.className = 'border-t';
    tr.innerHTML = `
      <td class="p-2">#${v.solicitud_id}</td>
      <td class="p-2">${v.laboratorio_nombre ?? ''}</td>
      <td class="p-2">${v.curso_nombre ?? ''} / ${v.seccion_nombre ?? ''}</td>
      <td class="p-2">${v.practica_titulo ?? ''}</td>
      <td class="p-2">${v.grupo_nombre ?? ''}</td>
      <td class="p-2"><span class="px-2 py-1 rounded bg-slate-100">${v.estado}</span></td>
      <td class="p-2">${v.actualizado_at ?? ''}</td>
    `;
    tbody.appendChild(tr);
  });
}

function renderPag(meta){
  const { current_page, last_page, next_page_url, prev_page_url, total } = meta;
  pagDiv.innerHTML = `
    <div>Total: <b>${total}</b></div>
    <div class="flex items-center gap-2">
      <button ${prev_page_url?'':'disabled'} data-url="${prev_page_url||''}" class="px-3 py-1 border rounded">« Anterior</button>
      <span>Página ${current_page} / ${last_page}</span>
      <button ${next_page_url?'':'disabled'} data-url="${next_page_url||''}" class="px-3 py-1 border rounded">Siguiente »</button>
    </div>
  `;
  pagDiv.querySelectorAll('button[data-url]').forEach(b=>{
    b.addEventListener('click', (e)=>{
      const url = e.currentTarget.getAttribute('data-url');
      if(url) load(url);
    });
  });
}

async function load(url){
  const res = await fetch(url, { credentials: 'include' });
  if(!res.ok){ tbody.innerHTML = `<tr><td class="p-3 text-red-600">Error ${res.status}</td></tr>`; return; }
  const json = await res.json();
  renderRows(json.data || []);
  renderPag({
    current_page: json.current_page,
    last_page: json.last_page,
    next_page_url: json.next_page_url,
    prev_page_url: json.prev_page_url,
    total: json.total
  });
}

form.addEventListener('submit', (e)=>{
  e.preventDefault();
  const p = new URLSearchParams(new FormData(form));
  load('/api/tablon?'+p.toString());
});
btnLim.addEventListener('click', ()=>{
  form.reset();
  load('/api/tablon');
});

load('/api/tablon');
</script>
@endsection
