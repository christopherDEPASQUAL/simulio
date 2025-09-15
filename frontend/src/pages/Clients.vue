<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api, type Client } from '@/lib/apiClients'

const loading = ref(true)
const items = ref<Client[]>([])

const dialog = ref(false)
const form = ref<Omit<Client,'id'>>({ first_name:'', last_name:'', email:'', phone:'', address:'' })
const err = ref('')

async function load() {
  loading.value = true
  try {
    const res = await api.listClients()
    items.value = res.items
  } finally { loading.value = false }
}

async function createClient() {
  try {
    err.value = ''
    const c = await api.createClient(form.value)
    items.value.unshift(c)
    dialog.value = false
    form.value = { first_name:'', last_name:'', email:'', phone:'', address:'' }
  } catch (e:any) {
    err.value = e.message
  }
}

onMounted(load)
</script>

<template>
  <v-container class="py-6" fluid>
    <v-row class="mx-auto" style="max-width:1100px">
      <v-col cols="12">
        <v-card>
          <div class="d-flex align-center justify-space-between pa-4">
            <h2 class="text-h6">Mes clients</h2>
            <v-btn color="primary" @click="dialog = true">Nouveau client</v-btn>
          </div>

          <v-data-table
            :items="items"
            :headers="[
              { title:'Nom', key:'last_name' },
              { title:'Prénom', key:'first_name' },
              { title:'Email', key:'email' },
              { title:'Téléphone', key:'phone' },
              { title:'Adresse', key:'address' },
            ]"
            :loading="loading"
            items-per-page="10"
          >
            <template #no-data>
              <div class="pa-6 text-medium-emphasis">Aucun client.</div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="520">
      <v-card>
        <v-card-title>Nouveau client</v-card-title>
        <v-card-text>
          <v-text-field v-model="form.first_name" label="Prénom" />
          <v-text-field v-model="form.last_name" label="Nom" />
          <v-text-field v-model="form.email" label="Email" type="email" />
          <v-text-field v-model="form.phone" label="Téléphone" />
          <v-text-field v-model="form.address" label="Adresse" />
          <v-alert v-if="err" type="error" variant="tonal" class="mt-2">{{ err }}</v-alert>
        </v-card-text>
        <v-card-actions class="px-4 pb-4">
          <v-spacer />
          <v-btn variant="text" @click="dialog=false">Annuler</v-btn>
          <v-btn color="primary" @click="createClient">Créer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
