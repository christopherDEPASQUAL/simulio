<!-- <template>
  <v-card class="mx-auto" style="max-width:420px">
    <v-card-title>Connexion</v-card-title>
    <v-card-text>
      <v-text-field v-model="email" label="Email" type="email" />
      <v-text-field v-model="password" label="Mot de passe" type="password" />
      <v-btn :loading="loading" class="mt-2" block color="primary" @click="onLogin">Se connecter</v-btn>
      <v-alert v-if="err" type="error" variant="tonal" class="mt-3">{{ err }}</v-alert>
    </v-card-text>
  </v-card>
</template> -->

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/stores/auth'
import { useRoute, useRouter } from 'vue-router'

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
    await auth.login(email.value, password.value)
    // redirige vers la page demandée ou /simulate par défaut
    const to = (route.query.redirect as string) || '/simulate'
    router.replace(to)
  } catch (e: any) {
    err.value = e.message || 'Connexion impossible'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-container class="py-10" style="max-width:420px">
    <v-card class="pa-6">
      <h1 class="text-h6 mb-4">Connexion</h1>
      <v-text-field v-model="email" label="Email" type="email" />
      <v-text-field v-model="password" label="Mot de passe" type="password" />
      <v-alert v-if="err" type="error" variant="tonal" class="mb-3">{{ err }}</v-alert>
      <v-btn :loading="loading" color="primary" block @click="submit">Se connecter</v-btn>
    </v-card>
  </v-container>
</template>
