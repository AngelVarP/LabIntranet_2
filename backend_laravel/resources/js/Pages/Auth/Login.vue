<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})
const show = ref(false)

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <div class="min-h-screen grid md:grid-cols-2">
    <div class="hidden md:block bg-gradient-to-br from-indigo-600 to-violet-600"></div>

    <div class="flex items-center justify-center p-6">
      <div class="w-full max-w-md space-y-6">
        <div class="text-center">
          <div class="text-2xl font-semibold">
            <span class="text-indigo-700">Lab</span><span class="text-orange-500">Intranet</span>
          </div>
          <p class="text-slate-500 mt-1">Inicia sesión para continuar</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4 bg-white rounded-2xl p-6 shadow-sm border">
          <div>
            <label class="text-sm text-slate-600">Correo</label>
            <input v-model="form.email" type="email" autocomplete="email"
              class="mt-1 w-full rounded border p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</div>
          </div>

          <div>
            <label class="text-sm text-slate-600">Contraseña</label>
            <div class="mt-1 relative">
              <input :type="show ? 'text' : 'password'" v-model="form.password" autocomplete="current-password"
                class="w-full rounded border p-2 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <button type="button" @click="show=!show"
                class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-700">
                {{ show ? '🙈' : '👁️' }}
              </button>
            </div>
            <div v-if="form.errors.password" class="text-xs text-rose-600 mt-1">{{ form.errors.password }}</div>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
              <input type="checkbox" v-model="form.remember" class="rounded border-slate-300" />
              Recuérdame
            </label>
            <Link href="/forgot-password" class="text-sm text-indigo-600 hover:underline">
              ¿Olvidaste tu contraseña?
            </Link>
          </div>

          <button :disabled="form.processing"
            class="w-full rounded bg-indigo-600 py-2 text-white hover:bg-indigo-700 disabled:opacity-60">
            {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500">
          ¿Sin cuenta? <Link href="/register" class="text-indigo-600 hover:underline">Registrarse</Link>
        </p>
      </div>
    </div>
  </div>
</template>
