// backend_laravel/resources/js/app.js
import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'          // <- usa alias, no vendor/...
import { createPinia } from 'pinia'
import Alpine from 'alpinejs'

window.Alpine = Alpine

// --- helper: asegurar cookie CSRF de Sanctum antes de hacer fetch autenticados
async function ensureCsrf() {
  try {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' })
  } catch (e) {
    console.warn('CSRF cookie fail:', e)
  }
}

// --- componente Alpine para el Tablón (usado en la vista Blade)
Alpine.data('tablonPage', () => ({
  q: '',
  estado: '',
  estados: ['BORRADOR','PENDIENTE','APROBADO','RECHAZADO','PREPARADO','ENTREGADO','CERRADO'],
  rows: [],
  loading: false,

  async load(pageUrl = null) {
    this.loading = true
    try {
      const url = new URL(pageUrl ?? '/api/tablon', window.location.origin)
      if (this.q) url.searchParams.set('q', this.q)
      if (this.estado) url.searchParams.set('estado', this.estado)
      url.searchParams.set('per_page', 20)

      const res = await fetch(url, { credentials: 'include' }) // Sanctum stateful
      if (!res.ok) throw new Error('HTTP ' + res.status)
      const data = await res.json()
      this.rows = data.data ?? data
    } catch (e) {
      console.error(e)
      this.rows = []
    } finally {
      this.loading = false
    }
  },

  badge(est) {
    const map = {
      BORRADOR:'bg-slate-400', PENDIENTE:'bg-amber-500', APROBADO:'bg-blue-600',
      RECHAZADO:'bg-rose-600', PREPARADO:'bg-indigo-600',
      ENTREGADO:'bg-emerald-600', CERRADO:'bg-slate-700'
    }
    return map[est] ?? 'bg-slate-500'
  },

  async init() {
    await ensureCsrf()
    this.load()
  }
}))

Alpine.start()

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Si todavía no usas páginas Inertia, puedes dejar esto tal cual. No molesta.
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      

    app.mount(el)
  },
  progress: { color: '#4B5563' },
})
