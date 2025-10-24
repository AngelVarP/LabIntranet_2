<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import { API } from '../../api/api.js'

const filtros = reactive({ desde:'', hasta:'', estado:'', laboratorio_id:'', curso_id:'' })
const labs   = ref([]); const cursos = ref([])
const kpi    = ref({ solicitudesPendientes:0, enPreparacion:0, prestamosAbiertos:0 })
const loading = ref(true); const error = ref(null)

function qs(obj){ const p=new URLSearchParams(); Object.entries(obj).forEach(([k,v])=>{ if(v!==''&&v!=null)p.set(k,v) }); return p.toString() }

async function loadLookups(){
  try{
    const [l, c] = await Promise.all([
      axios.get('/api/lookups/laboratorios'),
      axios.get('/api/lookups/cursos')
    ])
    labs.value   = l.data?.data ?? l.data ?? []
    cursos.value = c.data?.data ?? c.data ?? []
  }catch{}
}
async function loadKPIs(){
  loading.value=true; error.value=null
  try{
    const { data } = await axios.get(API.dashResumen)
    if (data && typeof data === 'object'){
      kpi.value = {
        solicitudesPendientes: Number(data.solicitudesPendientes ?? 0),
        enPreparacion: Number(data.enPreparacion ?? 0),
        prestamosAbiertos: Number(data.prestamosAbiertos ?? 0),
      }
    }
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}
function exportar(formato='csv'){
  let url=''
  if (formato==='pdf') {
    url = `${API.reportPdfSolicitudes}?${qs(filtros)}`
  } else {
    url = `${API.reportCsvPrestamos}?${qs(filtros)}`
    // Alternativas:
    // url = `${API.reportCsvInsumos}?${qs(filtros)}`
    // url = `${API.reportCsvKardex}?${qs(filtros)}`
    // url = `${API.reportCsvSolicitudes}?${qs(filtros)}`
  }
  window.open(url, '_blank')
}
function aplicar(){ loadKPIs() }

onMounted(async () => { await loadLookups(); aplicar() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-slate-800">Reportes</h1>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-6">
          <div class="md:col-span-2">
            <label class="block text-xs text-slate-500 mb-1">Desde</label>
            <input v-model="filtros.desde" type="date" class="w-full rounded border p-2"/>
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs text-slate-500 mb-1">Hasta</label>
            <input v-model="filtros.hasta" type="date" class="w-full rounded border p-2"/>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Estado</label>
            <select v-model="filtros.estado" class="w-full rounded border p-2">
              <option value="">(todos)</option>
              <option value="PENDIENTE">PENDIENTE</option>
              <option value="APROBADO">APROBADO</option>
              <option value="RECHAZADO">RECHAZADO</option>
              <option value="PREPARADO">PREPARADO</option>
              <option value="ENTREGADO">ENTREGADO</option>
              <option value="CERRADO">CERRADO</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Laboratorio</label>
            <select v-model="filtros.laboratorio_id" class="w-full rounded border p-2">
              <option value="">(todos)</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
          </div>
          <div class="md:col-span-3">
            <label class="block text-xs text-slate-500 mb-1">Curso</label>
            <select v-model="filtros.curso_id" class="w-full rounded border p-2">
              <option value="">(todos)</option>
              <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.codigo }} — {{ c.nombre }}</option>
            </select>
          </div>
          <div class="md:col-span-3 flex items-end justify-end gap-2">
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="aplicar">Aplicar</button>
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="exportar('csv')">Exportar CSV</button>
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="exportar('pdf')">Exportar PDF</button>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

      <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Solicitudes pendientes</div>
          <div class="mt-1 text-3xl font-semibold">{{ kpi.solicitudesPendientes }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">En preparación</div>
          <div class="mt-1 text-3xl font-semibold">{{ kpi.enPreparacion }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Préstamos abiertos</div>
          <div class="mt-1 text-3xl font-semibold">{{ kpi.prestamosAbiertos }}</div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
