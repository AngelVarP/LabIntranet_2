<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'
import { Link } from '@inertiajs/vue3'
axios.defaults.withCredentials = true

const estado = ref('')
const rows = ref([]); const loading = ref(true); const error = ref('')

async function load(){
  loading.value = true; error.value = ''
  try{
    const { data } = await axios.get('/api/alumno/mis-solicitudes',{
      params:{ estado: estado.value || undefined, per_page: 50 }
    })
    rows.value = data?.data ?? data ?? []
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}
onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold">Mis Solicitudes</h1>

      <div class="rounded-xl bg-white p-4 shadow-sm border flex items-center gap-3">
        <select v-model="estado" class="rounded border p-2">
          <option value="">(todas)</option>
          <option>PENDIENTE</option><option>APROBADO</option><option>RECHAZADO</option><option>ENTREGADO</option>
        </select>
        <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="load">Aplicar</button>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>
      <div v-else-if="loading" class="rounded border bg-white p-3">Cargando…</div>

      <div v-else class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="text-left text-slate-500">
            <th class="px-3 py-2">#</th><th class="px-3 py-2">Grupo</th>
            <th class="px-3 py-2">Práctica</th><th class="px-3 py-2">Laboratorio</th>
            <th class="px-3 py-2">Estado</th><th></th>
          </tr></thead>
          <tbody>
            <tr v-for="r in rows" :key="r.id" class="border-t">
              <td class="px-3 py-2">{{ r.id }}</td>
              <td class="px-3 py-2">{{ r.grupo_nombre ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.practica_titulo ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.laboratorio_nombre ?? '-' }}</td>
              <td class="px-3 py-2">{{ r.estado ?? '-' }}</td>
              <td class="px-3 py-2">
                <Link :href="`/solicitudes/${r.id}`" class="text-indigo-600 hover:underline">Ver</Link>
              </td>
            </tr>
            <tr v-if="!rows.length"><td colspan="6" class="px-3 py-8 text-center text-slate-500">Sin registros</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
