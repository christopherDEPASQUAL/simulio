import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [
    vue(),
    vuetify({ autoImport: true }),
  ],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
  },
  server: {
    proxy: {
      // IMPORTANT : mets ici le nom de service Docker du backend
      // D’après tes logs, c’est probablement "backend_php".
      // Si ton service s’appelle différemment (ex: backend-php), change la valeur.
      '/api': {
        target: 'http://backend-php:8000',
        changeOrigin: true,
      },
    },
  },
})
