<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { api, type Client } from '@/lib/apiClients'

const search = ref('')
const items = ref<Client[]>([])
const loading = ref(false)

const dialog = ref(false)
const editId = ref<number|null>(null)
const form = ref<Omit<Client,'id'|'user_id'>>({
  first_name:'', last_name:'', email:'', phone:'', address:''
})

async function load() {
  loading.value = true
  try {
    items.value = (await api.searchClients(search.value, 1, 50)).items
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editId.value = null
  form.value = { first_name:'', last_name:'', email:'', phone:'', address:'' }
  dialog.value = true
}

function openEdit(c: Client) {
  editId.value = c.id
  form.value = { first_name:c.first_name, last_name:c.last_name, email:c.email, phone:c.phone, address:c.address }
  dialog.value = true
}

async function save() {
  loading.value = true
  try {
    if (editId.value) {
      await api.updateClient(editId.value, form.value)
    } else {
      await api.createClient(form.value)
    }
    dialog.value = false
    await load()
  } finally {
    loading.value = false
  }
}

async function remove(id: number) {
  if (!confirm('Supprimer ce client ?')) return
  loading.value = true
  try {
    await api.deleteClient(id)
    await load()
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
        <div class="d-flex ga-3 mb-3">
          <v-text-field v-model="search" label="Rechercher (nom, email)" @keyup.enter="load" prepend-inner-icon="mdi-magnify" clearable/>
          <v-btn color="primary" @click="load">Rechercher</v-btn>
          <v-spacer />
          <v-btn color="primary" @click="openCreate">Nouveau client</v-btn>
        </div>

        <v-data-table
          :items="items"
          :loading="loading"
          :item-value="'id'"
          :headers="[
            {title:'Nom', key:'last_name'},
            {title:'Prénom', key:'first_name'},
            {title:'Email', key:'email'},
            {title:'Téléphone', key:'phone'},
            {title:'Actions', key:'actions', sortable:false},
          ]"
        >
<template #item.actions="{ item: slotItem }">
  <v-btn
    size="small"
    variant="text"
    @click="openEdit(slotItem)"
  >
    Modifier
  </v-btn>

  <v-btn
    size="small"
    variant="text"
    color="error"
    @click.stop="remove(Number(slotItem?.value ?? slotItem?.id ?? slotItem?.raw?.id))"
  >
    Supprimer
  </v-btn>
</template>

        </v-data-table>

      </v-col>
    </v-row>

    <v-dialog v-model="dialog" max-width="520">
      <v-card title="Client">
        <v-card-text>
          <v-text-field v-model="form.last_name" label="Nom" required />
          <v-text-field v-model="form.first_name" label="Prénom" required />
          <v-text-field v-model="form.email" label="Email" type="email" required />
          <v-text-field v-model="form.phone" label="Téléphone" />
          <v-text-field v-model="form.address" label="Adresse" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="dialog=false">Annuler</v-btn>
          <v-btn color="primary" :loading="loading" @click="save">Enregistrer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
