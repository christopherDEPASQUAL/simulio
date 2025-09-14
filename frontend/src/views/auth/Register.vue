<template>
  <v-card class="mx-auto" style="max-width:420px">
    <v-card-title>Connexion</v-card-title>
    <v-card-text>
      <v-text-field v-model="email" label="Email" type="email" />
      <v-text-field v-model="password" label="Mot de passe" type="password" />
      <v-btn :loading="loading" class="mt-2" block color="primary" @click="onLogin">Se connecter</v-btn>
      <v-alert v-if="err" type="error" variant="tonal" class="mt-3">{{ err }}</v-alert>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/stores/auth'

const router = useRouter()
const email = ref(''); const password = ref('')
const err = ref(''); const loading = ref(false)
const auth = useAuth()

async function onLogin(){
  try {
    loading.value = true; err.value=''
    await auth.login(email.value, password.value)
    // Bonus: préremplir le formulaire simulateur
    if (auth.user) {
      localStorage.setItem('simulio:client', JSON.stringify(auth.user))
    }
    router.push('/')
  } catch (e:any) {
    err.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
