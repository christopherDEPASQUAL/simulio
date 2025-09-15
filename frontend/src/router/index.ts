// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '@/stores/auth'

const routes = [
  { path: '/', redirect: '/simulate' },
  { path: '/simulate', component: () => import('@/pages/Simulate.vue'), meta:{ requiresAuth:true } },
  { path: '/history',  component: () => import('@/pages/History.vue'),  meta:{ requiresAuth:true } },
  { path: '/login',    component: () => import('@/views/auth/Login.vue') },
  { path: '/register', component: () => import('@/views/auth/Register.vue') },
  { path: '/:pathMatch(.*)*', redirect: '/simulate' },
]

const router = createRouter({ history: createWebHistory(), routes })
router.beforeEach((to) => {
  const auth = useAuth()
  if (to.meta?.requiresAuth && !auth.isAuthenticated)
    return { path: '/login', query: { redirect: to.fullPath } }
})
export default router

