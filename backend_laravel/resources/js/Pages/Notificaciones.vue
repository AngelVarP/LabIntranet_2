<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../Layouts/AppShell.vue'
import { showToast } from '../Components/ui/useToast.js'
import { API } from '../api/api.js'

const filtros = reactive({ q:'', estado:'' }) // estado: '' | 'LEIDA' | 'NUEVA'
const rows = ref([])
const meta = ref(null)
const pageNum = ref(1)
const perPage = 12
const loading = ref(false)
const error = ref(null)

async function load(p=1){
  loading.value=true; error.value=null
  try{
    const { data } = await axios.get(API.notificaciones, {
      params:{
        q: filtros.q || undefined,
        estado: filtros.estado || undefined,
        page: p,
        per_page: perPage
      }
    })
    rows.value = Array.isArray(data) ? data : (data?.data ?? [])
    meta.value = Array.isArray(data) ? { current_page:1, last_page:1, total:rows.value.length } : (data?.meta ?? null)
    pageNum.value = meta.value?.current_page ?? 1
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    rows.value=[]; meta.value=null
  }finally{ loading.value=false }
}

async function marcarLeida(id){
  try{
    await axios.post(`${API.notificaciones}/${id}/leer`)
    showToast('Notificación marcada como leída')
    await load(pageNum.value)
  }catch(e){ showToast('No se pudo marcar','error') }
}
async function leerTodas(){
  try{
    await axios.post(`${API.notificaciones}/leer-todas`)
    showToast('Todas marcadas como leídas')
    await load(pageNum.value)
  }catch(e){ showToast('No se pudo completar','error') }
}

onMounted(() => { load() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Notificaciones</h1>
        <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="leerTodas">Leer todas</button>
      </div>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
          <input v-model.trim="filtros.q" class="rounded border p-2 md:col-span-2" placeholder="Buscar…" />
          <select v-model="filtros.estado" class="rounded border p-2">
            <option value="">(todas)</option>
            <option value="NUEVA">Nuevas</option>
            <option value="LEIDA">Leídas</option>
          </select>
          <div class="md:col-span-1 flex justify-end">
            <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load(1)">Aplicar</button>
          </div>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3 text-slate-500">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">Título</th>
              <th class="px-3 py-2">Mensaje</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in rows" :key="n.id" class="border-t">
              <td class="px-3 py-2">#{{ n.id }}</td>
              <td class="px-3 py-2 font-medium">{{ n.titulo ?? '—' }}</td>
              <td class="px-3 py-2">{{ n.mensaje ?? n.body ?? '—' }}</td>
              <td class="px-3 py-2">
                <span :class="(n.estado==='LEIDA' ? 'bg-slate-500' : 'bg-amber-500') + ' text-white rounded px-2 py-0.5 text-xs'">
                  {{ n.estado ?? 'NUEVA' }}
                </span>
              </td>
              <td class="px-3 py-2">{{ n.created_at_fmt ?? n.created_at }}</td>
              <td class="px-3 py-2 text-right">
                <button class="rounded border px-2 py-1" @click="marcarLeida(n.id)">Marcar leída</button>
              </td>
            </tr>
            <tr v-if="!rows.length">
              <td colspan="6" class="px-3 py-6 text-center text-slate-500">Sin notificaciones</td>
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
