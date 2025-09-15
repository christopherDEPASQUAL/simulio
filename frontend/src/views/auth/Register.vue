<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'

const first_name = ref('')
const last_name = ref('')
const email = ref('')
const password = ref('')

const loading = ref(false)
const err = ref('')

const auth = useAuth()
const route = useRoute()
const router = useRouter()

async function submit() {
  loading.value = true
  err.value = ''
  try {
    await auth.register({
      first_name: first_name.value,
      last_name: last_name.value,
      email: email.value,
      password: password.value,
    })
    const to = (route.query.redirect as string) || '/simulate'
    router.replace(to)
  } catch (e: any) {
    err.value = e.message || 'Inscription impossible'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-container class="py-10" style="max-width:420px">
    <v-card class="pa-6">
      <h1 class="text-h6 mb-4">Créer un compte</h1>

      <v-text-field v-model="first_name" label="Prénom" />
      <v-text-field v-model="last_name" label="Nom" />
      <v-text-field v-model="email" label="Email" type="email" />
      <v-text-field v-model="password" label="Mot de passe" type="password" />

      <v-alert v-if="err" type="error" variant="tonal" class="mb-3">{{ err }}</v-alert>

      <v-btn :loading="loading" color="primary" block @click="submit">
        S’inscrire
      </v-btn>

      <div class="mt-3 text-body-2">
        Déjà un compte ?
        <RouterLink :to="{ path: '/login', query: route.query }">Se connecter</RouterLink>
      </div>
    </v-card>
  </v-container>
</template>
