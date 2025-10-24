<script setup>
const props = defineProps({ show: Boolean, title: String })
const emit = defineEmits(['close'])
</script>

<template>
  <teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50">
      <div class="absolute inset-0 bg-black/40" @click="emit('close')" />
      <div class="absolute inset-0 grid place-items-center p-4">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-lg">
          <div class="flex items-center justify-between border-b px-4 py-3">
            <h3 class="font-semibold">{{ title }}</h3>
            <button class="rounded px-2 py-1 hover:bg-slate-100" @click="emit('close')">✕</button>
          </div>
          <div class="p-4">
            <slot />
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>
