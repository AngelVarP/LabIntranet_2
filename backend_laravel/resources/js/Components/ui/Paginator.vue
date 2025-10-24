<script setup>
import { computed } from 'vue'


const props = defineProps({
meta: { type: Object, default: null },
modelValue: { type: Number, default: 1 },
})
const emit = defineEmits(['update:modelValue','change'])


const current = computed(() => props.meta?.current_page ?? props.modelValue ?? 1)
const last = computed(() => props.meta?.last_page ?? 1)


function go (p) {
const page = Math.min(Math.max(1, p), last.value)
emit('update:modelValue', page)
emit('change', page)
}


const nums = computed(() => {
const L = last.value
if (L <= 7) return Array.from({length:L}, (_,i)=>i+1)
const c = current.value
const set = new Set([1,2,L-1,L,c-1,c,c+1])
return Array.from(set).filter(n => n>=1 && n<=L).sort((a,b)=>a-b)
})
</script>


<template>
<div class="flex items-center gap-1 text-sm">
<button class="rounded border px-2 py-1" :disabled="current<=1" @click="go(current-1)">Anterior</button>
<template v-for="(n,idx) in nums" :key="n">
<span v-if="idx>0 && n-nums[idx-1] > 1" class="px-1">…</span>
<button class="rounded px-2 py-1"
:class="n===current ? 'bg-slate-900 text-white' : 'border hover:bg-slate-50'"
@click="go(n)">{{ n }}</button>
</template>
<button class="rounded border px-2 py-1" :disabled="current>=last" @click="go(current+1)">Siguiente</button>
</div>
</template>