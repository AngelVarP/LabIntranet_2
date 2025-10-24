<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  fetchUrl: { type: String, required: true }, // ej: '/api/lookups/equipos/buscar'
  placeholder: { type: String, default: 'Buscar…' },
  minChars: { type: Number, default: 2 },
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['select'])

const q = ref('')
const open = ref(false)
const loading = ref(false)
const results = ref([])
let aborter = null
let tid = null

async function fetchData() {
  results.value = []
  if ((q.value || '').trim().length < props.minChars) { open.value = false; return }
  loading.value = true
  aborter?.abort?.()
  aborter = new AbortController()
  try {
    const url = new URL(props.fetchUrl, window.location.origin)
    url.searchParams.set('q', q.value.trim())
    const res = await fetch(url, { credentials: 'include', signal: aborter.signal })
    if (!res.ok) throw new Error('HTTP ' + res.status)
    const data = await res.json()
    results.value = Array.isArray(data) ? data : (data?.data ?? [])
    open.value = true
  } catch { /* noop */ }
  finally { loading.value = false }
}

watch(q, () => {
  clearTimeout(tid)
  tid = setTimeout(fetchData, 300)
})

function selectItem(item) {
  emit('select', item)
  q.value = display(item)
  open.value = false
}

function display(item) {
  if (!item) return ''
  return item.nombre
    ? `${item.nombre}${item.codigo ? ' · ' + item.codigo : ''}`
    : (item.codigo || '')
}

function onBlur() {
  setTimeout(()=> { open.value = false }, 100)
}
onBeforeUnmount(()=> clearTimeout(tid))
</script>

<template>
  <div class="relative">
    <input
      v-model="q"
      :placeholder="placeholder"
      :disabled="disabled"
      class="w-full rounded border p-2"
      @focus="fetchData"
      @blur="onBlur"
    />
    <div v-if="open" class="absolute z-30 mt-1 max-h-64 w-full overflow-auto rounded border bg-white shadow">
      <div v-if="loading" class="p-2 text-sm text-slate-500">Buscando…</div>
      <template v-else>
        <button
          v-for="r in results" :key="r.id"
          class="block w-full text-left px-3 py-2 hover:bg-slate-50"
          @click="selectItem(r)"
        >
          <div class="font-medium">{{ r.nombre ?? r.titulo ?? r.codigo }}</div>
          <div class="text-xs text-slate-500" v-if="r.codigo">{{ r.codigo }}</div>
        </button>
        <div v-if="!results.length" class="p-2 text-sm text-slate-500">Sin resultados</div>
      </template>
    </div>
  </div>
</template>
