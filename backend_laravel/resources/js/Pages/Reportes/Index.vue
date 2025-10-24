<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'

axios.defaults.withCredentials = true

// Usa otro nombre para evitar choques con 'data'
const metrics = ref({
  solicitudes: {},
  prestamos: {},
  totales: { solicitudes: 0, prestamos: 0 }
})

const estado = ref('')
const desde = ref('')
const hasta = ref('')

const loading = ref(true)
const error = ref('')

async function load () {
  loading.value = true
  error.value = ''
  try {
    // Si ya nombraste la ruta en api.php como 'api.reportes.resumen', puedes usar:
    // const { data: payload } = await axios.get(route('api.reportes.resumen'))
    const { data: payload } = await axios.get('/api/reportes/resumen')
    metrics.value = payload ?? { solicitudes:{}, prestamos:{}, totales:{ solicitudes:0, prestamos:0 } }
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}

function exportCSV () {
  const params = new URLSearchParams()
  if (estado.value) params.set('estado', estado.value)
  if (desde.value)  params.set('desde',  desde.value)
  if (hasta.value)  params.set('hasta',  hasta.value)
  const url = '/api/reportes/solicitudes-csv' + (params.toString() ? `?${params.toString()}` : '')
  window.open(url, '_blank')
}

onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold">Reportes</h1>

      <div class="grid md:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm border">
          <div class="text-slate-500 text-sm">Total Solicitudes</div>
          <div class="text-3xl font-semibold">{{ metrics.totales?.solicitudes ?? 0 }}</div>
          <div class="mt-2 text-xs text-slate-500">
            <span v-for="(n, est) in metrics.solicitudes" :key="est" class="mr-3">{{ est }}: {{ n }}</span>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm border">
          <div class="text-slate-500 text-sm">Total Préstamos</div>
          <div class="text-3xl font-semibold">{{ metrics.totales?.prestamos ?? 0 }}</div>
          <div class="mt-2 text-xs text-slate-500">
            <span v-for="(n, est) in metrics.prestamos" :key="est" class="mr-3">{{ est }}: {{ n }}</span>
          </div>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm border">
          <div class="text-slate-500 text-sm mb-2">Exportar CSV de Solicitudes</div>
          <div class="grid grid-cols-3 gap-2">
            <select v-model="estado" class="rounded border p-2">
              <option value="">(todas)</option>
              <option>PENDIENTE</option>
              <option>APROBADO</option>
              <option>RECHAZADO</option>
              <option>ENTREGADO</option>
            </select>
            <input v-model="desde" type="date" class="rounded border p-2" />
            <input v-model="hasta" type="date" class="rounded border p-2" />
          </div>
          <button class="mt-3 rounded border px-3 py-2 hover:bg-slate-50" @click="exportCSV">
            Exportar CSV
          </button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">
        {{ error }}
      </div>
      <div v-else-if="loading" class="rounded border bg-white p-3">
        Cargando…
      </div>
    </div>
  </AppShell>
</template>
