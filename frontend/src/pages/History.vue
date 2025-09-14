<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api, type HistoryItem } from '@/lib/apiClients'
import { eur } from '@/lib/formats'

const headers = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Client', key: 'client_email', sortable: true },
  { title: 'Date', key: 'created_at', sortable: true },
  { title: 'Mensualité', key: 'monthly_payment_eur', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const items = ref<HistoryItem[]>([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const res = await api.history(1, 50)
    items.value = res.items
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <v-container class="py-6" fluid>
    <v-row class="mx-auto" style="max-width:1100px">
      <v-col cols="12">
        <v-card elevation="1">
          <div class="d-flex align-center justify-space-between pa-4">
            <h2 class="text-h6">Historique des simulations</h2>
            <v-btn variant="tonal" :loading="loading" @click="load">Rafraîchir</v-btn>
          </div>

          <v-data-table
            :headers="headers"
            :items="items"
            :loading="loading"
            items-per-page="10"
          >
            <template #item.created_at="{ item }">
              {{ new Date(item.created_at).toLocaleString('fr-FR') }}
            </template>

            <template #item.monthly_payment_eur="{ item }">
              {{ eur(item.monthly_payment_eur) }}
            </template>

            <template #item.actions="{ item }">
              <v-btn size="small" variant="tonal" color="primary"
                    :href="`/api/simulations/${item.id}/pdf`" target="_blank" rel="noopener">
                PDF
              </v-btn>
            </template>

            <template #no-data>
              <div class="pa-6 text-medium-emphasis">Aucune simulation pour l’instant.</div>
            </template>

            <template #loading>
              <v-skeleton-loader type="table-row@5"></v-skeleton-loader>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
