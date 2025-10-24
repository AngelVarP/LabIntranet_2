<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

const loading = ref(false)
const error   = ref(null)
const soloNoLeidas = ref(false)
const items = ref([])

async function fetchList(){
  loading.value = true; error.value = null
  try{
    const { data } = await axios.get('/api/notificaciones', {
      withCredentials: true,
      params: { solo_no_leidas: soloNoLeidas.value ? 1 : undefined }
    })
    items.value = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
  }catch(e){
    error.value = e?.response?.data?.message || e.message
    items.value = []
  }finally{
    loading.value = false
  }
}
async function leer(id){
  try{
    await axios.post(`/api/notificaciones/${id}/leer`, {}, { withCredentials:true })
    items.value = items.value.map(n => n.id===id ? { ...n, leida:1 } : n)
  }catch(e){ /* noop */ }
}
async function leerTodas(){
  try{
    await axios.post('/api/notificaciones/leer-todas', {}, { withCredentials:true })
    items.value = items.value.map(n => ({ ...n, leida:1 }))
  }catch(e){ /* noop */ }
}

onMounted(fetchList)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Notificaciones</h1>
        <div class="flex items-center gap-2">
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" v-model="soloNoLeidas" @change="fetchList" />
            Solo no leídas
          </label>
          <button class="rounded bg-slate-100 px-3 py-2 hover:bg-slate-200" @click="leerTodas">
            Marcar todas
          </button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-if="loading" class="rounded border bg-white p-4 text-slate-500">Cargando…</div>

      <div v-if="!loading && items.length" class="space-y-2">
        <div v-for="n in items" :key="n.id"
             class="rounded-xl border bg-white p-4 shadow-sm flex items-start justify-between">
          <div>
            <div class="text-sm font-semibold text-slate-800">{{ n.titulo }}</div>
            <div class="text-xs text-slate-500 mt-1">{{ n.tipo }} · {{ new Date(n.creado_at).toLocaleString() }}</div>
            <div class="text-sm text-slate-700 mt-2" v-if="n.cuerpo">{{ n.cuerpo }}</div>
          </div>
          <div class="pl-3">
            <span v-if="n.leida" class="text-xs text-slate-400">Leída</span>
            <button v-else class="rounded border px-3 py-1 text-xs hover:bg-slate-50"
                    @click="leer(n.id)">Marcar leída</button>
          </div>
        </div>
      </div>

      <p v-else-if="!loading" class="text-slate-500">Sin notificaciones.</p>
    </div>
  </AppShell>
</template>
