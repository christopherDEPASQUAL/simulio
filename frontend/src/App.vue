<template>
  <v-app>
    <v-app-bar flat color="surface" class="border-b">
      <v-app-bar-title class="font-weight-medium">Simulio</v-app-bar-title>

      <v-spacer />

      <v-btn to="/" variant="text">Simulation</v-btn>
      <v-btn to="/history" variant="text">Historique</v-btn>
      <v-btn to="/login" variant="text">Connexion</v-btn>
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
