<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import AppShell from '../Layouts/AppShell.vue'
import Autocomplete from '../Components/ui/Autocomplete.vue'
import { showToast } from '../Components/ui/useToast.js'

axios.defaults.withCredentials = true
async function ensureCsrf(){ try{ await axios.get('/sanctum/csrf-cookie') }catch{} }

const look = reactive({ cursos:[], practicas:[], labs:[] })
const form = reactive({
  practica_id: null,
  laboratorio_id: null,
  prioridad: 'MEDIA',
  observaciones: '',
  items: [ { tipo_item:'INSUMO', item_id:null, cantidad_solic:1, unidad:'' } ],
})
const loading = ref(false)

async function loadLookups(){
  try{
    const [labs, cursos] = await Promise.all([
      axios.get('/api/lookups/laboratorios'),
      axios.get('/api/lookups/cursos')
    ])
    look.labs   = labs.data?.data ?? labs.data ?? []
    look.cursos = cursos.data?.data ?? cursos.data ?? []
  }catch{}
}

// Si quieres listar prácticas por curso:
const cursoId = ref('')
async function loadPracticas(){
  if(!cursoId.value){ look.practicas=[]; return }
  try{
    const { data } = await axios.get('/api/practicas', { params:{ curso_id: cursoId.value } })
    look.practicas = data?.data ?? data ?? []
  }catch{ look.practicas=[] }
}

function addItem(){ form.items.push({ tipo_item:'INSUMO', item_id:null, cantidad_solic:1, unidad:'' }) }
function delItem(i){ form.items.splice(i,1) }

function onSelectItem(idx, obj){
  // INSUMO o EQUIPO según selección; solo guardamos el id
  form.items[idx].item_id = obj?.id ?? null
  // si el endpoint trae unidad sugerida:
  if (obj?.unidad && !form.items[idx].unidad) form.items[idx].unidad = obj.unidad
}

async function submit(){
  if(!form.practica_id || !form.laboratorio_id || !form.items.length) return showToast('Complete los campos','error')
  if(form.items.some(it => !it.item_id)) return showToast('Faltan ítems','error')

  loading.value = true
  await ensureCsrf()
  try{
    await axios.post('/api/solicitudes', form)
    showToast('Solicitud creada')
    // reset
    cursoId.value = ''
    Object.assign(form, {
      practica_id:null, laboratorio_id:null, prioridad:'MEDIA', observaciones:'',
      items:[{ tipo_item:'INSUMO', item_id:null, cantidad_solic:1, unidad:'' }]
    })
    look.practicas = []
  }catch(e){
    showToast(e?.response?.data?.message || 'Error al guardar','error')
  }finally{
    loading.value=false
  }
}

onMounted(() => { loadLookups() })
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-semibold text-slate-800">Nueva Solicitud</h1>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm">
          <label class="block text-sm">Curso
            <select v-model="cursoId" class="mt-1 w-full rounded border p-2" @change="loadPracticas">
              <option value="">Seleccione…</option>
              <option v-for="c in look.cursos" :key="c.id" :value="c.id">
                {{ c.codigo }} — {{ c.nombre }}
              </option>
            </select>
          </label>
          <label class="block text-sm">Práctica
            <select v-model="form.practica_id" class="mt-1 w-full rounded border p-2">
              <option :value="null">Seleccione…</option>
              <option v-for="p in look.practicas" :key="p.id" :value="p.id">{{ p.nombre }}</option>
            </select>
          </label>
          <label class="block text-sm">Laboratorio
            <select v-model="form.laboratorio_id" class="mt-1 w-full rounded border p-2">
              <option :value="null">Seleccione…</option>
              <option v-for="l in look.labs" :key="l.id" :value="l.id">{{ l.nombre }}</option>
            </select>
          </label>
          <label class="block text-sm">Prioridad
            <select v-model="form.prioridad" class="mt-1 w-full rounded border p-2">
              <option>ALTA</option><option>MEDIA</option><option>BAJA</option>
            </select>
          </label>
          <label class="block text-sm">Observaciones
            <textarea v-model.trim="form.observaciones" class="mt-1 w-full rounded border p-2" rows="3"></textarea>
          </label>
        </div>

        <div class="space-y-3 rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-2 font-medium">Ítems</div>

          <div v-for="(it,i) in form.items" :key="i" class="grid grid-cols-12 items-start gap-2">
            <select v-model="it.tipo_item" class="col-span-3 rounded border p-2">
              <option>INSUMO</option>
              <option>EQUIPO</option>
            </select>

            <div class="col-span-5">
              <Autocomplete
                :fetch-url="it.tipo_item==='EQUIPO' ? '/api/lookups/equipos/buscar' : '/api/lookups/insumos/buscar'"
                :placeholder="it.tipo_item==='EQUIPO' ? 'Buscar equipo…' : 'Buscar insumo…'"
                @select="(obj)=>onSelectItem(i,obj)"
              />
            </div>

            <input v-model.number="it.cantidad_solic" class="col-span-2 rounded border p-2" type="number" min="0" step="0.01" placeholder="cant." />
            <input v-model.trim="it.unidad" class="col-span-1 rounded border p-2" placeholder="u" />
            <button class="col-span-1 rounded border px-2 py-2 hover:bg-slate-50" @click="delItem(i)" type="button">-</button>
          </div>

          <button class="rounded border px-3 py-2 hover:bg-slate-50" @click="addItem" type="button">
            + Añadir ítem
          </button>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button class="rounded bg-emerald-600 px-4 py-2 text-white" :disabled="loading" @click="submit">
          {{ loading ? 'Guardando…' : 'Guardar' }}
        </button>
        <Link href="/tablon" class="rounded border px-3 py-2 hover:bg-slate-50">Volver al Tablón</Link>
      </div>
    </div>
  </AppShell>
</template>
