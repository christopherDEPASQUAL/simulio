import { defineStore } from 'pinia'
import { login, register } from '@/lib/authApi'

type User = { first_name:string; last_name:string; email:string }
type State = { token: string | null; user: User | null }

export const useAuth = defineStore('auth', {
  state: (): State => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),
  getters: { isAuthenticated: s => !!s.token },
  actions: {
    async login(email: string, password: string) {
      const { token, user } = await login(email, password)
      this.token = token; this.user = user
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },
    async register(payload: { first_name:string; last_name:string; email:string; password:string }) {
      const { token, user } = await register(payload)
      this.token = token; this.user = user
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },
    logout() {
      this.token = null; this.user = null
      localStorage.removeItem('token'); localStorage.removeItem('user')
    },
  },
})
