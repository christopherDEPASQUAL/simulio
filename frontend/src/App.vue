<script setup lang="ts">
import { useAuth } from '@/stores/auth'
const auth = useAuth()
const logout = () => auth.logout()
</script>

<template>
  <v-app>
    <v-app-bar flat color="surface" class="border-b">
      <v-app-bar-title class="font-weight-medium">Simulio</v-app-bar-title>

      <v-spacer />

      
      <template v-if="auth.isAuthenticated">
        <v-btn to="/simulate" variant="text">Simulation</v-btn>
        <v-btn to="/history"  variant="text">Historique</v-btn>
        <v-btn to="/clients" variant="text">Clients</v-btn>
        <span class="mx-3 text-medium-emphasis">
          {{ auth.user?.first_name }} {{ auth.user?.last_name }}
        </span>
        <v-btn variant="text" @click="logout">Déconnexion</v-btn>
      </template>
      <template v-else>
        <v-btn to="/login" variant="text">Connexion</v-btn>
        <v-btn to="/register" variant="text">Inscription</v-btn>
      </template>
    </v-app-bar>

    <v-main>
      <v-container fluid class="py-6">
        <div class="mx-auto" style="max-width:1100px">
          <router-view />
        </div>
      </v-container>
    </v-main>
  </v-app>
</template>



