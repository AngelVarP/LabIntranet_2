<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
const page = usePage()
const user  = computed(() => page.props.auth?.user ?? null)
const roles = computed(() => user.value?.roles ?? []) // vienen como ['admin','tecnico',...]

const items = [
  { label: 'Dashboard', to: '/dashboard', roles: ['admin','profesor','tecnico'] },
  { label: 'Tablón',    to: '/tablon',    roles: ['admin','profesor','tecnico','alumno'] },
  { label: 'Insumos',   to: '/insumos',   roles: ['admin','tecnico'] },
  { label: 'Notificaciones', to: '/notificaciones', roles: ['admin','profesor','tecnico','alumno'] },
  { label: 'Académico', to: '/academico', roles: ['admin','profesor'] },
  { label: 'Préstamos', to: '/prestamos', roles: ['admin','tecnico'] },
  { label: 'Reportes', to: '/reportes', roles: ['admin','profesor','tecnico'] },

]
const menu = computed(() => roles.value?.length ? items.filter(i => i.roles.some(r => roles.value.includes(r))) : items)
const isActive = (to) => page.url.startsWith(to)
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <!-- TOPBAR -->
    <header class="sticky top-0 z-40 border-b bg-white/80 backdrop-blur">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <img src="/logoLab.png" class="h-8 w-8" alt="Labintranet" />
          <span class="font-semibold text-slate-800">
            <span class="text-indigo-700">Lab</span><span class="text-orange-500">intranet</span>
          </span>
        </div>
        <div class="text-sm text-slate-600">
          <span v-if="user">{{ user.name }}</span>
        </div>
      </div>
    </header>

    <!-- BODY -->
    <div class="mx-auto grid max-w-7xl gap-6 p-4 md:grid-cols-[220px_1fr]">
      <!-- SIDEBAR -->
      <aside class="rounded-2xl bg-white p-3 shadow-sm">
        <nav class="space-y-1">
          <Link v-for="i in menu" :key="i.to" :href="i.to"
                class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100"
                :class="isActive(i.to) ? 'bg-slate-100 font-medium text-slate-900' : ''">
            {{ i.label }}
          </Link>
        </nav>
      </aside>

      <!-- PAGE -->
      <main class="min-h-[65vh]">
        <slot />
      </main>
    </div>
  </div>
</template>
