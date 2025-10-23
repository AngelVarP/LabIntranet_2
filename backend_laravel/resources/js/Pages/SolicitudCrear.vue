<script setup>
import { ref } from 'vue'
import axios from 'axios'

const grupoId = 1 // cambia al grupo real del usuario
const practicai = ref(1)
const laboratorioi = ref(1)
const prioridad = ref('MEDIA')
const observaciones = ref('')
const items = ref([{ tipo_item:'INSUMO', item_id:1, cantidad_solic:1, unidad:'u' }])
const loading = ref(false)
const msg = ref(null)
const err = ref(null)

function addItem(){ items.value.push({ tipo_item:'INSUMO', item_id:1, cantidad_solic:1, unidad:'u' }) }
function delItem(i){ items.value.splice(i,1) }

async function submit(){
  loading.value = true; msg.value=null; err.value=null
  try{
    const { data } = await axios.post(`/api/grupos/${grupoId}/solicitudes`,{
      practica_id: practicai.value,
      laboratorio_id: laboratorioi.value,
      prioridad: prioridad.value,
      observaciones: observaciones.value,
      items: items.value
    })
    msg.value = `Creada #${data.id}`
  }catch(e){
    err.value = e?.response?.data || e.message
  }finally{ loading.value=false }
}
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Crear Solicitud</h1>

    <div class="grid gap-3 max-w-xl">
      <label class="block">Práctica ID
        <input class="border p-2 w-full" type="number" v-model.number="practicai"/>
      </label>
      <label class="block">Laboratorio ID
        <input class="border p-2 w-full" type="number" v-model.number="laboratorioi"/>
      </label>
      <label class="block">Prioridad
        <select class="border p-2 w-full" v-model="prioridad">
          <option>ALTA</option><option selected>MEDIA</option><option>BAJA</option>
        </select>
      </label>
      <label class="block">Observaciones
        <textarea class="border p-2 w-full" v-model="observaciones"></textarea>
      </label>

      <div>
        <h2 class="font-medium mb-2">Ítems</h2>
        <div v-for="(it,i) in items" :key="i" class="flex gap-2 mb-2">
          <select class="border p-2" v-model="it.tipo_item">
            <option>INSUMO</option><option>EQUIPO</option>
          </select>
          <input class="border p-2 w-24" type="number" v-model.number="it.item_id" placeholder="item_id"/>
          <input class="border p-2 w-24" type="number" v-model.number="it.cantidad_solic" placeholder="cant"/>
          <input class="border p-2 w-20" v-model="it.unidad" placeholder="u"/>
          <button class="border px-2" @click="delItem(i)" type="button">-</button>
        </div>
        <button class="border px-2" @click="addItem" type="button">+ Añadir ítem</button>
      </div>

      <div class="flex items-center gap-3">
        <button class="border px-3 py-1" :disabled="loading" @click="submit">Guardar</button>
        <span v-if="loading">Guardando…</span>
        <span v-if="msg" class="text-green-700">{{ msg }}</span>
        <span v-if="err" class="text-red-600">{{ err }}</span>
      </div>
    </div>
  </div>
</template>
