<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import { API } from '../../api/api.js'

const filtros = reactive({ item_id:'', tipo:'', laboratorio_id:'', desde:'', hasta:'' })
const tipos = ['INGRESO','EGRESO','AJUSTE']
const labs = ref([])
const itemTexto = ref('') // display

const rows = ref([]); const loading = ref(false); const error = ref(null)

async function loadLabs(){ try{ const {data}=await axios.get(API.lookLabs); labs.value=data?.data ?? data ?? [] }catch{ labs.value=[] } }

async function buscarItem(){
  try{
    // usa tus lookups reales
    const url = new URL(API.lookInsumos, window.location.origin)
    url.searchParams.set('q', itemTexto.value)
    const res = await fetch(url, { credentials:'include' })
    const data = await res.json()
    const it = (Array.isArray(data)? data : (data?.data ?? []))[0]
    filtros.item_id = it?.id || ''
  }catch{ filtros.item_id='' }
}

async function load(){
  loading.value=true; error.value=null
  try{
    const { data } = await axios.get(API.kardex, { params:{
      item_id: filtros.item_id || undefined,
      tipo: filtros.tipo || undefined,
      laboratorio_id: filtros.laboratorio_id || undefined,
      desde: filtros.desde || undefined,
      hasta: filtros.hasta || undefined,
    }})
    rows.value = Array.isArray(data)? data : (data?.data ?? [])
  }catch(e){ error.value = e?.response?.data?.message || e.message; rows.value=[] }
  finally{ loading.value=false }
}

onMounted(async()=>{ await loadLabs(); load() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <h1 class="text-2xl font-semibold text-slate-800">Kardex</h1>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-6">
          <div class="md:col-span-2">
            <label class="block text-xs text-slate-500 mb-1">Ítem (buscar)</label>
            <input v-model.trim="itemTexto" class="w-full rounded border p-2" placeholder="nombre/código" @change="buscarItem" />
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Tipo</label>
            <select v-model="filtros.tipo" class="w-full rounded border p-2">
              <option value="">(todos)</option>
              <option v-for="t in tipos" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Laboratorio</label>
            <select v-model="filtros.laboratorio_id" class="w-full rounded border p-2">
              <option value="">(todos)</option>
              <option v-for="l in labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
          </div>
          <div><label class="block text-xs text-slate-500 mb-1">Desde</label><input v-model="filtros.desde" type="date" class="w-full rounded border p-2" /></div>
          <div><label class="block text-xs text-slate-500 mb-1">Hasta</label><input v-model="filtros.hasta" type="date" class="w-full rounded border p-2" /></div>
          <div class="md:col-span-6 flex justify-end">
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
              <th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Item</th>
              <th class="px-3 py-2">Cantidad</th><th class="px-3 py-2">Unidad</th><th class="px-3 py-2">Lab</th><th class="px-3 py-2">Obs.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(r,i) in rows" :key="i" class="border-t">
              <td class="px-3 py-2">{{ r.fecha_fmt ?? r.fecha }}</td>
              <td class="px-3 py-2">{{ r.tipo }}</td>
              <td class="px-3 py-2">{{ r.item_nombre ?? r.nombre }} ({{ r.item_codigo ?? r.codigo }})</td>
              <td class="px-3 py-2">{{ r.cantidad }}</td>
              <td class="px-3 py-2">{{ r.unidad ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.laboratorio_nombre ?? r.laboratorio }}</td>
              <td class="px-3 py-2">{{ r.obs ?? r.observacion ?? '' }}</td>
            </tr>
            <tr v-if="!rows.length"><td colspan="7" class="px-3 py-6 text-center text-slate-500">Sin movimientos</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
