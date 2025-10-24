<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

// OJO: desde Pages/Equipos.vue es UN nivel arriba:
import AppShell from '../Layouts/AppShell.vue'
import Modal from '../Components/ui/Modal.vue'
import { showToast } from '../Components/ui/useToast.js'
import { ensureCsrf } from '../api/api.js'

const filtros = reactive({ q:'', laboratorio_id:'', activo:'' })
const labs = ref([])

const rows = ref([]); const meta = ref(null)
const pageNum = ref(1); const perPage = 12
const loading = ref(false); const error = ref(null)

async function loadLabs(){
  try{
    const { data } = await axios.get('/api/lookups/laboratorios')
    labs.value = data?.data ?? data ?? []
  }catch{ labs.value = [] }
}

async function load(p=1){
  loading.value = true; error.value = null
  try{
    const { data } = await axios.get('/api/equipos', {
      params:{
        q: filtros.q || undefined,
        laboratorio_id: filtros.laboratorio_id || undefined,
        activo: filtros.activo || undefined,
        page: p,
        per_page: perPage
      }
    })
    rows.value = Array.isArray(data) ? data : (data?.data ?? [])
    meta.value  = Array.isArray(data) ? { current_page:1, last_page:1, total:rows.value.length } : (data?.meta ?? null)
    pageNum.value = meta.value?.current_page ?? 1
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    rows.value = []; meta.value = null
  }finally{ loading.value = false }
}

/* ====== Crear/Editar ====== */
const showForm = ref(false); const isEdit = ref(false)
const form = reactive({ id:null, codigo:'', nombre:'', nro_serie:'', laboratorio_id:'', descripcion:'', activo:1 })

function nuevo(){
  isEdit.value=false
  Object.assign(form,{ id:null, codigo:'', nombre:'', nro_serie:'', laboratorio_id:'', descripcion:'', activo:1 })
  showForm.value=true
}
function editar(r){
  isEdit.value=true
  Object.assign(form,{
    id:r.id, codigo:r.codigo ?? '', nombre:r.nombre ?? '', nro_serie:r.nro_serie ?? r.serie ?? '',
    laboratorio_id:r.laboratorio_id ?? r.laboratorio?.id ?? '', descripcion:r.descripcion ?? '', activo:r.activo ? 1 : 0
  })
  showForm.value=true
}

async function guardar(){
  await ensureCsrf()
  try{
    if(isEdit.value){
      await axios.put(`/api/equipos/${form.id}`, form)
      showToast('Equipo actualizado')
    }else{
      await axios.post('/api/equipos', form)
      showToast('Equipo creado')
    }
    showForm.value=false
    await load(pageNum.value)
  }catch(e){ showToast(e?.response?.data?.message || 'No se pudo guardar','error') }
}

async function eliminar(id){
  if(!confirm('¿Eliminar equipo?')) return
  await ensureCsrf()
  try{
    await axios.delete(`/api/equipos/${id}`)
    showToast('Equipo eliminado')
    await load(pageNum.value)
  }catch(e){ showToast(e?.response?.data?.message || 'No se pudo eliminar','error') }
}

onMounted(async()=>{ await loadLabs(); load() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Equipos</h1>
        <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="nuevo">Nuevo</button>
      </div>

      <!-- Filtros -->
      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
          <input v-model.trim="filtros.q" class="rounded border p-2 md:col-span-2" placeholder="Buscar por nombre/código/serie…" />
          <select v-model="filtros.laboratorio_id" class="rounded border p-2">
            <option value="">(todos los labs)</option>
            <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
          </select>
          <select v-model="filtros.activo" class="rounded border p-2">
            <option value="">(todos)</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
          <div class="md:col-span-4 flex justify-end">
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load(1)">Aplicar</button>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">Código</th>
              <th class="px-3 py-2">Nombre</th>
              <th class="px-3 py-2">Serie</th>
              <th class="px-3 py-2">Laboratorio</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="px-3 py-2">#{{ r.id }}</td>
              <td class="px-3 py-2">{{ r.codigo }}</td>
              <td class="px-3 py-2">{{ r.nombre }}</td>
              <td class="px-3 py-2">{{ r.nro_serie ?? r.serie ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.laboratorio_nombre ?? r.laboratorio?.nombre ?? '-' }}</td>
              <td class="px-3 py-2">
                <span :class="(r.activo? 'bg-emerald-600':'bg-slate-400') + ' text-white rounded px-2 py-0.5 text-xs'">
                  {{ r.activo ? 'ACTIVO' : 'INACTIVO' }}
                </span>
              </td>
              <td class="px-3 py-2 text-right">
                <div class="flex justify-end gap-2">
                  <button class="rounded border px-2 py-1" @click="editar(r)">Editar</button>
                  <button class="rounded border px-2 py-1 text-rose-600 border-rose-200" @click="eliminar(r.id)">Eliminar</button>
                </div>
              </td>
            </tr>
            <tr v-if="!rows.length"><td colspan="7" class="px-3 py-6 text-center text-slate-500">Sin resultados</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="meta" class="flex items-center gap-2">
        <button class="rounded border px-2 py-1" :disabled="pageNum<=1" @click="load(pageNum-1)">Anterior</button>
        <div class="text-sm text-slate-600">Página {{ meta.current_page }} / {{ meta.last_page }}</div>
        <button class="rounded border px-2 py-1" :disabled="pageNum>=meta.last_page" @click="load(pageNum+1)">Siguiente</button>
      </div>
    </div>

    <!-- Modal -->
    <Modal :show="showForm" :title="isEdit ? 'Editar equipo' : 'Nuevo equipo'" @close="showForm=false">
      <div class="grid gap-3 md:grid-cols-2">
        <label class="block text-sm">Código
          <input v-model.trim="form.codigo" class="mt-1 w-full rounded border p-2"/>
        </label>
        <label class="block text-sm">Nombre
          <input v-model.trim="form.nombre" class="mt-1 w-full rounded border p-2"/>
        </label>
        <label class="block text-sm">Nro. Serie
          <input v-model.trim="form.nro_serie" class="mt-1 w-full rounded border p-2"/>
        </label>
        <label class="block text-sm">Laboratorio
          <select v-model="form.laboratorio_id" class="mt-1 w-full rounded border p-2">
            <option :value="''">Seleccione…</option>
            <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
          </select>
        </label>
        <label class="flex items-center gap-2 text-sm md:col-span-2">
          <input type="checkbox" v-model="form.activo" :true-value="1" :false-value="0" /> Activo
        </label>
        <label class="block text-sm md:col-span-2">Descripción
          <textarea v-model.trim="form.descripcion" rows="3" class="mt-1 w-full rounded border p-2"></textarea>
        </label>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button class="rounded border px-3 py-2" @click="showForm=false">Cancelar</button>
        <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="guardar">Guardar</button>
      </div>
    </Modal>
  </AppShell>
</template>
