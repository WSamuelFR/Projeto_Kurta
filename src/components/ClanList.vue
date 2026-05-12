<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const clans = ref([])
const loading = ref(true)
const filter = ref('todos')

onMounted(fetchClans)

async function fetchClans() {
  loading.value = true
  try {
    const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
    const res = await axios.get('/api/clans', {
      params: { filter: filter.value, user_id: user.id }
    })
    if (res.data.status === 'success') {
      clans.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao carregar clãs:', err)
  } finally {
    loading.value = false
  }
}

async function joinClan(clanId) {
  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!user.id) {
    window.$toast.add('Você precisa estar logado para entrar em um clã.', 'error')
    return
  }

  try {
    const res = await axios.post('/api/clans/join', {
      clan_id: clanId,
      user_id: user.id
    })
    if (res.data.status === 'success') {
      window.$toast.add('Você entrou no clã!', 'success')
      fetchClans()
    } else {
      window.$toast.add(res.data.message || 'Erro ao entrar no clã.', 'error')
    }
  } catch (err) {
    window.$toast.add('Erro ao entrar no clã.', 'error')
  }
}

function clan_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'Clan')}&backgroundColor=1e293b,334155,0f172a`
}
</script>

<template>
  <div class="clan-module">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="btn-group btn-group-sm">
        <button 
          class="btn btn-outline-primary" 
          :class="{ active: filter === 'todos' }" 
          @click="filter = 'todos'; fetchClans()"
        >Explorar</button>
        <button 
          class="btn btn-outline-primary" 
          :class="{ active: filter === 'meus-clas' }" 
          @click="filter = 'meus-clas'; fetchClans()"
        >Meus Clãs</button>
      </div>
      <router-link to="/clan/create" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Criar Clã
      </router-link>
    </div>

    <div v-if="loading" class="text-center p-3">
      <div class="spinner-border spinner-border-sm text-primary"></div>
    </div>

    <div v-else-if="clans.length === 0" class="text-muted italic p-3 text-center">
      Nenhum clã encontrado nesta categoria.
    </div>

    <div v-else class="row g-3">
      <div v-for="clan in clans" :key="clan.clan_id" class="col-md-6">
        <div class="clan-card glass-card p-3 d-flex align-items-center gap-3">
          <img :src="clan_pic_url(clan.name_clan)" class="clan-avatar" alt="Clan">
          <div class="flex-grow-1 overflow-hidden text-start">
            <h6 class="mb-0 text-white text-truncate">{{ clan.name_clan }}</h6>
            <span class="text-muted small">👥 {{ clan.total_membros }} vivos</span>
          </div>
          <div class="actions">
            <button 
              v-if="!clan.user_role && clan.visibility === 'public'" 
              class="btn btn-sm btn-primary"
              @click="joinClan(clan.clan_id)"
            >Entrar</button>
            <router-link :to="`/clan/${clan.clan_id}`" class="btn btn-sm btn-outline-light">Ver</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.clan-card {
  transition: all 0.2s;
  background: rgba(255, 255, 255, 0.05);
}
.clan-card:hover {
  background: rgba(255, 255, 255, 0.1);
}
.clan-avatar {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
