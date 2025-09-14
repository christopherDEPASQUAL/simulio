import { useAuth } from '@/stores/auth'

async function authFetch(input: RequestInfo, init: RequestInit = {}) {
  const auth = useAuth()
  const headers = new Headers(init.headers || {})
  if (auth.token) headers.set('Authorization', `Bearer ${auth.token}`)
  return fetch(input, { ...init, headers })
}

export type SimulationInput = { /* … inchangé … */ }
export type CreatedSimulation = { /* … inchangé … */ }
export type HistoryItem = { /* … inchangé … */ }

const BASE = '' // /api via proxy Vite

async function json<T>(res: Response): Promise<T> {
  if (!res.ok) {
    const text = await res.text().catch(() => '')
    throw new Error(`${res.status} ${res.statusText}${text ? ` - ${text}` : ''}`)
  }
  return res.json() as Promise<T>
}

export const api = {
  simulate: async (payload: SimulationInput) =>
    json<CreatedSimulation>(await authFetch(`${BASE}/api/simulations`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
    })),
  history: async (page=1, perPage=20) =>
    json<{ items: HistoryItem[] }>(await authFetch(`${BASE}/api/simulations?page=${page}&per_page=${perPage}`)),
}

type AuthResponse = { token: string; user: { first_name:string; last_name:string; email:string } }

export const authApi = {
  login: async (email: string, password: string) => {
    const r = await fetch('/api/auth/login', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })
    if (!r.ok) throw new Error(`${r.status} ${r.statusText}`)
    return r.json() as Promise<AuthResponse>
  },
  register: async (payload: { first_name:string; last_name:string; email:string; password:string }) => {
    const r = await fetch('/api/auth/register', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    if (!r.ok) throw new Error(`${r.status} ${r.statusText}`)
    return r.json() as Promise<AuthResponse>
  },
}
