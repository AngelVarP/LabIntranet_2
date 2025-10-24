<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AppShell from '../../Layouts/AppShell.vue'

const secciones = ref([])
const grupos = ref([])
const alumnos = ref([])
const selSeccion = ref(null)
const selGrupo = ref(null)
const seleccionado = ref(null) // alumno_id elegido para delegado

const msg = ref(''); const loading = ref(false); const error = ref('')

async function loadSecciones(){
  error.value=''; loading.value=true
  try{
    const { data } = await axios.get('/api/profesor/mis-secciones')
    secciones.value = data
    if (secciones.value.length){
      selSeccion.value = secciones.value[0]
      await loadGrupos()
    }
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}

async function loadGrupos(){
  grupos.value=[]; alumnos.value=[]; selGrupo.value=null; seleccionado.value=null; msg.value=''
  if (!selSeccion.value) return
  try{
    const { data } = await axios.get(`/api/profesor/secciones/${selSeccion.value.seccion_id}/grupos`)
    grupos.value = data
    if (grupos.value.length){
      selGrupo.value = grupos.value[0]
      await loadAlumnos()
    }
  }catch(e){ error.value = e?.response?.data?.message || e.message }
}

async function loadAlumnos(){
  alumnos.value = []; seleccionado.value=null; msg.value=''
  if (!selGrupo.value) return
  const { data } = await axios.get(`/api/profesor/grupos/${selGrupo.value.id}/alumnos`)
  alumnos.value = data
  const actual = alumnos.value.find(a => Number(a.es_delegado) === 1)
  seleccionado.value = actual?.id || null
}

async function guardar(){
  if(!selGrupo.value || !seleccionado.value) return
  try{
    await axios.post(`/api/profesor/grupos/${selGrupo.value.id}/delegado`, { alumno_id: seleccionado.value })
    msg.value = 'Delegado actualizado'
    await loadAlumnos()
  }catch(e){ error.value = e?.response?.data?.message || e.message }
}

async function revocar(){
  if(!selGrupo.value) return
  try{
    await axios.post(`/api/profesor/grupos/${selGrupo.value.id}/delegado/revocar`)
    msg.value = 'Delegado revocado'
    await loadAlumnos()
  }catch(e){ error.value = e?.response?.data?.message || e.message }
}

onMounted(loadSecciones)

const header = computed(() =>
  selSeccion.value
    ? `${selSeccion.value.codigo} — ${selSeccion.value.curso} • Sección ${selSeccion.value.seccion}`
    : 'Seleccione sección'
)
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold">Gestión de Delegados</h1>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

      <div class="grid md:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border shadow-sm p-3">
          <div class="text-sm text-slate-500 mb-2">Secciones que dictas</div>
          <ul class="divide-y">
            <li v-for="s in secciones" :key="s.seccion_id" class="py-2">
              <button class="text-left w-full" @click="selSeccion=s; loadGrupos()">
                <div class="font-medium">{{ s.codigo }} — {{ s.curso }}</div>
                <div class="text-slate-500 text-sm">Sección: {{ s.seccion }}</div>
              </button>
            </li>
          </ul>
        </div>

        <div class="rounded-2xl bg-white border shadow-sm p-3">
          <div class="text-sm text-slate-500 mb-2">Grupos</div>
          <div v-if="!grupos.length" class="text-slate-500">Sin grupos</div>
          <ul v-else class="divide-y">
            <li v-for="g in grupos" :key="g.id" class="py-2">
              <button class="text-left w-full" @click="selGrupo=g; loadAlumnos()">
                <div class="font-medium">{{ g.nombre ?? ('Grupo #' + g.id) }}</div>
              </button>
            </li>
          </ul>
        </div>

        <div class="rounded-2xl bg-white border shadow-sm p-4 md:col-span-1 md:col-start-3">
          <div class="font-medium mb-2">{{ header }}</div>
          <div v-if="!selGrupo" class="text-slate-500">Elige un grupo…</div>

          <div v-else>
            <div class="text-sm text-slate-500 mb-2">Alumnos del grupo</div>
            <div v-if="!alumnos.length" class="text-slate-500">Sin alumnos</div>

            <ul v-else class="space-y-2">
              <li v-for="a in alumnos" :key="a.id" class="flex items-center justify-between border-t py-2 first:border-0">
                <label class="flex items-center gap-2">
                  <input type="radio" :value="a.id" v-model="seleccionado" />
                  <span>{{ a.name }}</span>
                </label>
                <span class="text-slate-500 text-sm">{{ a.email }}</span>
              </li>
            </ul>

            <div class="mt-3 flex gap-2">
              <button class="rounded bg-indigo-600 px-3 py-2 text-white" @click="guardar" :disabled="!seleccionado">
                Guardar delegado
              </button>
              <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="revocar">
                Revocar
              </button>
            </div>

            <div v-if="msg" class="mt-3 text-emerald-700 text-sm">{{ msg }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
