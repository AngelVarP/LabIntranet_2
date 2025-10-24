import { defineStore } from 'pinia'

export const useAuth = defineStore('auth', {
  state: () => ({ user: null, roles: [] }),
  getters: {
    is: (state) => (role) => state.roles.includes(role),
    canAny: (state) => (arr) => arr.some(r => state.roles.includes(r)),
  },
  actions: {
    async fetch() {
      try {
        const res = await fetch('/api/me', { credentials: 'include' })
        if (!res.ok) return
        const data = await res.json()
        this.user  = { id: data.id, name: data.name, email: data.email }
        this.roles = data.roles || []
      } catch (e) { console.error(e) }
    }
  }
})
