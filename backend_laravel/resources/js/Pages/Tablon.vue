<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

// que todas las peticiones incluyan cookies (Sanctum stateful)
axios.defaults.withCredentials = true

// -------------------- estado base --------------------
const loading = ref(false)
const error = ref(null)
const rows = ref([])
const meta = ref(null)
const page = ref(1)
const perPage = 12

const filtros = reactive({
  q: '',
  estado: '',
  laboratorio_id: '',
  curso_id: '',
})

// Lookups
const labs = ref([])
const cursos = ref([])

// -------------------- roles/permiso --------------------
const pageInertia = usePage()
const roles = computed(() => {
  const r = pageInertia.props?.auth?.user?.roles ?? []
  return Array.isArray(r) ? r.map(x => x?.name ?? x) : []
})
const puedeCambiarEstado = computed(() =>
  roles.value.includes('admin') || roles.value.includes('tecnico')
)

// -------------------- lookups --------------------
async function loadLookups () {
  try {
    const [l, c] = await Promise.all([
      axios.get('/api/lookups/laboratorios'),
      axios.get('/api/lookups/cursos'),
    ])
    labs.value = l.data || []
    cursos.value = c.data || []
  } catch (e) {
    console.warn('Lookups error', e?.response?.data || e.message)
  }
}

// -------------------- fetch de tablón --------------------
async function fetchTablon () {
  loading.value = true; error.value = null
  try {
    await axios.get('/sanctum/csrf-cookie')
    const { data } = await axios.get('/api/tablon', {
      params: {
        q: filtros.q || undefined,
        estado: filtros.estado || undefined,
        laboratorio_id: filtros.laboratorio_id || undefined,
        curso_id: filtros.curso_id || undefined,
        page: page.value,
        per_page: perPage,
      }
    })
    rows.value = data?.data ?? data ?? []
    meta.value = data?.meta ?? null
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
    rows.value = []
    meta.value = null
  } finally {
    loading.value = false
  }
}

function resetAndFetch () {
  page.value = 1
  fetchTablon()
}

// -------------------- helpers UI --------------------
function badgeClass (est) {
  const map = {
    BORRADOR:  'bg-slate-200 text-slate-700',
    PENDIENTE: 'bg-amber-100 text-amber-800',
    APROBADO:  'bg-blue-100 text-blue-800',
    RECHAZADO: 'bg-rose-100 text-rose-800',
    PREPARADO: 'bg-indigo-100 text-indigo-800',
    ENTREGADO: 'bg-emerald-100 text-emerald-800',
    CERRADO:   'bg-slate-300 text-slate-700',
  }
  return 'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ' + (map[est] || 'bg-slate-200 text-slate-700')
}

// -------------------- cambio de estado inline --------------------
const cambiando = reactive({})
async function onEstadoChange (row, nuevo) {
  if (!puedeCambiarEstado.value) return
  if (nuevo === row.estado) return
  const prev = row.estado
  try {
    cambiando[row.solicitud_id] = true
    await axios.patch(`/api/solicitudes/${row.solicitud_id}/estado`, { estado: nuevo })
    row.estado = nuevo
  } catch (e) {
    row.estado = prev
    alert(e?.response?.data?.message || 'No se pudo cambiar estado')
  } finally {
    cambiando[row.solicitud_id] = false
  }
}

// -------------------- detalle (drawer simple) --------------------
const showDetalle = ref(false)
const detLoading = ref(false)
const detalle = ref(null)

async function abrirDetalle (row) {
  detalle.value = {
    solicitud_id: row.solicitud_id,
    curso: row.curso_nombre,
    seccion: row.seccion_nombre,
    practica: row.practica_titulo,
    grupo: row.grupo_nombre,
    laboratorio: row.laboratorio_nombre,
    estado: row.estado,
    actualizado_at: row.actualizado_at,
    items: [],
  }
  showDetalle.value = true
  detLoading.value = true
  try {
    const { data } = await axios.get(`/api/solicitudes/${row.solicitud_id}`)
    if (data?.items) detalle.value.items = data.items
  } catch (e) {
    console.warn('Detalle minimal:', e?.response?.data || e.message)
  } finally {
    detLoading.value = false
  }
}

function cerrarDetalle () {
  showDetalle.value = false
  detalle.value = null
}

// -------------------- lifecycle --------------------
onMounted(async () => {
  await loadLookups()
  await fetchTablon()
})
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Tablón de Pedidos</h1>
        <button
          class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700"
          @click="fetchTablon"
          :disabled="loading">
          Refrescar
        </button>
      </div>

      <!-- filtros -->
      <div class="rounded-xl border bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-5">
          <div class="md:col-span-2">
            <label class="text-xs text-slate-500">Buscar</label>
            <input
              class="mt-1 w-full rounded-lg border px-3 py-2"
              placeholder="práctica, grupo, curso, sección…"
              v-model.trim="filtros.q"
              @keyup.enter="resetAndFetch" />
          </div>

          <div>
            <label class="text-xs text-slate-500">Estado</label>
            <select class="mt-1 w-full rounded-lg border px-3 py-2" v-model="filtros.estado" @change="resetAndFetch">
              <option value="">(todos)</option>
              <option>BORRADOR</option>
              <option>PENDIENTE</option>
              <option>APROBADO</option>
              <option>RECHAZADO</option>
              <option>PREPARADO</option>
              <option>ENTREGADO</option>
              <option>CERRADO</option>
            </select>
          </div>

          <div>
            <label class="text-xs text-slate-500">Laboratorio</label>
            <select class="mt-1 w-full rounded-lg border px-3 py-2" v-model="filtros.laboratorio_id" @change="resetAndFetch">
              <option value="">(todos)</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
          </div>

          <div>
            <label class="text-xs text-slate-500">Curso</label>
            <select class="mt-1 w-full rounded-lg border px-3 py-2" v-model="filtros.curso_id" @change="resetAndFetch">
              <option value="">(todos)</option>
              <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- errores / loading -->
      <div v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-rose-700">
        {{ error }}
      </div>
      <div v-if="loading" class="rounded-lg border bg-white p-4 text-slate-500 shadow-sm">
        Cargando…
      </div>

      <!-- tabla -->
      <div v-if="!loading" class="overflow-x-auto rounded-xl border bg-white shadow-sm">
        <table class="w-full text-left text-sm">
          <thead class="sticky top-0 bg-slate-50 text-slate-600">
            <tr>
              <th class="px-3 py-2 border-b">#</th>
              <th class="px-3 py-2 border-b">Curso / Sección</th>
              <th class="px-3 py-2 border-b">Práctica</th>
              <th class="px-3 py-2 border-b">Grupo</th>
              <th class="px-3 py-2 border-b">Laboratorio</th>
              <th class="px-3 py-2 border-b">Estado</th>
              <th class="px-3 py-2 border-b">Actualizado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.solicitud_id"
                class="hover:bg-slate-50 cursor-pointer"
                @click="abrirDetalle(r)">
              <td class="px-3 py-2 border-b">#{{ r.solicitud_id }}</td>
              <td class="px-3 py-2 border-b">
                <div class="font-medium text-slate-800">{{ r.curso_nombre }}</div>
                <div class="text-xs text-slate-500">Sección {{ r.seccion_nombre }}</div>
              </td>
              <td class="px-3 py-2 border-b">{{ r.practica_titulo }}</td>
              <td class="px-3 py-2 border-b">{{ r.grupo_nombre }}</td>
              <td class="px-3 py-2 border-b">{{ r.laboratorio_nombre }}</td>
              <td class="px-3 py-2 border-b">
                <template v-if="puedeCambiarEstado">
                  <select
                    class="rounded border px-2 py-1 text-sm"
                    :value="r.estado"
                    :disabled="cambiando[r.solicitud_id]"
                    @click.stop
                    @change="onEstadoChange(r, $event.target.value)">
                    <option value="PENDIENTE">PENDIENTE</option>
                    <option value="APROBADO">APROBADO</option>
                    <option value="RECHAZADO">RECHAZADO</option>
                    <option value="PREPARADO">PREPARADO</option>
                    <option value="ENTREGADO">ENTREGADO</option>
                    <option value="CERRADO">CERRADO</option>
                  </select>
                </template>
                <template v-else>
                  <span :class="badgeClass(r.estado)">{{ r.estado }}</span>
                </template>
              </td>
              <td class="px-3 py-2 border-b">
                {{ new Date(r.actualizado_at).toLocaleString() }}
              </td>
            </tr>

            <tr v-if="rows.length === 0">
              <td colspan="7" class="px-3 py-10 text-center text-slate-500">
                Sin resultados con los filtros actuales.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- paginación -->
      <div v-if="meta" class="flex items-center gap-2 text-sm">
        <button class="rounded border px-3 py-1 disabled:opacity-50"
                :disabled="page<=1"
                @click="page--; fetchTablon()">Anterior</button>
        <span>Página {{ meta.current_page }} de {{ meta.last_page }}</span>
        <button class="rounded border px-3 py-1 disabled:opacity-50"
                :disabled="page>=meta.last_page"
                @click="page++; fetchTablon()">Siguiente</button>
      </div>
    </div>

    <!-- Drawer de detalle -->
    <div v-if="showDetalle"
         class="fixed inset-0 z-50 flex items-stretch justify-end bg-black/30"
         @click.self="cerrarDetalle">
      <div class="h-full w-full max-w-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b p-4">
          <div class="text-lg font-semibold text-slate-800">
            Solicitud #{{ detalle?.solicitud_id }}
          </div>
          <button class="rounded bg-slate-100 px-3 py-1" @click="cerrarDetalle">Cerrar</button>
        </div>

        <div class="p-4 space-y-2 text-sm">
          <div><span class="text-slate-500">Curso:</span> {{ detalle?.curso }}</div>
          <div><span class="text-slate-500">Sección:</span> {{ detalle?.seccion }}</div>
          <div><span class="text-slate-500">Práctica:</span> {{ detalle?.practica }}</div>
          <div><span class="text-slate-500">Grupo:</span> {{ detalle?.grupo }}</div>
          <div><span class="text-slate-500">Laboratorio:</span> {{ detalle?.laboratorio }}</div>
          <div><span class="text-slate-500">Estado:</span>
            <span :class="badgeClass(detalle?.estado)">{{ detalle?.estado }}</span>
          </div>
          <div><span class="text-slate-500">Actualizado:</span> {{ new Date(detalle?.actualizado_at).toLocaleString() }}</div>
        </div>

        <div class="border-t p-4">
          <div class="font-medium text-slate-700 mb-2">Ítems</div>
          <div v-if="detLoading" class="text-slate-500">Cargando ítems…</div>
          <div v-else-if="!detalle?.items?.length" class="text-slate-500">Sin ítems disponibles.</div>
          <ul v-else class="space-y-2 text-sm">
            <li v-for="(it, idx) in detalle.items" :key="idx" class="rounded border p-2">
              <div class="font-medium">{{ it?.nombre ?? ('Item ' + (it?.item_id ?? '')) }}</div>
              <div class="text-slate-500">
                {{ Number(it?.cantidad ?? 0) }} {{ it?.unidad ?? '' }}
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppShell>
</template>
