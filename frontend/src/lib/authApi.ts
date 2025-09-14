type AuthResponse = { token: string; user: { first_name:string; last_name:string; email:string } }

export const login = async (email: string, password: string) => {
  const r = await fetch('/api/auth/login', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  })
  if (!r.ok) throw new Error(`${r.status} ${r.statusText}`)
  return r.json() as Promise<AuthResponse>
}

export const register = async (payload: { first_name:string; last_name:string; email:string; password:string }) => {
  const r = await fetch('/api/auth/register', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })
  if (!r.ok) throw new Error(`${r.status} ${r.statusText}`)
  return r.json() as Promise<AuthResponse>
}
