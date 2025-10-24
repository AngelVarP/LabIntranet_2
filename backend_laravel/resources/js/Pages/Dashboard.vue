<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

const kpis   = ref(null)
const error  = ref(null)
const desde  = ref(new Date(Date.now()-14*864e5).toISOString().slice(0,10))
const hasta  = ref(new Date().toISOString().slice(0,10))
const series = ref({ solicitudes:[], entregas:[], rango:{desde:'',hasta:''} })

async function loadKpis(){
  try{
    const { data } = await axios.get('/api/dashboard/kpis', { withCredentials:true })
    kpis.value = data
  }catch(e){
    error.value = e?.response?.data?.message || e.message
  }
}

async function loadSeries(){
  try{
    const { data } = await axios.get('/api/dashboard/series', {
      withCredentials:true,
      params: { desde: desde.value, hasta: hasta.value }
    })
    series.value = data
  }catch(e){
    error.value = e?.response?.data?.message || e.message
  }
}

onMounted(() => { loadKpis(); loadSeries() })
watch([desde, hasta], loadSeries)

// --- util: escalar data a SVG ---
function toPoints(list, w=640, h=140, pad=10){
  if(!list?.length) return ''
  const xs = list.map((_,i)=>i)
  const ys = list.map(p=>Number(p.c)||0)
  const minY = 0
  const maxY = Math.max(1, ...ys)
  const stepX = (w-2*pad)/Math.max(1,(xs.length-1))
  return list.map((p,i)=>{
    const x = pad + i*stepX
    const y = pad + (h-2*pad) * (1 - (Number(p.c)-minY)/(maxY-minY))
    return `${x},${y}`
  }).join(' ')
}
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-slate-800">Dashboard</h1>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

      <!-- KPIs -->
      <div v-if="kpis" class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Pendientes</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ kpis.solicitudes.PENDIENTE }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Aprobadas</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ kpis.solicitudes.APROBADO }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Preparadas</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ kpis.solicitudes.PREPARADO }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Entregadas hoy</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ kpis.entregas_hoy }}</div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Bajo mínimo</div>
          <div class="mt-1 text-2xl font-semibold text-amber-700">{{ kpis.alertas.bajo_minimo }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Caducan ≤30 días</div>
          <div class="mt-1 text-2xl font-semibold text-rose-700">{{ kpis.alertas.caducan_30d }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Préstamos abiertos</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ kpis.prestamos_abiertos }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="text-xs text-slate-500">Fecha</div>
          <div class="mt-1 text-lg text-slate-700">{{ kpis.fecha }}</div>
        </div>
      </div>

      <!-- Serie -->
      <div class="rounded-xl bg-white p-4 shadow-sm">
        <div class="mb-3 flex flex-wrap items-end gap-3">
          <div>
            <label class="text-xs text-slate-500">Desde</label>
            <input type="date" v-model="desde" class="mt-1 rounded border px-2 py-1" />
          </div>
          <div>
            <label class="text-xs text-slate-500">Hasta</label>
            <input type="date" v-model="hasta" class="mt-1 rounded border px-2 py-1" />
          </div>
        </div>

        <div class="overflow-x-auto">
          <svg :width="720" :height="200" class="rounded border bg-slate-50">
            <!-- grid simple -->
            <line x1="40" y1="10" x2="40" y2="190" stroke="#cbd5e1" />
            <line x1="40" y1="190" x2="710" y2="190" stroke="#cbd5e1" />

            <!-- solicitudes -->
            <polyline
              :points="toPoints(series.solicitudes, 720, 200, 40)"
              fill="none" stroke="#0ea5e9" stroke-width="2" />
            <!-- entregas -->
            <polyline
              :points="toPoints(series.entregas, 720, 200, 40)"
              fill="none" stroke="#10b981" stroke-width="2" />

            <text x="50" y="24" font-size="12" fill="#0ea5e9">Solicitudes</text>
            <text x="140" y="24" font-size="12" fill="#10b981">Entregas</text>
          </svg>
        </div>
      </div>
    </div>
  </AppShell>
</template>
