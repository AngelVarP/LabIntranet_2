<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../Layouts/AppShell.vue'

axios.defaults.withCredentials = true
async function ensureCsrf(){ try{ await axios.get('/sanctum/csrf-cookie') }catch{} }

const filtros = reactive({ curso_id:'', seccion:'', desde:'', hasta:'' })
const cursos = ref([])
const items  = ref([])
const loading = ref(false)
const error   = ref(null)

async function loadCursos(){
  try{
    const { data } = await axios.get('/api/lookups/cursos').catch(()=>({ data:[] }))
    cursos.value = data?.data ?? data ?? []
  }catch{ cursos.value = [] }
}

async function load(){
  loading.value=true; error.value=null
  try{
    const { data } = await axios.get('/api/academico/proximos', {
      params:{
        curso_id: filtros.curso_id || undefined,
        seccion:  filtros.seccion   || undefined,
        desde:    filtros.desde     || undefined,
        hasta:    filtros.hasta     || undefined,
      }
    }).catch(()=>({ data:[] }))
    items.value = Array.isArray(data) ? data : (data?.data ?? [])
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    items.value = []
  }finally{ loading.value=false }
}

onMounted(async () => { await Promise.all([ensureCsrf(), loadCursos()]); load() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <h1 class="text-2xl font-semibold text-slate-800">Académico</h1>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
          <select v-model="filtros.curso_id" class="rounded border p-2">
            <option value="">(todos los cursos)</option>
            <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.codigo }} — {{ c.nombre }}</option>
          </select>
          <input v-model.trim="filtros.seccion" class="rounded border p-2" placeholder="Sección (A, B…)" />
          <input v-model="filtros.desde" type="date" class="rounded border p-2" />
          <input v-model="filtros.hasta" type="date" class="rounded border p-2" />
          <div class="md:col-span-4 flex justify-end">
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load">Aplicar</button>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2">Curso</th>
              <th class="px-3 py-2">Sección</th>
              <th class="px-3 py-2">Práctica / Tema</th>
              <th class="px-3 py-2">Grupo</th>
              <th class="px-3 py-2">Laboratorio</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="it in items" :key="it.id" class="border-t">
              <td class="px-3 py-2">{{ it.fecha_fmt ?? it.fecha }}</td>
              <td class="px-3 py-2">{{ it.curso_codigo ?? it.curso?.codigo }} — {{ it.curso_nombre ?? it.curso?.nombre }}</td>
              <td class="px-3 py-2">{{ it.seccion ?? it.seccion_nombre }}</td>
              <td class="px-3 py-2">{{ it.practica ?? it.practica_nombre ?? it.tema }}</td>
              <td class="px-3 py-2">{{ it.grupo ?? '-' }}</td>
              <td class="px-3 py-2">{{ it.laboratorio ?? it.laboratorio_nombre }}</td>
            </tr>
            <tr v-if="!items.length">
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Sin próximos eventos</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
