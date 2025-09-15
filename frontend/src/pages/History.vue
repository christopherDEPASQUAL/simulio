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
const downloadingId = ref<number|null>(null)

async function load() {
  loading.value = true
  try { items.value = (await api.history(1, 50)).items }
  finally { loading.value = false }
}
async function downloadPdf(id:number) {
  try {
    downloadingId.value = id
    const blob = await api.pdf(id)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `simulation-${id}.pdf`
    document.body.appendChild(a); a.click(); a.remove()
    URL.revokeObjectURL(url)
  } finally {
    downloadingId.value = null
  }
}
onMounted(load)
</script>

<template>
  <!-- ... -->
  <v-data-table :headers="headers" :items="items" :loading="loading" items-per-page="10">
    <template #item.created_at="{ item }">
      {{ new Date(item.created_at).toLocaleString('fr-FR') }}
    </template>
    <template #item.monthly_payment_eur="{ item }">
      {{ eur(item.monthly_payment_eur) }}
    </template>
    <template #item.actions="{ item }">
      <v-btn size="small" variant="tonal" color="primary"
            :loading="downloadingId === item.id"
            @click="downloadPdf(item.id)">
        PDF
      </v-btn>
    </template>
  </v-data-table>
</template>
