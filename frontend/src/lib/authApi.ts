// frontend/src/lib/authApi.ts
type AuthResponse = { token: string; user: { first_name:string; last_name:string; email:string } }

const BASE = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/+$/, '');
const api  = (p: string) => `${BASE}/${p.replace(/^\/+/, '')}`;

export const login = async (email: string, password: string) => {
  const r = await fetch(api('/api/auth/login'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  });
  if (!r.ok) throw new Error(`${r.status} ${r.statusText}`);
  return r.json() as Promise<AuthResponse>;
}

export const register = async (payload: { first_name:string; last_name:string; email:string; password:string }) => {
  const r = await fetch(api('/api/auth/register'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!r.ok) throw new Error(`${r.status} ${r.statusText}`);
  return r.json() as Promise<AuthResponse>;
}
