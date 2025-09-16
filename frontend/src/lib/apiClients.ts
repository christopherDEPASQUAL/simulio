// src/lib/apiClients.ts
import { useAuth } from '@/stores/auth'

export type Client = {
  id: number
  user_id?: number
  first_name: string
  last_name: string
  email: string
  phone: string
  address: string
}

export type SimulationInput = {
  years: number
  purchase_price: number
  down_payment: number
  works: number
  agency_fee_rate_percent: number
  notary_fee_rate_percent: number
  interest_rate_percent: number
  insurance_rate_percent: number
  appreciation_rate_percent: number
  acquisition_month: number
  acquisition_year: number
  client?: {
    first_name: string
    last_name: string
    email: string
    phone: string
    address: string
  }
  client_id?: number | null
}

export type CreatedSimulation = {
  id: number
  result: {
    monthly_payment_eur: number
    loan_amount_eur: number
    notary_fee_eur: number
    agency_fee_eur: number
    min_monthly_income_eur: number
  }
}

export type HistoryItem = {
  id: number
  created_at: string
  client_email: string | null
  monthly_payment_eur: number
}

const BASE_URL = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/+$/, '');

async function authFetch(input: RequestInfo, init: RequestInit = {}) {
  const auth = useAuth()
  const headers = new Headers(init.headers || {})
  if (auth.token) headers.set('Authorization', `Bearer ${auth.token}`)
  const res = await fetch(input, { ...init, headers })

  if (res.status === 401) {
    auth.logout()
    const redirect = encodeURIComponent(location.pathname + location.search)
    location.href = `/login?redirect=${redirect}`
    throw new Error('Session expirée (401)')
  }
  return res
}

async function json<T>(res: Response): Promise<T> {
  if (!res.ok) {
    const text = await res.text().catch(() => '')
    throw new Error(`${res.status} ${res.statusText}${text ? ` - ${text}` : ''}`)
  }
  return res.json() as Promise<T>
}

const apiUrl = (p: string) => `${BASE_URL}/${p.replace(/^\/+/, '')}`
async function ok(res: Response): Promise<void> {
  if (!res.ok) {
    const text = await res.text().catch(() => '')
    throw new Error(`${res.status} ${res.statusText}${text ? ` - ${text}` : ''}`)
  }
}


export const api = {
  simulate: async (payload: SimulationInput) =>
    json<CreatedSimulation>(await authFetch(apiUrl('/api/simulations'), {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
    })),

  history: async (page = 1, perPage = 20) =>
    json<{ items: HistoryItem[] }>(await authFetch(apiUrl(`/api/simulations?page=${page}&limit=${perPage}`))),

  listClients: async () =>
    json<{ items: Client[] }>(await authFetch(apiUrl('/api/clients'))),

  createClient: async (payload: Omit<Client, 'id'>) =>
    json<Client>(await authFetch(apiUrl('/api/clients'), {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
    })),

    searchClients: async (q = '', page = 1, perPage = 50) =>
    json<{ items: Client[] }>(
      await authFetch(apiUrl(`/api/clients?search=${encodeURIComponent(q)}&page=${page}&limit=${perPage}`), {
        // évite d’éventuels caches agressifs du navigateur/proxy
        cache: 'no-store',
      })
    ),

  updateClient: async (id: number, payload: Partial<Client>) =>
    ok(await authFetch(apiUrl(`/api/clients/${id}`), {
      method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
    })),

  deleteClient: async (id: number) =>
    ok(await authFetch(apiUrl(`/api/clients/${id}`), { method: 'DELETE' })),

  deleteSimulation: async (id: number) =>
    ok(await authFetch(apiUrl(`/api/simulations/${id}`), { method: 'DELETE' })),

  pdf: async (id: number) => {
    const res = await authFetch(apiUrl(`/api/simulations/${id}/pdf`))
    if (!res.ok) {
      const text = await res.text().catch(()=> '')
      throw new Error(`${res.status} ${res.statusText}${text ? ` - ${text}` : ''}`)
    }
    return res.blob()
  },
}
