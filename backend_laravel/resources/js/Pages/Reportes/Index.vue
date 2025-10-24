<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

const labs = ref([])
const cargandoLabs = ref(false)
const error = ref(null)

// Filtros
const fKardex = ref({
  laboratorio_id: '',
  tipo_item: '',
  item_id: '',
  tipo_mov: '',
  desde: '',
  hasta: '',
})
const fPrestamos = ref({
  estado: '',
  responsable_id: '',
  desde: '',
  hasta: '',
})
const fSolicitudes = ref({
  estado: '',
  laboratorio_id: '',
  desde: '',
  hasta: '',
})
const fDevol = ref({
  laboratorio_id: '',
  equipo_id: '',
  responsable_id: '',
  estado_equipo: '',
  desde: '',
  hasta: '',
})

// JSON rápidos (vista previa)
const bajoMinimo = ref(null)
const incidencias = ref(null)
const cargandoBM = ref(false)
const cargandoInc = ref(false)

function buildUrl(base, params) {
  const p = new URLSearchParams()
  Object.entries(params).forEach(([k,v])=>{
    if (v !== null && v !== undefined && String(v).trim() !== '') p.append(k, v)
  })
  const qs = p.toString()
  return qs ? `${base}?${qs}` : base
}

async function ensureCsrf () {
  try { await axios.get('/sanctum/csrf-cookie', { withCredentials:true }) } catch {}
}

async function loadLabs(){
  cargandoLabs.value = true
  error.value = null
  await ensureCsrf()
  try {
    const { data } = await axios.get('/api/lookups/laboratorios', { withCredentials:true })
    labs.value = data
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
  } finally {
    cargandoLabs.value = false
  }
}

function descargarKardex(){
  const url = buildUrl('/api/reportes/kardex.csv', fKardex.value)
  window.location.href = url
}
function descargarPrestamos(){
  const url = buildUrl('/api/reportes/prestamos.csv', fPrestamos.value)
  window.location.href = url
}
function descargarInsumos(){
  const url = '/api/reportes/insumos.csv'
  window.location.href = url
}
function descargarSolicitudes(){
  const url = buildUrl('/api/reportes/solicitudes.csv', fSolicitudes.value)
  window.location.href = url
}
function descargarDevoluciones(){
  const url = buildUrl('/api/reportes/devoluciones.csv', fDevol.value)
  window.location.href = url
}

async function verBajoMinimo(){
  cargandoBM.value = true
  bajoMinimo.value = null
  try{
    const { data } = await axios.get('/api/reportes/insumos-bajo-minimo', { withCredentials:true })
    bajoMinimo.value = data
  }catch(e){
    bajoMinimo.value = { error: e?.response?.data?.message || e.message }
  }finally{
    cargandoBM.value = false
  }
}

async function verIncidencias(){
  cargandoInc.value = true
  incidencias.value = null
  try{
    const { data } = await axios.get('/api/reportes/devoluciones/incidencias', { withCredentials:true })
    incidencias.value = data
  }catch(e){
    incidencias.value = { error: e?.response?.data?.message || e.message }
  }finally{
    cargandoInc.value = false
  }
}

onMounted(loadLabs)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-slate-800">Reportes</h1>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

      <!-- GRID de tarjetas -->
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <!-- KARDEX CSV -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Kardex (CSV)</div>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <select v-model="fKardex.laboratorio_id" class="border rounded p-2">
              <option value="">Laboratorio</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
            <select v-model="fKardex.tipo_item" class="border rounded p-2">
              <option value="">Tipo Item</option>
              <option>INSUMO</option>
              <option>EQUIPO</option>
            </select>
            <input v-model="fKardex.item_id" class="border rounded p-2" placeholder="Item ID (opcional)">
            <select v-model="fKardex.tipo_mov" class="border rounded p-2">
              <option value="">Movimiento</option>
              <option>INGRESO</option>
              <option>EGRESO</option>
              <option>AJUSTE</option>
            </select>
            <input v-model="fKardex.desde" type="date" class="border rounded p-2">
            <input v-model="fKardex.hasta" type="date" class="border rounded p-2">
          </div>
          <button class="rounded bg-indigo-600 text-white px-3 py-2" @click="descargarKardex">Descargar</button>
        </section>

        <!-- PRESTAMOS CSV -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Préstamos (CSV)</div>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <select v-model="fPrestamos.estado" class="border rounded p-2">
              <option value="">Estado</option>
              <option>ABIERTO</option>
              <option>PARCIAL</option>
              <option>CERRADO</option>
            </select>
            <input v-model="fPrestamos.responsable_id" class="border rounded p-2" placeholder="Responsable ID">
            <input v-model="fPrestamos.desde" type="date" class="border rounded p-2">
            <input v-model="fPrestamos.hasta" type="date" class="border rounded p-2">
          </div>
          <button class="rounded bg-indigo-600 text-white px-3 py-2" @click="descargarPrestamos">Descargar</button>
        </section>

        <!-- INSUMOS CSV -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Insumos (CSV)</div>
          <p class="text-sm text-slate-600">Listado general de insumos.</p>
          <button class="rounded bg-indigo-600 text-white px-3 py-2" @click="descargarInsumos">Descargar</button>
        </section>

        <!-- SOLICITUDES CSV -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Solicitudes (CSV)</div>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <select v-model="fSolicitudes.estado" class="border rounded p-2">
              <option value="">Estado</option>
              <option>BORRADOR</option>
              <option>PENDIENTE</option>
              <option>APROBADO</option>
              <option>RECHAZADO</option>
              <option>PREPARADO</option>
              <option>ENTREGADO</option>
              <option>CERRADO</option>
            </select>
            <select v-model="fSolicitudes.laboratorio_id" class="border rounded p-2">
              <option value="">Laboratorio</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
            <input v-model="fSolicitudes.desde" type="date" class="border rounded p-2">
            <input v-model="fSolicitudes.hasta" type="date" class="border rounded p-2">
          </div>
          <button class="rounded bg-indigo-600 text-white px-3 py-2" @click="descargarSolicitudes">Descargar</button>
        </section>

        <!-- DEVOLUCIONES CSV -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Devoluciones (CSV)</div>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <select v-model="fDevol.laboratorio_id" class="border rounded p-2">
              <option value="">Laboratorio</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
            <input v-model="fDevol.equipo_id" class="border rounded p-2" placeholder="Equipo ID">
            <input v-model="fDevol.responsable_id" class="border rounded p-2" placeholder="Responsable ID">
            <select v-model="fDevol.estado_equipo" class="border rounded p-2">
              <option value="">Estado equipo</option>
              <option>OK</option>
              <option>DANADO</option>
              <option>FALTANTE</option>
            </select>
            <input v-model="fDevol.desde" type="date" class="border rounded p-2">
            <input v-model="fDevol.hasta" type="date" class="border rounded p-2">
          </div>
          <button class="rounded bg-indigo-600 text-white px-3 py-2" @click="descargarDevoluciones">Descargar</button>
        </section>

        <!-- JSON rápidos: Bajo mínimo -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Insumos bajo mínimo (JSON)</div>
          <button class="rounded bg-slate-700 text-white px-3 py-2" @click="verBajoMinimo" :disabled="cargandoBM">
            {{ cargandoBM ? 'Cargando…' : 'Ver' }}
          </button>
          <pre v-if="bajoMinimo" class="text-xs bg-slate-50 p-2 rounded overflow-auto max-h-60">{{ bajoMinimo }}</pre>
        </section>

        <!-- JSON rápidos: Incidencias en devoluciones -->
        <section class="rounded-xl bg-white p-4 shadow-sm space-y-3">
          <div class="font-semibold">Incidencias en devoluciones (JSON)</div>
          <button class="rounded bg-slate-700 text-white px-3 py-2" @click="verIncidencias" :disabled="cargandoInc">
            {{ cargandoInc ? 'Cargando…' : 'Ver' }}
          </button>
          <pre v-if="incidencias" class="text-xs bg-slate-50 p-2 rounded overflow-auto max-h-60">{{ incidencias }}</pre>
        </section>
      </div>

      <div v-if="cargandoLabs" class="text-sm text-slate-500">Cargando catálogos…</div>
    </div>
  </AppShell>
</template>
