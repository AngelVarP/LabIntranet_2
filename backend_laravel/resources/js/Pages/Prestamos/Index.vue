<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

async function ensureCsrf(){
  try { await axios.get('/sanctum/csrf-cookie', { withCredentials:true }) } catch {}
}

/* ================== CREAR PRÉSTAMO ================== */
const crear = reactive({
  solicitud_id: null,
  fecha_compromiso: '' // opcional (YYYY-MM-DDTHH:mm)
})
const creando = ref(false)

async function crearPrestamo(){
  creando.value = true
  await ensureCsrf()
  try{
    const payload = {
      solicitud_id: crear.solicitud_id,
      // si viene vacío, no lo enviamos
      ...(crear.fecha_compromiso ? { fecha_compromiso: crear.fecha_compromiso } : {})
    }
    const { data } = await axios.post('/api/prestamos', payload, { withCredentials:true })
    // asumimos que devuelve { id: ... }
    if(data?.id){
      prestamoId.value = data.id
      await cargarPrestamo()
      alert('Préstamo creado #' + data.id)
    }else{
      alert('Creado. Carga el préstamo con su ID.')
    }
  }catch(e){
    console.error(e?.response?.data || e)
    alert(e?.response?.data?.message || 'Error al crear préstamo')
  }finally{
    creando.value = false
  }
}

/* ================== VER / OPERAR PRÉSTAMO ================== */
const prestamoId = ref('')
const loading = ref(false)
const error = ref(null)
const prestamo = ref(null) // detalle

async function cargarPrestamo(){
  if(!prestamoId.value) return
  loading.value = true; error.value = null
  try{
    const { data } = await axios.get(`/api/prestamos/${prestamoId.value}`, { withCredentials:true })
    prestamo.value = data
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    prestamo.value = null
  }finally{
    loading.value = false
  }
}

/* ================== AGREGAR EQUIPO ================== */
const buscarEq = ref('')
const resultadosEq = ref([])
const agregando = ref(false)

async function buscarEquipos(){
  if(!buscarEq.value){ resultadosEq.value = []; return }
  try{
    const { data } = await axios.get('/api/lookups/equipos/buscar', { params:{ q: buscarEq.value } })
    resultadosEq.value = data?.data ?? data ?? []
  }catch{ resultadosEq.value = [] }
}

async function agregarEquipo(equipo_id){
  if(!prestamo.value?.id) return alert('Carga un préstamo primero.')
  agregando.value = true
  await ensureCsrf()
  try{
    await axios.post(`/api/prestamos/${prestamo.value.id}/agregar`, { equipo_id }, { withCredentials:true })
    await cargarPrestamo()
  }catch(e){
    console.error(e?.response?.data || e)
    alert(e?.response?.data?.message || 'No se pudo agregar equipo')
  }finally{
    agregando.value = false
  }
}

/* ================== DEVOLVER EQUIPO(S) ================== */
const devolucion = reactive({
  equipo_id: null,
  estado_equipo: 'OK', // OK | DANADO | FALTANTE
  observacion: ''
})
const devolviendo = ref(false)

async function registrarDevolucion(){
  if(!prestamo.value?.id) return alert('Carga un préstamo primero.')
  if(!devolucion.equipo_id) return alert('Selecciona un equipo del préstamo.')

  await ensureCsrf()
  devolviendo.value = true
  try{
    await axios.post(`/api/devoluciones/${prestamo.value.id}`, {
      items: [{
        equipo_id: devolucion.equipo_id,
        estado_equipo: devolucion.estado_equipo,
        observacion: devolucion.observacion || null
      }]
    }, { withCredentials:true })

    // limpiar e ir a refrescar
    devolucion.equipo_id = null
    devolucion.estado_equipo = 'OK'
    devolucion.observacion = ''
    await cargarPrestamo()
    alert('Devolución registrada')
  }catch(e){
    console.error(e?.response?.data || e)
    alert(e?.response?.data?.message || 'Error al registrar devolución')
  }finally{
    devolviendo.value = false
  }
}

/* ================== CERRAR PRÉSTAMO ================== */
const cerrando = ref(false)
async function cerrarPrestamo(){
  if(!prestamo.value?.id) return
  if(!confirm('¿Cerrar el préstamo?')) return
  await ensureCsrf()
  cerrando.value = true
  try{
    await axios.post(`/api/prestamos/${prestamo.value.id}/cerrar`, {}, { withCredentials:true })
    await cargarPrestamo()
    alert('Préstamo cerrado')
  }catch(e){
    console.error(e?.response?.data || e)
    alert(e?.response?.data?.message || 'No se pudo cerrar')
  }finally{
    cerrando.value = false
  }
}

onMounted(async ()=>{ await ensureCsrf() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Préstamos & Devoluciones</h1>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- ================== CREAR PRÉSTAMO ================== -->
        <section class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Crear préstamo</h2>
          <div class="space-y-3">
            <div>
              <label class="block text-xs text-slate-500 mb-1">ID de Solicitud</label>
              <input v-model.number="crear.solicitud_id" type="number" class="w-full rounded border p-2" placeholder="(ej. 123)" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-1">Fecha compromiso (opcional)</label>
              <input v-model="crear.fecha_compromiso" type="datetime-local" class="w-full rounded border p-2" />
            </div>
            <button class="rounded bg-emerald-600 px-3 py-2 text-white" :disabled="creando" @click="crearPrestamo">
              {{ creando ? 'Creando…' : 'Crear' }}
            </button>
          </div>
        </section>

        <!-- ================== GESTIONAR PRÉSTAMO ================== -->
        <section class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Gestionar préstamo</h2>

          <!-- Cargar -->
          <div class="mb-3 flex items-end gap-2">
            <div class="flex-1">
              <label class="block text-xs text-slate-500 mb-1">ID de Préstamo</label>
              <input v-model.trim="prestamoId" class="w-full rounded border p-2" placeholder="(ej. 45)" />
            </div>
            <button class="rounded border px-3 py-2" @click="cargarPrestamo">Cargar</button>
          </div>

          <div v-if="error" class="mb-3 rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
          <div v-if="loading">Cargando…</div>

          <div v-if="prestamo && !loading" class="space-y-4">
            <div class="rounded border p-3">
              <div class="grid md:grid-cols-2 gap-2 text-sm">
                <div><span class="text-slate-500">ID:</span> #{{ prestamo.id }}</div>
                <div><span class="text-slate-500">Estado:</span> {{ prestamo.estado }}</div>
                <div><span class="text-slate-500">Solicitud:</span> #{{ prestamo.solicitud_id }}</div>
                <div><span class="text-slate-500">Responsable:</span> {{ prestamo.responsable?.name ?? prestamo.responsable_id }}</div>
                <div><span class="text-slate-500">Compromiso:</span> {{ prestamo.fecha_compromiso ?? '—' }}</div>
              </div>
            </div>

            <!-- Equipos del préstamo -->
            <div class="rounded border p-3">
              <h3 class="mb-2 font-medium">Equipos en préstamo</h3>
              <table class="w-full text-sm">
                <thead><tr class="border-b bg-slate-50">
                  <th class="p-2 text-left">Equipo</th>
                  <th class="p-2 text-left">Código</th>
                  <th class="p-2 text-left">Obs.</th>
                </tr></thead>
                <tbody>
                  <tr v-for="it in (prestamo.items || prestamo.detalle || [])" :key="it.equipo_id" class="border-b">
                    <td class="p-2">{{ it.equipo_nombre ?? it.nombre ?? it.equipo_id }}</td>
                    <td class="p-2">{{ it.equipo_codigo ?? it.codigo ?? '—' }}</td>
                    <td class="p-2">{{ it.observacion ?? '—' }}</td>
                  </tr>
                  <tr v-if="!(prestamo.items || prestamo.detalle || []).length">
                    <td colspan="3" class="p-2 text-slate-500">Sin equipos</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Agregar equipo -->
            <div class="rounded border p-3">
              <h3 class="mb-2 font-medium">Agregar equipo</h3>
              <div class="flex gap-2">
                <input v-model="buscarEq" @input="buscarEquipos" class="flex-1 rounded border p-2" placeholder="Buscar por nombre/código…" />
              </div>
              <div class="mt-2 grid gap-1">
                <button
                  v-for="r in resultadosEq"
                  :key="r.id"
                  class="flex items-center justify-between rounded border px-3 py-2 text-left hover:bg-slate-50"
                  :disabled="agregando"
                  @click="agregarEquipo(r.id)"
                >
                  <span>
                    <span class="font-medium">{{ r.nombre }}</span>
                    <span class="text-slate-500"> · {{ r.codigo }}</span>
                  </span>
                  <span class="text-xs text-slate-500">ID {{ r.id }}</span>
                </button>
                <div v-if="!resultadosEq.length" class="text-sm text-slate-500">Sin resultados</div>
              </div>
            </div>

            <!-- Registrar devolución -->
            <div class="rounded border p-3">
              <h3 class="mb-2 font-medium">Registrar devolución</h3>
              <div class="grid md:grid-cols-3 gap-2">
                <select v-model="devolucion.equipo_id" class="rounded border p-2">
                  <option :value="null">Equipo del préstamo…</option>
                  <option
                    v-for="it in (prestamo.items || prestamo.detalle || [])"
                    :key="it.equipo_id"
                    :value="it.equipo_id"
                  >
                    {{ it.equipo_codigo ?? it.codigo ?? '#'+it.equipo_id }} · {{ it.equipo_nombre ?? it.nombre ?? '' }}
                  </option>
                </select>
                <select v-model="devolucion.estado_equipo" class="rounded border p-2">
                  <option value="OK">OK</option>
                  <option value="DANADO">Dañado</option>
                  <option value="FALTANTE">Faltante</option>
                </select>
                <input v-model.trim="devolucion.observacion" class="rounded border p-2" placeholder="Observación (opcional)" />
              </div>
              <div class="mt-2">
                <button class="rounded bg-indigo-600 px-3 py-2 text-white" :disabled="devolviendo" @click="registrarDevolucion">
                  {{ devolviendo ? 'Guardando…' : 'Guardar devolución' }}
                </button>
              </div>
            </div>

            <!-- Cerrar préstamo -->
            <div class="flex justify-end">
              <button class="rounded bg-slate-800 px-3 py-2 text-white" :disabled="cerrando" @click="cerrarPrestamo">
                {{ cerrando ? 'Cerrando…' : 'Cerrar préstamo' }}
              </button>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AppShell>
</template>
