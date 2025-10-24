<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../Layouts/AppShell.vue'

const stats = ref({ sol_pendientes:0, prestamos_abiertos:0, insumos:0, equipos:0 })
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/stats/home', { withCredentials:true })
    stats.value = data
  } catch { /* noop */ } finally { loading.value=false }
})
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-slate-800">Dashboard</h1>

      <div v-if="loading" class="rounded bg-white p-4 border">Cargando…</div>
      <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-slate-500 text-sm">Solicitudes pendientes</div>
          <div class="text-3xl font-semibold mt-1">{{ stats.sol_pendientes }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-slate-500 text-sm">Préstamos abiertos</div>
          <div class="text-3xl font-semibold mt-1">{{ stats.prestamos_abiertos }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-slate-500 text-sm">Insumos</div>
          <div class="text-3xl font-semibold mt-1">{{ stats.insumos }}</div>
        </div>
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <div class="text-slate-500 text-sm">Equipos</div>
          <div class="text-3xl font-semibold mt-1">{{ stats.equipos }}</div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
