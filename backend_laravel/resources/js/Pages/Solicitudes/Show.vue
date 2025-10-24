<script setup>
import axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import AppShell from '../../Layouts/AppShell.vue'
import { usePage, Link } from '@inertiajs/vue3'

const page = usePage()
const roles = computed(() => (page.props?.auth?.user?.roles ?? []).map(r => r.toLowerCase()))
const canAtender = computed(() => roles.value.includes('admin') || roles.value.includes('tecnico'))

const id = Number(window.location.pathname.split('/').pop()) || 0
const solicitud = ref(null)
const items = ref([])
const loading = ref(true)
const msg = ref('')

async function load() {
  loading.value = true; msg.value=''
  try {
    const { data } = await axios.get(`/api/solicitudes/${id}`)
    solicitud.value = data.solicitud
    items.value = data.items ?? []
  } catch (e) {
    msg.value = e?.response?.data?.message || e.message
  } finally { loading.value = false }
}

async function cambiar(estado) {
  try {
    await axios.post(`/api/solicitudes/${id}/estado`, { estado })
    solicitud.value.estado = estado
    msg.value = `Estado cambiado a ${estado}`
  } catch (e) {
    msg.value = e?.response?.data?.message || e.message
  }
}

onMounted(load)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-5">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">Solicitud #{{ id }}</h1>
          <p class="text-slate-500 text-sm">Detalle y seguimiento</p>
        </div>
        <Link href="/tablon" class="rounded border px-3 py-2 hover:bg-slate-50">Volver</Link>
      </div>

      <div v-if="msg" class="rounded border bg-amber-50 p-3 text-amber-800">{{ msg }}</div>

      <!-- Acciones -->
      <div v-if="canAtender" class="flex gap-2">
        <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="cambiar('APROBADO')">Aprobar</button>
        <button class="rounded bg-rose-600 px-3 py-2 text-white" @click="cambiar('RECHAZADO')">Rechazar</button>
        <button class="rounded bg-indigo-600 px-3 py-2 text-white" @click="cambiar('ENTREGADO')">Marcar Entregado</button>
      </div>

      <!-- Info -->
      <div v-if="solicitud" class="rounded-2xl bg-white p-4 shadow-sm border">
        <div class="grid md:grid-cols-2 gap-3 text-sm">
          <div><span class="text-slate-500">Grupo:</span> {{ solicitud.grupo ?? '-' }}</div>
          <div><span class="text-slate-500">Práctica:</span> {{ solicitud.practica ?? solicitud.practica_titulo ?? '-' }}</div>
          <div><span class="text-slate-500">Laboratorio:</span> {{ solicitud.laboratorio ?? solicitud.laboratorio_nombre ?? '-' }}</div>
          <div><span class="text-slate-500">Estado:</span> {{ solicitud.estado ?? '-' }}</div>
        </div>
      </div>

      <!-- Items -->
      <div class="rounded-2xl bg-white p-2 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="text-left text-slate-500">
            <th class="px-3 py-2">Ítem</th><th class="px-3 py-2">Cant. solicitada</th><th class="px-3 py-2">Unidad</th>
          </tr></thead>
          <tbody>
            <tr v-for="it in items" :key="it.id" class="border-t">
              <td class="px-3 py-2">{{ it.item_nombre ?? '-' }}</td>
              <td class="px-3 py-2">{{ it.cantidad_solic ?? it.cantidad ?? 0 }}</td>
              <td class="px-3 py-2">{{ it.unidad ?? '-' }}</td>
            </tr>
            <tr v-if="!items.length"><td colspan="3" class="text-center text-slate-500 px-3 py-6">Sin ítems</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppShell>
</template>
