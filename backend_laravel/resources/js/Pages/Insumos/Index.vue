<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

// --------- estado ----------
const loading = ref(false)
const error   = ref(null)
const rows    = ref([])
const meta    = ref(null)
const page    = ref(1)
const perPage = 12

const filtros = reactive({
  q: '',
  categoria_id: '',
  solo_activos: '',
})

const categorias = ref([])

// --------- modal crear/editar ----------
const showForm = ref(false)
const formMode = ref('crear') // 'crear' | 'editar'
const saving   = ref(false)
const formErr  = ref({})
const form = reactive({
  id: null,
  nombre: '',
  codigo: '',
  unidad: '',
  stock: 0,
  minimo: 0,
  categoria_id: null,
  activo: 1,
})

function openCrear() {
  formMode.value = 'crear'
  Object.assign(form, {
    id: null, nombre:'', codigo:'', unidad:'',
    stock: 0, minimo: 0, categoria_id: null, activo: 1
  })
  formErr.value = {}
  showForm.value = true
}

function openEditar(r) {
  formMode.value = 'editar'
  Object.assign(form, {
    id: r.id,
    nombre: r.nombre ?? '',
    codigo: r.codigo ?? '',
    unidad: r.unidad ?? '',
    stock: Number(r.stock ?? 0),
    minimo: Number(r.minimo ?? 0),
    categoria_id: r.categoria_id ?? null,
    activo: r.activo ?? 1,
  })
  formErr.value = {}
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

// --------- carga lookups ----------
async function loadCategorias() {
  try {
    const { data } = await axios.get('/api/lookups/categorias-insumo', { withCredentials: true })
    categorias.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.warn('lookup categorias', e?.response?.data || e.message)
    categorias.value = []
  }
}

// --------- fetch listado ----------
async function fetchInsumos() {
  loading.value = true; error.value = null
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const { data } = await axios.get('/api/insumos', {
      withCredentials: true,
      params: {
        q: filtros.q || undefined,
        categoria_id: filtros.categoria_id || undefined,
        per_page: perPage,
        page: page.value,
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

function resetAndFetch() { page.value = 1; fetchInsumos() }

// --------- persistencia ----------
async function saveForm() {
  saving.value = true
  formErr.value = {}
  try {
    const payload = {
      nombre: form.nombre,
      codigo: form.codigo || null,
      unidad: form.unidad || null,
      stock:  form.stock ?? 0,
      minimo: form.minimo ?? 0,
      categoria_id: form.categoria_id || null,
      activo: !!form.activo,
    }

    if (formMode.value === 'crear') {
      await axios.post('/api/insumos', payload, { withCredentials: true })
    } else {
      await axios.put(`/api/insumos/${form.id}`, payload, { withCredentials: true })
    }
    closeForm()
    fetchInsumos()
  } catch (e) {
    if (e?.response?.status === 422 && e.response.data?.errors) {
      formErr.value = e.response.data.errors
    } else {
      alert(e?.response?.data?.message || 'Error al guardar')
    }
  } finally {
    saving.value = false
  }
}

async function deleteRow(r) {
  if (!confirm(`¿Eliminar "${r.nombre}"?`)) return
  try {
    await axios.delete(`/api/insumos/${r.id}`, { withCredentials: true })
    fetchInsumos()
  } catch (e) {
    alert(e?.response?.data?.message || 'No se pudo eliminar')
  }
}

// --------- lifecycle ----------
onMounted(async () => {
  await loadCategorias()
  await fetchInsumos()
})
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Insumos</h1>
        <button
          class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700"
          @click="openCrear">
          Nuevo
        </button>
      </div>

      <!-- filtros -->
      <div class="rounded-xl border bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
          <div class="md:col-span-2">
            <label class="text-xs text-slate-500">Buscar por nombre/código</label>
            <input
              class="mt-1 w-full rounded-lg border px-3 py-2"
              placeholder="Ej: pipeta, HCL-1L…"
              v-model.trim="filtros.q"
              @keyup.enter="resetAndFetch"/>
          </div>

          <div>
            <label class="text-xs text-slate-500">Categoría</label>
            <select class="mt-1 w-full rounded-lg border px-3 py-2"
                    v-model="filtros.categoria_id"
                    @change="resetAndFetch">
              <option value="">(todas)</option>
              <option v-for="c in categorias" :key="c.id" :value="c.id">
                {{ c.nombre }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button class="w-full rounded-lg border px-3 py-2 hover:bg-slate-50"
                    @click="resetAndFetch">
              Buscar
            </button>
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
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-3 py-2 border-b">Código</th>
              <th class="px-3 py-2 border-b">Nombre</th>
              <th class="px-3 py-2 border-b">Unidad</th>
              <th class="px-3 py-2 border-b">Categoría</th>
              <th class="px-3 py-2 border-b text-right">Stock</th>
              <th class="px-3 py-2 border-b text-right">Mínimo</th>
              <th class="px-3 py-2 border-b">Activo</th>
              <th class="px-3 py-2 border-b"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.id" class="hover:bg-slate-50">
              <td class="px-3 py-2 border-b">{{ r.codigo || '—' }}</td>
              <td class="px-3 py-2 border-b font-medium text-slate-800">{{ r.nombre }}</td>
              <td class="px-3 py-2 border-b">{{ r.unidad || '—' }}</td>
              <td class="px-3 py-2 border-b">{{ r.categoria || '—' }}</td>
              <td class="px-3 py-2 border-b text-right">{{ r.stock ?? 0 }}</td>
              <td class="px-3 py-2 border-b text-right">{{ r.minimo ?? 0 }}</td>
              <td class="px-3 py-2 border-b">
                <span :class="r.activo ? 'text-emerald-700' : 'text-slate-500'">
                  {{ r.activo ? 'Sí' : 'No' }}
                </span>
              </td>
              <td class="px-3 py-2 border-b">
                <div class="flex justify-end gap-2">
                  <button class="rounded border px-2 py-1 text-xs hover:bg-slate-50"
                          @click="openEditar(r)">Editar</button>
                  <button class="rounded border px-2 py-1 text-xs text-rose-700 hover:bg-rose-50"
                          @click="deleteRow(r)">Eliminar</button>
                </div>
              </td>
            </tr>

            <tr v-if="rows.length === 0">
              <td colspan="8" class="px-3 py-10 text-center text-slate-500">
                Sin resultados.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- paginación -->
      <div v-if="meta" class="flex items-center gap-2 text-sm">
        <button class="rounded border px-3 py-1 disabled:opacity-50"
                :disabled="page<=1"
                @click="page--; fetchInsumos()">Anterior</button>
        <span>Página {{ meta.current_page }} de {{ meta.last_page }}</span>
        <button class="rounded border px-3 py-1 disabled:opacity-50"
                :disabled="page>=meta.last_page"
                @click="page++; fetchInsumos()">Siguiente</button>
      </div>
    </div>

    <!-- Modal crear/editar -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeForm">
      <div class="w-full max-w-xl rounded-xl bg-white p-5 shadow-xl">
        <div class="mb-3 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-800">
            {{ formMode === 'crear' ? 'Nuevo Insumo' : 'Editar Insumo' }}
          </h2>
          <button class="rounded bg-slate-100 px-3 py-1" @click="closeForm">Cerrar</button>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
          <div>
            <label class="text-xs text-slate-500">Nombre *</label>
            <input v-model.trim="form.nombre" class="mt-1 w-full rounded border px-3 py-2" />
            <p v-if="formErr?.nombre" class="text-xs text-rose-600 mt-1">{{ formErr.nombre[0] }}</p>
          </div>
          <div>
            <label class="text-xs text-slate-500">Código</label>
            <input v-model.trim="form.codigo" class="mt-1 w-full rounded border px-3 py-2" />
            <p v-if="formErr?.codigo" class="text-xs text-rose-600 mt-1">{{ formErr.codigo[0] }}</p>
          </div>

          <div>
            <label class="text-xs text-slate-500">Unidad</label>
            <input v-model.trim="form.unidad" class="mt-1 w-full rounded border px-3 py-2" />
            <p v-if="formErr?.unidad" class="text-xs text-rose-600 mt-1">{{ formErr.unidad[0] }}</p>
          </div>
          <div>
            <label class="text-xs text-slate-500">Categoría</label>
            <select v-model="form.categoria_id" class="mt-1 w-full rounded border px-3 py-2">
              <option :value="null">(sin categoría)</option>
              <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
            <p v-if="formErr?.categoria_id" class="text-xs text-rose-600 mt-1">{{ formErr.categoria_id[0] }}</p>
          </div>

          <div>
            <label class="text-xs text-slate-500">Stock</label>
            <input type="number" step="0.01" min="0" v-model.number="form.stock"
                   class="mt-1 w-full rounded border px-3 py-2" />
            <p v-if="formErr?.stock" class="text-xs text-rose-600 mt-1">{{ formErr.stock[0] }}</p>
          </div>
          <div>
            <label class="text-xs text-slate-500">Mínimo</label>
            <input type="number" step="0.01" min="0" v-model.number="form.minimo"
                   class="mt-1 w-full rounded border px-3 py-2" />
            <p v-if="formErr?.minimo" class="text-xs text-rose-600 mt-1">{{ formErr.minimo[0] }}</p>
          </div>

          <div class="md:col-span-2 flex items-center gap-2">
            <input id="activo" type="checkbox" v-model="form.activo" class="h-4 w-4">
            <label for="activo" class="text-sm text-slate-700">Activo</label>
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button class="rounded border px-3 py-2" @click="closeForm" :disabled="saving">Cancelar</button>
          <button class="rounded bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
                  @click="saveForm" :disabled="saving">
            {{ saving ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </AppShell>
</template>
