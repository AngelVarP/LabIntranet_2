<script setup>
import { onMounted, computed } from 'vue'
import { useAuth } from '@/stores/auth'

const auth = useAuth()

onMounted(() => auth.fetch())

// Menú base (puedes ampliar cuando agregues más pantallas)
const items = [
  { label: 'Dashboard', to: '/dashboard', roles: ['admin','profesor','tecnico'] },
  { label: 'Tablón',    href: '/tablon',   roles: ['admin','profesor','tecnico','alumno'] },
  { label: 'Insumos',   to: '/insumos',    roles: ['admin','tecnico'] },       // placeholder
  { label: 'Préstamos', to: '/prestamos',  roles: ['admin','tecnico'] },       // placeholder
  { label: 'Académico', to: '/academico',  roles: ['admin','profesor'] },      // placeholder
  { label: 'Reportes',  to: '/reportes',   roles: ['admin','profesor','tecnico'] },
]

const menu = computed(() =>
  items.filter(i => i.roles.some(r => auth.roles.includes(r)))
)
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Topbar -->
    <header class="h-14 border-b bg-white">
      <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <img src="/logoLab.png" class="h-8 w-8" alt="Labintranet" />
          <span class="font-semibold text-slate-800">Labintranet</span>
        </div>
        <div class="text-sm text-slate-600">
          {{ auth.user?.name }} <span class="text-slate-400">•</span>
          <span class="uppercase">{{ auth.roles.join(', ') }}</span>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div class="mx-auto grid max-w-7xl gap-6 p-4 md:grid-cols-[220px_1fr]">
      <!-- Sidebar -->
      <aside class="rounded-xl bg-white p-3 shadow-sm">
        <nav class="space-y-1">
          <template v-for="(i,idx) in menu" :key="idx">
            <a v-if="i.href"
               :href="i.href"
               class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">
              {{ i.label }}
            </a>
            <inertia-link v-else
               :href="i.to"
               class="block rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">
              {{ i.label }}
            </inertia-link>
          </template>
        </nav>
      </aside>

      <!-- Page -->
      <main class="min-h-[60vh]">
        <slot />
      </main>
    </div>
  </div>
</template>
