<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
const toasts = ref([]); let id = 0
function onShow(e){ const t={id:++id, message:e.detail.message||'', type:e.detail.type||'info'}; toasts.value.push(t); setTimeout(()=>dismiss(t.id), e.detail.ms||3000) }
function dismiss(id){ toasts.value = toasts.value.filter(t => t.id!==id) }
onMounted(()=>window.addEventListener('toast:show', onShow))
onBeforeUnmount(()=>window.removeEventListener('toast:show', onShow))
</script>

<template>
  <div class="pointer-events-none fixed bottom-4 right-4 z-50 grid gap-2">
    <div v-for="t in toasts" :key="t.id" class="pointer-events-auto flex items-start gap-2 rounded-xl border bg-white p-3 shadow-sm min-w-[240px]">
      <span v-if="t.type==='success'">✅</span>
      <span v-else-if="t.type==='error'">❌</span>
      <span v-else>ℹ️</span>
      <div class="text-sm text-slate-800">{{ t.message }}</div>
      <button class="ml-auto text-slate-400 hover:text-slate-700" @click="dismiss(t.id)">✕</button>
    </div>
  </div>
</template>
