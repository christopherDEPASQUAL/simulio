<!-- src/pages/Simulate.vue -->
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { api, type SimulationInput, type CreatedSimulation, type Client } from '@/lib/apiClients'
import { eur } from '@/lib/formats'

const loading = ref(false)
const result = ref<CreatedSimulation['result'] | null>(null)
const createdId = ref<number | null>(null)

const clients = ref<Client[]>([])
const selectedClientId = ref<number | null>(null)

const form = ref<SimulationInput>({
  years: 25,
  purchase_price: 834000,
  down_payment: 50000,
  works: 20000,
  agency_fee_rate_percent: 3,
  notary_fee_rate_percent: 7.5,
  interest_rate_percent: 3.5,
  insurance_rate_percent: 0.32,
  appreciation_rate_percent: 1,
  acquisition_month: 7,
  acquisition_year: 2025,
  client: {
    first_name: 'Chris',
    last_name: 'Depasqual',
    email: 'chris@example.com',
    phone: '0600000000',
    address: 'Paris',
  },
})

const canSubmit = computed(() =>
  form.value.years > 0 &&
  form.value.purchase_price >= 0 &&
  form.value.interest_rate_percent >= 0
)

const snack = ref<{open:boolean;text:string;color:'info'|'success'|'error'}>({
  open:false, text:'', color:'info'
})

async function submit() {
  try {
    loading.value = true
    const payload: any = { ...form.value }
    if (selectedClientId.value) {
      payload.client_id = selectedClientId.value
      delete payload.client
    }
    const res = await api.simulate(payload)
    result.value = res.result
    createdId.value = res.id
    snack.value = { open:true, text:'Simulation créée ✅', color:'success' }
  } catch (e: any) {
    snack.value = { open:true, text:'Erreur simulation: ' + (e.message || 'inconnue'), color:'error' }
  } finally {
    loading.value = false
  }
}

const downloading = ref(false)
async function downloadPdf() {
  if (!createdId.value) return
  try {
    downloading.value = true
    const blob = await api.pdf(createdId.value)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `simulation-${createdId.value}.pdf`
    document.body.appendChild(a); a.click(); a.remove()
    URL.revokeObjectURL(url)
  } finally {
    downloading.value = false
  }
}

/** Validation helpers */
const required = (v:any) => (v !== null && v !== undefined && v !== '' ? true : 'Requis')
const positive = (v:number) => (v >= 0 || 'Doit être ≥ 0')
const between = (min:number,max:number) => (v:number) => (v>=min && v<=max || `Entre ${min} et ${max}`)

/** Init: charger clients + restaurer le formulaire */
onMounted(async () => {
  try {
    const res = await api.listClients()
    clients.value = res.items
  } catch { /* ignore */ }

  const saved = localStorage.getItem('simulio:form')
  if (saved) {
    try { form.value = JSON.parse(saved) } catch { /* ignore */ }
  }
})

watch(form, (v) => localStorage.setItem('simulio:form', JSON.stringify(v)), { deep:true })
</script>

<template>
  <v-container class="py-6" fluid>
    <v-row class="mx-auto" style="max-width:1100px">

      <!-- Sélection d’un client existant -->
      <v-col cols="12">
        <v-autocomplete
          v-model="selectedClientId"
          :items="clients"
          item-title="email"
          item-value="id"
          label="Attribuer la simulation à un client (optionnel)"
          clearable
          hint="Si tu choisis un client, les champs en dessous sont facultatifs."
          persistent-hint
        />
      </v-col>

      <!-- Colonne gauche : Formulaire -->
      <v-col cols="12" lg="6">
        <v-card elevation="1" class="pa-4">
          <h2 class="text-h6 mb-4">Achat en résidence principale (ancien)</h2>

          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.purchase_price" label="Prix du bien"
                            type="number" min="0" suffix="€" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.works" label="Travaux"
                            type="number" min="0" suffix="€" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.agency_fee_rate_percent" label="Frais d'agence"
                            type="number" min="0" step="0.1" suffix="%" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.years" label="Durée du prêt (années)"
                            type="number" min="1" :rules="[required, (v:number)=>v>=1||'≥ 1']" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.down_payment" label="Apport"
                            type="number" min="0" suffix="€" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.notary_fee_rate_percent" label="Frais de notaire"
                            type="number" min="0" step="0.1" suffix="%" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.interest_rate_percent" label="Taux d'intérêt"
                            type="number" min="0" step="0.01" suffix="%" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.insurance_rate_percent" label="Taux d'assurance"
                            type="number" min="0" step="0.01" suffix="%" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.appreciation_rate_percent" label="Revalorisation du bien"
                            type="number" min="0" step="0.1" suffix="%/an" :rules="[required, positive]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.acquisition_month" label="Mois d'acquisition"
                            type="number" min="1" max="12" :rules="[required, between(1,12)]" />
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field v-model.number="form.acquisition_year" label="Année d'acquisition"
                            type="number" min="1990" :rules="[required, (v:number)=>v>=1990||'≥ 1990']" />
            </v-col>
          </v-row>

          <div class="mt-4 d-flex ga-3">
            <v-btn :loading="loading" :disabled="!canSubmit" color="primary" @click="submit">
              Calculer
            </v-btn>

            <v-btn v-if="createdId" :loading="downloading" @click="downloadPdf"
                   variant="tonal" color="primary">
              Télécharger le PDF
            </v-btn>
          </div>
        </v-card>
      </v-col>

      <!-- Colonne droite : Résultat -->
      <v-col cols="12" lg="6">
        <v-card elevation="1" class="pa-4">
          <h2 class="text-h6 mb-4">Résultat de simulation</h2>

          <template v-if="loading">
            <v-skeleton-loader type="article"></v-skeleton-loader>
          </template>

          <template v-else-if="result">
            <div class="text-medium-emphasis">Votre mensualité sera de</div>
            <div class="text-h3 mb-4 text-primary">{{ eur(result.monthly_payment_eur) }}</div>

            <v-row dense>
              <v-col cols="12" sm="6">
                <v-sheet rounded="lg" class="pa-3" color="grey-lighten-4" elevation="0">
                  <div class="text-caption text-medium-emphasis">Montant du prêt</div>
                  <div class="text-body-1 font-medium">{{ eur(result.loan_amount_eur) }}</div>
                </v-sheet>
              </v-col>

              <v-col cols="12" sm="6">
                <v-sheet rounded="lg" class="pa-3" color="grey-lighten-4" elevation="0">
                  <div class="text-caption text-medium-emphasis">Frais de notaire</div>
                  <div class="text-body-1 font-medium">{{ eur(result.notary_fee_eur) }}</div>
                </v-sheet>
              </v-col>

              <v-col cols="12" sm="6">
                <v-sheet rounded="lg" class="pa-3" color="grey-lighten-4" elevation="0">
                  <div class="text-caption text-medium-emphasis">Frais d'agence</div>
                  <div class="text-body-1 font-medium">{{ eur(result.agency_fee_eur) }}</div>
                </v-sheet>
              </v-col>

              <v-col cols="12" sm="6">
                <v-sheet rounded="lg" class="pa-3" color="grey-lighten-4" elevation="0">
                  <div class="text-caption text-medium-emphasis">Revenu min. mensuel</div>
                  <div class="text-body-1 font-medium">{{ eur(result.min_monthly_income_eur) }}</div>
                </v-sheet>
              </v-col>
            </v-row>
          </template>

          <v-alert v-else type="info" variant="tonal">
            Renseigne le formulaire puis clique “Calculer”.
          </v-alert>
        </v-card>
      </v-col>
    </v-row>

    <v-snackbar v-model="snack.open" :color="snack.color" timeout="3000" location="top right">
      {{ snack.text }}
    </v-snackbar>
  </v-container>
</template>
