<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import { Link } from '@inertiajs/vue3'

const rows = ref([]); const loading = ref(true); const error = ref(null)
const estado = ref('')

async function load() {
  loading.value = true; error.value = null
  try {
    const { data } = await axios.get('/api/solicitudes/mias', { params:{ estado: estado.value || undefined } })
    rows.value = data?.data ?? data ?? []
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <h1 class="text-2xl font-semibold text-slate-800">Mis Solicitudes</h1>

      <div class="rounded-xl bg-white p-4 shadow-sm border">
        <div class="flex items-center gap-3">
          <select v-model="estado" class="rounded border p-2">
            <option value="">(todas)</option>
            <option>PENDIENTE</option>
            <option>APROBADO</option>
            <option>RECHAZADO</option>
            <option>ENTREGADO</option>
          </select>
          <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load">Aplicar</button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded bg-white border p-3">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="px-3 py-2">#</th>
              <th class="px-3 py-2">Práctica</th>
              <th class="px-3 py-2">Laboratorio</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Creado</th>
              <th class="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in rows" :key="r.solicitud_id ?? r.id" class="border-t">
              <td class="px-3 py-2">{{ r.solicitud_id ?? r.id }}</td>
              <td class="px-3 py-2">{{ r.practica_titulo ?? r.practica ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.laboratorio_nombre ?? r.laboratorio ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.estado ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.creado_at ?? r.created_at ?? '-' }}</td>
              <td class="px-3 py-2">
                <Link :href="`/solicitudes/${r.solicitud_id ?? r.id}`" class="text-indigo-600 hover:underline">Ver</Link>
              </td>
            </tr>
            <tr v-if="!rows.length">
              <td colspan="6" class="px-3 py-8 text-center text-slate-500">Sin registros</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
