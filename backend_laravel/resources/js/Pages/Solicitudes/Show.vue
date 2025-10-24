<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import BadgeEstado from '../../Components/ui/BadgeEstado.vue'
import Modal from '../../Components/ui/Modal.vue'
import { showToast } from '../../Components/ui/useToast.js'

axios.defaults.withCredentials = true
async function ensureCsrf(){ try{ await axios.get('/sanctum/csrf-cookie') }catch{} }

const page  = usePage()
const roles = computed(() => page.props?.auth?.user?.roles ?? [])
const puedeAccionar = computed(() => roles.value.includes('admin') || roles.value.includes('tecnico'))

// Tomamos el id desde la URL /solicitudes/{id}
const id = ref(Number((window.location.pathname.split('/').pop() || '').replace(/\D/g,'')) || null)

const loading = ref(false)
const error   = ref(null)
const s       = ref(null)   // solicitud
const hist    = ref([])     // historial/linea de tiempo
const showJSON = ref(false) // modal debug

async function load(){
  if(!id.value) { error.value = 'ID inválido'; return }
  loading.value = true; error.value = null
  try{
    const { data } = await axios.get(`/api/solicitudes/${id.value}`)
    s.value = data
    // Soporta diferentes nombres de propiedad para el historial
    hist.value = data?.historial ?? data?.timeline ?? data?.audits ?? []
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    s.value = null; hist.value = []
  }finally{ loading.value=false }
}

async function runAccion(accion){
  if(!s.value?.id) return
  await ensureCsrf()
  const tries = [
    { url: `/api/solicitudes/${s.value.id}/${accion}`, body:{} },
    { url: `/api/solicitudes/${s.value.id}/estado`, body:{ estado: accion.toUpperCase() } },
    { url: `/api/solicitudes/${s.value.id}/cambiar-estado`, body:{ estado: accion.toUpperCase() } },
  ]
  for(const t of tries){
    try{
      await axios.post(t.url, t.body)
      showToast(`Solicitud #${s.value.id}: ${accion} ✔️`)
      await load()
      return
    }catch{/* prueba siguiente */}
  }
  showToast('No se pudo ejecutar la acción','error')
}

onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-slate-800">Solicitud #{{ s?.id ?? id }}</h1>
          <div class="mt-1 text-slate-600 text-sm">Detalle y seguimiento</div>
        </div>
        <div class="flex gap-2">
          <Link href="/tablon" class="rounded border px-3 py-2 hover:bg-slate-50">Volver</Link>
          <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="showJSON=true">Ver JSON</button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <template v-else-if="s">
        <!-- Header -->
        <div class="grid gap-4 md:grid-cols-3">
          <div class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="text-xs text-slate-500">Estado</div>
            <div class="mt-1"><BadgeEstado :estado="s.estado" /></div>
          </div>
          <div class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="text-xs text-slate-500">Curso / Sección</div>
            <div class="mt-1 font-medium">
              {{ s.curso_codigo ?? s.curso?.codigo }} {{ s.curso_nombre ?? s.curso?.nombre }}
            </div>
            <div class="text-xs text-slate-500">{{ s.seccion ?? s.seccion_nombre }}</div>
          </div>
          <div class="rounded-2xl bg-white p-4 shadow-sm">
            <div class="text-xs text-slate-500">Laboratorio</div>
            <div class="mt-1 font-medium">{{ s.laboratorio_nombre ?? s.laboratorio?.nombre ?? '—' }}</div>
          </div>
        </div>

        <!-- Items -->
        <div class="rounded-2xl bg-white p-4 shadow-sm">
          <div class="mb-2 font-medium">Ítems solicitados</div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-slate-500">
                  <th class="px-3 py-2">Tipo</th>
                  <th class="px-3 py-2">Código</th>
                  <th class="px-3 py-2">Nombre</th>
                  <th class="px-3 py-2">Cant.</th>
                  <th class="px-3 py-2">Unidad</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(it,idx) in (s.items ?? s.detalles ?? [])" :key="it.id ?? idx" class="border-t">
                  <td class="px-3 py-2">{{ it.tipo_item ?? it.tipo ?? '—' }}</td>
                  <td class="px-3 py-2">{{ it.codigo ?? it.item_codigo ?? '—' }}</td>
                  <td class="px-3 py-2">{{ it.nombre ?? it.item_nombre ?? '—' }}</td>
                  <td class="px-3 py-2">{{ it.cantidad_solic ?? it.cantidad ?? 0 }}</td>
                  <td class="px-3 py-2">{{ it.unidad ?? '—' }}</td>
                </tr>
                <tr v-if="!(s.items?.length || s.detalles?.length)">
                  <td colspan="5" class="px-3 py-6 text-center text-slate-500">Sin ítems</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Timeline -->
        <div class="rounded-2xl bg-white p-4 shadow-sm">
          <div class="mb-2 font-medium">Historial</div>
          <div v-if="!hist.length" class="text-sm text-slate-500">No hay eventos de historial.</div>
          <ol v-else class="relative border-s border-slate-200 pl-4">
            <li v-for="(h, i) in hist" :key="i" class="mb-3">
              <div class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full bg-slate-300"></div>
              <div class="text-sm">
                <span class="font-medium">{{ h.accion ?? h.action ?? h.estado ?? 'evento' }}</span>
                <span class="text-slate-500" v-if="h.usuario || h.user"> · {{ h.usuario ?? h.user }}</span>
              </div>
              <div class="text-xs text-slate-500">{{ h.fecha ?? h.created_at ?? h.timestamp }}</div>
              <div class="text-xs text-slate-600" v-if="h.obs || h.observacion || h.note">{{ h.obs ?? h.observacion ?? h.note }}</div>
            </li>
          </ol>
        </div>

        <!-- Acciones -->
        <div v-if="puedeAccionar" class="flex flex-wrap gap-2">
          <button class="rounded bg-blue-600 px-3 py-2 text-white" @click="runAccion('aprobar')">Aprobar</button>
          <button class="rounded bg-rose-600 px-3 py-2 text-white" @click="runAccion('rechazar')">Rechazar</button>
          <button class="rounded bg-indigo-600 px-3 py-2 text-white" @click="runAccion('preparar')">Preparar</button>
          <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="runAccion('entregar')">Entregar</button>
          <button class="rounded bg-slate-800 px-3 py-2 text-white" @click="runAccion('cerrar')">Cerrar</button>
        </div>
      </template>
    </div>

    <!-- Modal JSON debug -->
    <Modal :show="showJSON" title="Payload de la solicitud" @close="showJSON=false">
      <pre class="text-xs whitespace-pre-wrap text-slate-700">{{ JSON.stringify(s, null, 2) }}</pre>
    </Modal>
  </AppShell>
</template>
