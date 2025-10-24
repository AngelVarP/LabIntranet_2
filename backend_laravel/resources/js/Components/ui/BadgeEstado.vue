<script setup>
const props = defineProps({ estado: { type: String, default: '' } })
function cls(e){
  const m = {
    BORRADOR:'bg-slate-400', PENDIENTE:'bg-amber-500', APROBADO:'bg-blue-600',
    RECHAZADO:'bg-rose-600', PREPARADO:'bg-indigo-600',
    ENTREGADO:'bg-emerald-600', CERRADO:'bg-slate-700'
  }
  return (m[(e||'').toUpperCase()] ?? 'bg-slate-500') + ' text-white rounded px-2 py-0.5 text-xs'
}
</script>

<template>
  <span :class="cls(estado)">{{ estado || '—' }}</span>
</template>
