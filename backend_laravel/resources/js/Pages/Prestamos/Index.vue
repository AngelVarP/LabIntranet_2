<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import Autocomplete from '../../Components/ui/Autocomplete.vue'
import { showToast } from '../../Components/ui/useToast.js'
import { API, ensureCsrf } from '../../api/api.js'

/* ================== CREAR PRÉSTAMO ================== */
const crear = reactive({ solicitud_id: null, fecha_compromiso: '' })
const creando = ref(false)

async function crearPrestamo(){
  if(!crear.solicitud_id) return showToast('Ingresa el ID de solicitud','error')
  creando.value = true
  await ensureCsrf()
  try{
    const { data } = await axios.post(API.prestamos, crear)
    showToast('Préstamo creado')
    prestamo.value = data
  }catch(e){ showToast(e?.response?.data?.message || 'Error al crear','error') }
  finally{ creando.value=false }
}

/* ================== CARGAR / GESTIONAR ================== */
const buscarId = ref('')
const prestamo = ref(null)
const loading  = ref(false)
const error    = ref(null)

async function cargarPrestamo(){
  if(!buscarId.value) return
  loading.value=true; error.value=null
  try{
    const { data } = await axios.get(`${API.prestamos}/${buscarId.value}`)
    prestamo.value = data
  }catch(e){ error.value = e?.response?.data?.message || e.message; prestamo.value=null }
  finally{ loading.value=false }
}

/* ================== AGREGAR EQUIPO ================== */
const agregando = ref(false)
async function agregarEquipoByObj(obj){
  if(!prestamo.value?.id) return showToast('Carga un préstamo primero','error')
  if(!obj?.id) return
  agregando.value = true
  await ensureCsrf()
  try{
    await axios.post(`${API.prestamos}/${prestamo.value.id}/agregar`, { equipo_id: obj.id })
    await cargarPrestamo(); showToast('Equipo agregado')
  }catch(e){ showToast(e?.response?.data?.message || 'No se pudo agregar','error') }
  finally{ agregando.value=false }
}

/* ================== DEVOLUCIÓN ================== */
const devolucion = reactive({ equipo_id:null, observacion:'' })
const devolviendo = ref(false)

async function registrarDevolucion(){
  if(!prestamo.value?.id || !devolucion.equipo_id) return showToast('Selecciona un equipo','error')
  devolviendo.value=true
  await ensureCsrf()
  try{
    // TU API REAL:
    await axios.post(`${API.devoluciones}/${prestamo.value.id}`, {
      equipo_id: devolucion.equipo_id,
      observacion: devolucion.observacion
    })
    await cargarPrestamo(); showToast('Devolución registrada')
    Object.assign(devolucion, { equipo_id:null, observacion:'' })
  }catch(e){ showToast(e?.response?.data?.message || 'No se pudo registrar','error') }
  finally{ devolviendo.value=false }
}

/* ================== CERRAR ================== */
const cerrando = ref(false)
async function cerrarPrestamo(){
  if(!prestamo.value?.id) return
  cerrando.value=true
  await ensureCsrf()
  try{ await axios.post(`${API.prestamos}/${prestamo.value.id}/cerrar`); showToast('Préstamo cerrado'); await cargarPrestamo() }
  catch(e){ showToast(e?.response?.data?.message || 'No se pudo cerrar','error') }
  finally{ cerrando.value=false }
}
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Préstamos & Devoluciones</h1>
      </div>

      <div class="grid gap-6 md:grid-cols-2">
        <!-- CREAR -->
        <section class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Crear préstamo</h2>
          <div class="space-y-3">
            <label class="block text-sm">ID de Solicitud
              <input v-model.number="crear.solicitud_id" type="number" class="mt-1 w-full rounded border p-2" placeholder="(ej. 123)" />
            </label>
            <label class="block text-sm">Fecha compromiso (opcional)
              <input v-model="crear.fecha_compromiso" type="datetime-local" class="mt-1 w-full rounded border p-2" />
            </label>
            <button class="rounded bg-emerald-600 px-3 py-2 text-white" :disabled="creando" @click="crearPrestamo">
              {{ creando ? 'Creando…' : 'Crear' }}
            </button>
          </div>
        </section>

        <!-- GESTIONAR -->
        <section class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Gestionar préstamo</h2>

          <div class="mb-3 flex gap-2">
            <input v-model.trim="buscarId" class="w-40 rounded border p-2" placeholder="ID préstamo" />
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="cargarPrestamo">Cargar</button>
          </div>

          <div v-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>
          <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

          <div v-if="prestamo" class="space-y-4">
            <div class="rounded border p-3">
              <div class="text-sm text-slate-600">Préstamo #{{ prestamo.id }}</div>
              <div class="text-slate-800">Solicitud #{{ prestamo.solicitud_id }}</div>
            </div>

            <div class="rounded border p-3">
              <h3 class="mb-2 font-medium">Agregar equipo</h3>
              <Autocomplete
                fetch-url="/api/lookups/equipos/buscar"
                placeholder="Escribe nombre o código…"
                :disabled="agregando"
                @select="agregarEquipoByObj"
              />
            </div>

            <div class="rounded border p-3">
              <h3 class="mb-2 font-medium">Registrar devolución</h3>
              <div class="grid md:grid-cols-3 gap-2">
                <select v-model="devolucion.equipo_id" class="rounded border p-2">
                  <option :value="null">Equipo del préstamo…</option>
                  <option v-for="it in (prestamo.items||[])" :key="it.id" :value="it.id">
                    {{ it.nombre }} · {{ it.codigo }}
                  </option>
                </select>
                <input v-model.trim="devolucion.observacion" class="rounded border p-2 md:col-span-2" placeholder="Observación (opcional)" />
              </div>
              <div class="mt-2 flex justify-end">
                <button class="rounded bg-indigo-600 px-3 py-2 text-white" :disabled="devolviendo" @click="registrarDevolucion">
                  {{ devolviendo ? 'Registrando…' : 'Registrar' }}
                </button>
              </div>
            </div>

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
