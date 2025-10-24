<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen grid place-items-center bg-slate-100">
    <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
      <div class="mb-3 text-xl font-semibold">
        <span class="text-indigo-700">Lab</span><span class="text-orange-500">Intranet</span>
      </div>
      <p class="text-slate-600 mb-4">Bienvenido. Inicia sesión para continuar.</p>
      <Link href="/login" class="rounded bg-indigo-600 px-4 py-2 text-white">Iniciar sesión</Link>
    </div>
  </div>
</template>