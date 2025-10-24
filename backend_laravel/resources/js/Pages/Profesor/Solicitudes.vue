<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'

axios.defaults.withCredentials = true
async function ensureCsrf(){ try{ await axios.get('/sanctum/csrf-cookie') }catch{} }

const filtros = reactive({ q:'', estado:'', curso_id:'', seccion:'', desde:'', hasta:'' })
const estados = ['PENDIENTE','APROBADO','RECHAZADO','PREPARADO','ENTREGADO','CERRADO']
const cursos  = ref([])

const rows = ref([])
const meta = ref(null)
const pageNum = ref(1)
const perPage = 12
const loading = ref(false)
const error = ref(null)

async function loadCursos(){
  try{
    // Preferido:
    const r1 = await axios.get('/api/profesor/cursos').catch(()=>null)
    if (r1) { cursos.value = r1.data?.data ?? r1.data ?? []; return }
    // Fallback:
    const r2 = await axios.get('/api/lookups/cursos', { params:{ mios:1 } })
    cursos.value = r2.data?.data ?? r2.data ?? []
  }catch{ cursos.value = [] }
}

async function load(p=1){
  loading.value=true; error.value=null
  try{
    // Preferido:
    const r1 = await axios.get('/api/profesor/solicitudes', {
      params:{ ...filtros, page:p, per_page:perPage,
        q:filtros.q || undefined,
        estado:filtros.estado || undefined,
        curso_id:filtros.curso_id || undefined,
        seccion:filtros.seccion || undefined,
        desde:filtros.desde || undefined,
        hasta:filtros.hasta || undefined
      }
    }).catch(()=>null)

    const data = r1 ? r1.data : null
    if (data){
      rows.value = Array.isArray(data) ? data : (data?.data ?? [])
      meta.value = Array.isArray(data) ? { current_page:1, last_page:1, total:rows.value.length } : (data?.meta ?? null)
      pageNum.value = meta.value?.current_page ?? 1
      return
    }

    // Fallback general
    const r2 = await axios.get('/api/solicitudes', {
      params:{ profesor:1, ...filtros, page:p, per_page:perPage }
    })
    const data2 = r2.data
    rows.value = Array.isArray(data2) ? data2 : (data2?.data ?? [])
    meta.value = Array.isArray(data2) ? { current_page:1, last_page:1, total:rows.value.length } : (data2?.meta ?? null)
    pageNum.value = meta.value?.current_page ?? 1

  }catch(e){
    error.value = e?.response?.data?.message || e.message
    rows.value=[]; meta.value=null
  }finally{ loading.value=false }
}

onMounted(async () => { await Promise.all([ensureCsrf(), loadCursos()]); load() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <h1 class="text-2xl font-semibold text-slate-800">Solicitudes de mis cursos</h1>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-6">
          <input v-model.trim="filtros.q" class="rounded border p-2 md:col-span-2" placeholder="Buscar…" />
          <select v-model="filtros.estado" class="rounded border p-2">
            <option value="">(todas)</option>
            <option v-for="e in estados" :key="e" :value="e">{{ e }}</option>
          </select>
          <select v-model="filtros.curso_id" class="rounded border p-2">
            <option value="">(todos los cursos)</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.codigo }} — {{ c.nombre }}</option>
          </select>
          <input v-model.trim="filtros.seccion" class="rounded border p-2" placeholder="Sección (A, B…)" />
          <input v-model="filtros.desde" type="date" class="rounded border p-2" />
          <input v-model="filtros.hasta" type="date" class="rounded border p-2" />
          <div class="md:col-span-6 flex justify-end">
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load(1)">Aplicar</button>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">Curso / Sección</th>
              <th class="px-3 py-2">Práctica</th>
              <th class="px-3 py-2">Grupo</th>
              <th class="px-3 py-2">Laboratorio</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Actualizado</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="px-3 py-2">#{{ r.id }}</td>
              <td class="px-3 py-2">
                <div class="font-medium">{{ r.curso_nombre ?? r.curso }}</div>
                <div class="text-xs text-slate-500">{{ r.seccion ?? r.seccion_nombre }}</div>
              </td>
              <td class="px-3 py-2">{{ r.practica_nombre ?? r.practica }}</td>
              <td class="px-3 py-2">{{ r.grupo ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.laboratorio_nombre ?? r.laboratorio }}</td>
              <td class="px-3 py-2">
                <span :class="(r.estado==='RECHAZADO' ? 'bg-rose-600' :
                              r.estado==='APROBADO' ? 'bg-blue-600' :
                              r.estado==='PENDIENTE' ? 'bg-amber-500' :
                              r.estado==='PREPARADO'? 'bg-indigo-600' :
                              r.estado==='ENTREGADO'? 'bg-emerald-600' : 'bg-slate-500')
                              + ' text-white rounded px-2 py-0.5 text-xs'">
                  {{ r.estado }}
                </span>
              </td>
              <td class="px-3 py-2">{{ r.updated_at_fmt ?? r.updated_at }}</td>
              <td class="px-3 py-2 text-right">
                <div class="flex justify-end gap-2">
                  <Link :href="`/solicitudes/${r.id}`" class="rounded border px-2 py-1 hover:bg-slate-50">Ver</Link>
                </div>
              </td>
            </tr>
            <tr v-if="!rows.length">
              <td colspan="8" class="px-3 py-6 text-center text-slate-500">Sin resultados</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta" class="flex items-center gap-2">
        <button class="rounded border px-2 py-1" :disabled="pageNum<=1" @click="load(pageNum-1)">Anterior</button>
        <div class="text-sm text-slate-600">Página {{ meta.current_page }} / {{ meta.last_page }}</div>
        <button class="rounded border px-2 py-1" :disabled="pageNum>=meta.last_page" @click="load(pageNum+1)">Siguiente</button>
      </div>
    </div>
  </AppShell>
</template>
