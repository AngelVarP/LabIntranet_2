<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import Autocomplete from '../../Components/ui/Autocomplete.vue'
import { API, ensureCsrf } from '../../api/api.js'
import { showToast } from '../../Components/ui/useToast.js'

const id = ref(Number((window.location.pathname.split('/').pop() || '').replace(/\D/g,'')) || null)

const prestamo = ref(null)
const loading = ref(false)
const error = ref(null)

async function load(){
  if(!id.value){ error.value = 'ID inválido'; return }
  loading.value = true; error.value=null
  try{
    const { data } = await axios.get(`${API.prestamos}/${id.value}`)
    prestamo.value = data
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    prestamo.value = null
  }finally{ loading.value=false }
}

async function agregarEquipoByObj(obj){
  if(!prestamo.value?.id) return
  await ensureCsrf()
  try{
    await axios.post(`${API.prestamos}/${prestamo.value.id}/agregar`, { equipo_id: obj.id })
    showToast('Equipo agregado'); await load()
  }catch(e){ showToast('No se pudo agregar','error') }
}

const devolucion = ref({ equipo_id:null, observacion:'' })
async function registrarDevolucion(){
  if(!prestamo.value?.id || !devolucion.value.equipo_id) return
  await ensureCsrf()
  try{
    await axios.post(`${API.devoluciones}/${prestamo.value.id}`, {
      equipo_id: devolucion.value.equipo_id,
      observacion: devolucion.value.observacion
    })
    showToast('Devolución registrada')
    devolucion.value = { equipo_id:null, observacion:'' }
    await load()
  }catch(e){ showToast('No se pudo registrar','error') }
}

async function cerrar(){
  if(!prestamo.value?.id) return
  await ensureCsrf()
  try{ await axios.post(`${API.prestamos}/${prestamo.value.id}/cerrar`); showToast('Préstamo cerrado'); await load() }
  catch(e){ showToast('No se pudo cerrar','error') }
}

onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Préstamo #{{ prestamo?.id ?? id }}</h1>
        <div class="flex gap-2">
          <button class="rounded bg-slate-800 px-3 py-2 text-white" @click="cerrar">Cerrar préstamo</button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <template v-else-if="prestamo">
        <div class="rounded-2xl bg-white p-4 shadow-sm">
          <div class="text-sm text-slate-600">Solicitud #{{ prestamo.solicitud_id }}</div>
          <div class="text-slate-800">Estado: {{ prestamo.estado ?? '-' }}</div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <section class="rounded-2xl bg-white p-4 shadow-sm">
            <h3 class="mb-2 font-medium">Agregar equipo</h3>
            <Autocomplete
              fetch-url="/api/lookups/equipos/buscar"
              placeholder="Escribe nombre o código…"
              @select="agregarEquipoByObj"
            />
          </section>

          <section class="rounded-2xl bg-white p-4 shadow-sm">
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
              <button class="rounded bg-indigo-600 px-3 py-2 text-white" @click="registrarDevolucion">Registrar</button>
            </div>
          </section>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm">
          <div class="mb-2 font-medium">Items</div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead><tr class="text-left text-slate-500">
                <th class="px-3 py-2">#</th><th class="px-3 py-2">Código</th><th class="px-3 py-2">Nombre</th>
                <th class="px-3 py-2">Estado</th>
              </tr></thead>
              <tbody>
                <tr v-for="(it,idx) in (prestamo.items||[])" :key="it.id ?? idx" class="border-t">
                  <td class="px-3 py-2">{{ idx+1 }}</td>
                  <td class="px-3 py-2">{{ it.codigo }}</td>
                  <td class="px-3 py-2">{{ it.nombre }}</td>
                  <td class="px-3 py-2">{{ it.estado ?? '-' }}</td>
                </tr>
                <tr v-if="!(prestamo.items||[]).length"><td colspan="4" class="px-3 py-6 text-center text-slate-500">Sin items</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </AppShell>
</template>
