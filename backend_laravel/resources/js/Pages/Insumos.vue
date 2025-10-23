<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const q = ref('')
const categoria_id = ref('')
const perPage = 10
const page = ref(1)
const loading = ref(false)
const error = ref(null)
const rows = ref([])
const meta = ref(null)

const showForm = ref(false)
const editId = ref(null)
const form = ref({
  nombre: '', codigo: '', unidad: '',
  stock: 0, minimo: 0, categoria_id: null, activo: true
})

async function fetchInsumos() {
  loading.value = true; error.value = null
  try {
    const { data } = await axios.get('/api/insumos', {
      params: { q: q.value, categoria_id: categoria_id.value || undefined, page: page.value, per_page: perPage }
    })
    rows.value = data.data
    meta.value = data.meta
  } catch (e) {
    error.value = e?.response?.data || e.message
  } finally {
    loading.value = false
  }
}
function setPage(p){ page.value = p; fetchInsumos() }

function nuevo() {
  editId.value = null
  form.value = { nombre:'', codigo:'', unidad:'', stock:0, minimo:0, categoria_id:null, activo:true }
  showForm.value = true
}
function editar(r) {
  editId.value = r.id
  form.value = {
    nombre: r.nombre, codigo: r.codigo, unidad: r.unidad,
    stock: r.stock ?? 0, minimo: r.minimo ?? 0,
    categoria_id: r.categoria_id, activo: !!r.activo
  }
  showForm.value = true
}
async function guardar() {
  try {
    if (editId.value) {
      await axios.put(`/api/insumos/${editId.value}`, form.value)
    } else {
      await axios.post('/api/insumos', form.value)
    }
    showForm.value = false
    fetchInsumos()
  } catch (e) {
    alert('Error'); console.error(e?.response?.data || e)
  }
}
async function eliminar(id) {
  if (!confirm('¿Eliminar insumo?')) return
  try {
    await axios.delete(`/api/insumos/${id}`)
    fetchInsumos()
  } catch (e) {
    alert('Error'); console.error(e?.response?.data || e)
  }
}

onMounted(fetchInsumos)
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Insumos</h1>

    <div class="flex gap-2 mb-4">
      <input class="border p-2" placeholder="Buscar por nombre/código" v-model="q" @input="page=1;fetchInsumos()" />
      <!-- si luego quieres combo de categorías, rellénalo -->
      <!-- <select class="border p-2" v-model="categoria_id" @change="page=1;fetchInsumos()">
        <option value="">Todas las categorías</option>
      </select> -->
      <button class="px-3 py-2 rounded bg-emerald-600 text-white" @click="nuevo()">Nuevo</button>
    </div>

    <div v-if="error" class="text-red-600 mb-3">{{ error }}</div>
    <div v-if="loading">Cargando…</div>

    <table v-if="!loading && rows.length" class="w-full border text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border">ID</th>
          <th class="p-2 border">Nombre</th>
          <th class="p-2 border">Código</th>
          <th class="p-2 border">Unidad</th>
          <th class="p-2 border">Stock</th>
          <th class="p-2 border">Mínimo</th>
          <th class="p-2 border">Categoría</th>
          <th class="p-2 border">Activo</th>
          <th class="p-2 border">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="r in rows" :key="r.id">
          <td class="p-2 border">{{ r.id }}</td>
          <td class="p-2 border">{{ r.nombre }}</td>
          <td class="p-2 border">{{ r.codigo || '-' }}</td>
          <td class="p-2 border">{{ r.unidad || '-' }}</td>
          <td class="p-2 border text-right">{{ r.stock ?? 0 }}</td>
          <td class="p-2 border text-right">{{ r.minimo ?? 0 }}</td>
          <td class="p-2 border">{{ r.categoria || '-' }}</td>
          <td class="p-2 border">{{ r.activo ? 'Sí' : 'No' }}</td>
          <td class="p-2 border">
            <button class="text-indigo-600 underline mr-2" @click="editar(r)">Editar</button>
            <button class="text-red-600 underline" @click="eliminar(r.id)">Eliminar</button>
          </td>
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

  <!-- Modal formulario -->
  <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white w-full max-w-lg rounded shadow-lg p-4">
      <h3 class="text-lg font-semibold mb-3">{{ editId ? 'Editar' : 'Nuevo' }} insumo</h3>
      <div class="grid grid-cols-2 gap-3">
        <input class="border p-2 col-span-2" placeholder="Nombre" v-model="form.nombre">
        <input class="border p-2" placeholder="Código" v-model="form.codigo">
        <input class="border p-2" placeholder="Unidad (u, L, g, etc.)" v-model="form.unidad">
        <input class="border p-2" type="number" min="0" step="0.01" placeholder="Stock" v-model.number="form.stock">
        <input class="border p-2" type="number" min="0" step="0.01" placeholder="Mínimo" v-model.number="form.minimo">
        <input class="border p-2" type="number" min="1" placeholder="Categoria ID (opcional)" v-model.number="form.categoria_id">
        <label class="flex items-center gap-2"><input type="checkbox" v-model="form.activo"> Activo</label>
      </div>
      <div class="mt-3 flex justify-end gap-2">
        <button class="px-3 py-2 rounded bg-gray-200" @click="showForm=false">Cancelar</button>
        <button class="px-3 py-2 rounded bg-emerald-600 text-white" @click="guardar()">Guardar</button>
      </div>
    </div>
  </div>
</template>
