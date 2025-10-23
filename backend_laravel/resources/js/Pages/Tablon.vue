<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import axios from 'axios'

const loading = ref(false)
const error = ref(null)
const rows = ref([])
const meta = ref(null)
const page = ref(1)
const filters = ref({ q: '', estado: '' })
const perPage = 10

// --- Crear solicitud (lo que ya tenías) ---
const creando = ref(false)
const nuevo = ref({
  grupo_id: null,
  practica_id: null,
  comentario: '',
  items: [{ insumo_id: null, cantidad: 1, unidad: '' }],
})

async function crearSolicitud() {
  try {
    const { data } = await axios.post('/api/solicitudes', nuevo.value)
    alert('Creada #' + data.id)
    fetchTablon()
    creando.value = false
  } catch (e) {
    console.error(e.response?.data || e.message)
    alert('Error al crear')
  }
}

// --- Listado ---
async function fetchTablon() {
  loading.value = true; error.value = null
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const { data } = await axios.get('/api/tablon', {
      params: { ...filters.value, page: page.value, per_page: perPage }
    })
    rows.value = data.data
    meta.value = data.meta
  } catch (e) {
    error.value = e?.response?.data || e.message
  } finally {
    loading.value = false
  }
}

function setPage(p){ page.value = p; fetchTablon() }
function onEstadoFiltro(e){ filters.value.estado = e.target.value; page.value=1; fetchTablon() }
function onQ(e){ filters.value.q = e.target.value; page.value=1; fetchTablon() }

// --- Permisos y cambio de estado en línea ---
const cargandoFila = reactive({})

// lee roles del usuario (Breeze+Inertia suele enviarlos en $page.props.auth.user.roles)
const userRoles = window?.route ? (window?.Ziggy?.props?.auth?.user?.roles ?? []) : ($page?.props?.auth?.user?.roles ?? [])
const roleNames = Array.isArray(userRoles) ? userRoles.map(r => r.name ?? r) : []
const puedeCambiarEstado = computed(() =>
  roleNames.includes('admin') || roleNames.includes('tecnico')
)

async function onEstadoChange(row, nuevoEstado) {
  const anterior = row.estado
  if (!puedeCambiarEstado.value) return
  if (nuevoEstado === anterior) return

  try {
    cargandoFila[row.solicitud_id] = true
    await axios.patch(`/api/solicitudes/${row.solicitud_id}/estado`, {
      estado: nuevoEstado
      // opcional: observacion: '...'
    })
    row.estado = nuevoEstado
    // opcional: await fetchTablon()
  } catch (e) {
    row.estado = anterior
    alert(e?.response?.data?.message || 'No autorizado o error de servidor')
    console.error(e?.response?.data || e)
  } finally {
    cargandoFila[row.solicitud_id] = false
  }
}

onMounted(fetchTablon)
</script>

<template>
  <div class="mb-4 flex items-center gap-2">
    <button class="px-3 py-2 rounded bg-indigo-600 text-white"
            @click="creando = true">
      Nueva Solicitud
    </button>
  </div>

  <!-- Formulario rápido para crear -->
  <div v-if="creando" class="p-4 border rounded mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input v-model.number="nuevo.grupo_id" type="number" placeholder="Grupo ID" class="border rounded p-2">
      <input v-model.number="nuevo.practica_id" type="number" placeholder="Práctica ID" class="border rounded p-2">
      <input v-model="nuevo.comentario" type="text" placeholder="Comentario" class="border rounded p-2">
    </div>
    <div class="mt-3 space-y-2">
      <div v-for="(it,idx) in nuevo.items" :key="idx" class="grid grid-cols-3 gap-2">
        <input v-model.number="it.insumo_id" type="number" placeholder="Insumo ID" class="border rounded p-2">
        <input v-model.number="it.cantidad" type="number" step="0.01" min="0.01" class="border rounded p-2" placeholder="Cantidad">
        <input v-model="it.unidad" type="text" placeholder="Unidad" class="border rounded p-2">
      </div>
      <button class="text-sm underline" @click="nuevo.items.push({insumo_id:null,cantidad:1,unidad:''})">
        + Agregar ítem
      </button>
    </div>
    <div class="mt-3 flex gap-2">
      <button class="px-3 py-2 rounded bg-emerald-600 text-white" @click="crearSolicitud()">Guardar</button>
      <button class="px-3 py-2 rounded bg-gray-200" @click="creando=false">Cancelar</button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Tablón de Pedidos</h1>

    <div class="flex gap-2 mb-4">
      <input class="border p-2" placeholder="Buscar…" @input="onQ" />
      <select class="border p-2" @change="onEstadoFiltro">
        <option value="">Estado</option>
        <option>BORRADOR</option><option>PENDIENTE</option><option>APROBADO</option>
        <option>RECHAZADO</option><option>PREPARADO</option><option>ENTREGADO</option><option>CERRADO</option>
      </select>
    </div>

    <div v-if="error" class="text-red-600 mb-3">{{ error }}</div>
    <div v-if="loading">Cargando…</div>

    <!-- Tabla -->
    <table v-if="!loading && rows.length" class="w-full border text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Curso/Sección</th>
          <th class="p-2 border">Práctica</th>
          <th class="p-2 border">Grupo</th>
          <th class="p-2 border">Lab</th>
          <th class="p-2 border">Estado</th>
          <th class="p-2 border">Actualizado</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in rows" :key="r.solicitud_id">
          <td class="p-2 border">#{{ r.solicitud_id }}</td>
          <td class="p-2 border">{{ r.curso_nombre }} / {{ r.seccion_nombre }}</td>
          <td class="p-2 border">{{ r.practica_titulo }}</td>
          <td class="p-2 border">{{ r.grupo_nombre }}</td>
          <td class="p-2 border">{{ r.laboratorio_nombre }}</td>

          <!-- ESTADO editable si admin/tecnico -->
          <td class="p-2 border">
            <template v-if="puedeCambiarEstado">
              <select
                class="border rounded p-1 text-sm"
                :value="r.estado"
                :disabled="cargandoFila[r.solicitud_id]"
                @change="onEstadoChange(r, $event.target.value)"
              >
                <option value="PENDIENTE">PENDIENTE</option>
                <option value="APROBADO">APROBADO</option>
                <option value="RECHAZADO">RECHAZADO</option>
                <option value="PREPARADO">PREPARADO</option>
                <option value="ENTREGADO">ENTREGADO</option>
                <option value="CERRADO">CERRADO</option>
              </select>
            </template>
            <template v-else>
              <span class="px-2 py-1 rounded bg-gray-100">{{ r.estado }}</span>
            </template>
          </td>

          <td class="p-2 border">{{ new Date(r.actualizado_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else-if="!loading">Sin resultados</p>

    <div v-if="meta" class="mt-3 flex items-center gap-2">
      <button class="border px-3 py-1" :disabled="page<=1" @click="setPage(page-1)">Anterior</button>
      <span>Página {{ page }} de {{ meta.last_page }}</span>
      <button class="border px-3 py-1" :disabled="page>=meta.last_page" @click="setPage(page+1)">Siguiente</button>
    </div>
  </div>
</template>
