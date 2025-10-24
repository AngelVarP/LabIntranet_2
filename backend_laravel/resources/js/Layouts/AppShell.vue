<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user  = computed(() => page.props?.auth?.user ?? {})
const roles = computed(() => (page.props?.auth?.user?.roles ?? []).map(r => r.toLowerCase()))

const items = computed(() => {
  const all = [
    { label: 'Dashboard', to: '/dashboard', roles: ['admin','profesor','tecnico','alumno'] },
    { label: 'Tablón', to: '/tablon', roles: ['admin','tecnico','alumno'] },
    { label: 'Reportes', to: '/reportes', roles: ['admin','profesor','tecnico'] },
    { label: 'Prestamos', to: '/prestamos', roles: ['admin','tecnico'] },
    { label: 'Insumos', to: '/insumos', roles: ['admin','tecnico'] },
    { label: 'Equipos', to: '/equipos', roles: ['admin','tecnico'] },
    { label: 'Kardex', to: '/kardex', roles: ['admin','tecnico'] },
    { label: 'Solicitudes (Profesor)', to: '/profesor/solicitudes', roles: ['profesor','admin'] },
    { label: 'Mis Solicitudes', to: '/alumno/solicitudes', roles: ['alumno','admin'] },
    { label: 'Notificaciones', to: '/notificaciones', roles: ['admin','profesor','tecnico','alumno'] },
  ]
  return all.filter(it => it.roles.some(r => roles.value.includes(r)))
})
</script>

<template>
  <div class="min-h-screen bg-slate-100">
    <header class="sticky top-0 z-40 bg-white border-b">
      <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
        <div class="font-semibold">
          <span class="text-indigo-700">Lab</span><span class="text-orange-500">Intranet</span>
        </div>
        <div class="text-sm text-slate-600">
          {{ user.name ?? user.email }}
        </div>
      </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 py-6 grid gap-6 md:grid-cols-5">
      <aside class="md:col-span-1">
        <nav class="rounded-xl bg-white p-3 shadow-sm">
          <ul class="space-y-1">
            <li v-for="it in items" :key="it.to">
              <Link :href="it.to" class="block rounded px-3 py-2 hover:bg-slate-50">
                {{ it.label }}
              </Link>
            </li>
          </ul>
        </nav>
      </aside>

      <main class="md:col-span-4">
        <slot />
      </main>
    </div>
  </div>
</template>
