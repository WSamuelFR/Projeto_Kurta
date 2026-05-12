<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import PostCard from '../components/PostCard.vue'

const route = useRoute()
const query = ref(route.query.q || '')
const results = ref({ users: [], clans: [], feelings: [] })
const loading = ref(false)
const activeTab = ref('all')

onMounted(() => {
  if (query.value) performSearch()
})

watch(() => route.query.q, (newQ) => {
  query.value = newQ
  performSearch()
})

async function performSearch() {
  if (!query.value) return
  loading.value = true
  try {
    const res = await axios.get('/api/search', {
      params: { q: query.value }
    })
    if (res.data.status === 'success') {
      results.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function avatar_url(user) {
  if (typeof user === 'string') return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(user)}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
  const name = user.first_name + (user.last_name ? ' ' + user.last_name : '')
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name)}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function clan_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'Clan')}&backgroundColor=1e293b,334155,0f172a`
}
</script>

<template>
  <div class="search-page py-5">
    <div class="container">
      <div class="glass-card p-4 mb-5 animate__animated animate__fadeInDown">
        <h2 class="text-white fw-bold mb-1">Resultados para: <span class="text-primary">"{{ query }}"</span></h2>
        <p class="text-white-50 small mb-0">Encontramos {{ results.users.length + results.clans.length + results.feelings.length }} correspondências.</p>
      </div>

      <!-- Tabs Navigation -->
      <div class="search-tabs d-flex gap-2 mb-4 overflow-auto pb-2">
        <button 
          class="btn-tab" 
          :class="{ active: activeTab === 'all' }" 
          @click="activeTab = 'all'"
        >Tudo</button>
        <button 
          class="btn-tab" 
          :class="{ active: activeTab === 'users' }" 
          @click="activeTab = 'users'"
        >Pessoas ({{ results.users.length }})</button>
        <button 
          class="btn-tab" 
          :class="{ active: activeTab === 'clans' }" 
          @click="activeTab = 'clans'"
        >Clãs ({{ results.clans.length }})</button>
        <button 
          class="btn-tab" 
          :class="{ active: activeTab === 'feelings' }" 
          @click="activeTab = 'feelings'"
        >Feelings ({{ results.feelings.length }})</button>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

      <div v-else class="results-content animate__animated animate__fadeIn">
        
        <!-- Tab: Pessoas -->
        <div v-if="activeTab === 'all' || activeTab === 'users'" class="mb-5">
          <h5 class="text-white fw-bold mb-4" v-if="activeTab === 'all'">Pessoas</h5>
          <div class="row g-3">
            <div v-for="user in results.users" :key="user.user_id" class="col-md-6 col-lg-4">
              <div class="result-card p-3 d-flex align-items-center gap-3">
                <img :src="avatar_url(user)" class="rounded-circle shadow" style="width: 55px; height: 55px; object-fit: cover;">
                <div class="text-start flex-grow-1">
                  <h6 class="text-white mb-0 fw-bold">{{ user.first_name }} {{ user.last_name }}</h6>
                  <span class="text-muted small">Usuário fell.it</span>
                </div>
                <router-link :to="'/user/' + user.user_id" class="btn btn-sm btn-outline-primary rounded-pill">Ver Perfil</router-link>
              </div>
            </div>
          </div>
          <p v-if="results.users.length === 0 && activeTab === 'users'" class="text-muted">Nenhuma pessoa encontrada.</p>
        </div>

        <!-- Tab: Clãs -->
        <div v-if="activeTab === 'all' || activeTab === 'clans'" class="mb-5">
          <h5 class="text-white fw-bold mb-4" v-if="activeTab === 'all'">Clãs</h5>
          <div class="row g-3">
            <div v-for="clan in results.clans" :key="clan.clan_id" class="col-md-6 col-lg-4">
              <div class="result-card p-3 d-flex align-items-center gap-3">
                <img :src="clan_pic_url(clan.name_clan)" class="rounded-3 shadow" style="width: 55px; height: 55px; object-fit: cover;">
                <div class="text-start flex-grow-1">
                  <h6 class="text-white mb-0 fw-bold">{{ clan.name_clan }}</h6>
                  <p class="text-muted small mb-0 text-truncate" style="max-width: 150px;">{{ clan.description }}</p>
                </div>
                <router-link :to="'/clan/' + clan.clan_id" class="btn btn-sm btn-primary rounded-pill">Entrar</router-link>
              </div>
            </div>
          </div>
          <p v-if="results.clans.length === 0 && activeTab === 'clans'" class="text-muted">Nenhum clã encontrado.</p>
        </div>

        <!-- Tab: Feelings -->
        <div v-if="activeTab === 'all' || activeTab === 'feelings'" class="mb-5">
          <h5 class="text-white fw-bold mb-4" v-if="activeTab === 'all'">Sentimentos</h5>
          <div v-for="post in results.feelings" :key="post.feeling_id" class="mb-3">
            <PostCard :post="post" />
          </div>
          <p v-if="results.feelings.length === 0 && activeTab === 'feelings'" class="text-muted">Nenhum sentimento encontrado.</p>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.search-page {
  min-height: 100vh;
  background-color: #0f172a;
}

.glass-card {
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
}

.btn-tab {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  padding: 8px 20px;
  border-radius: 12px;
  font-weight: bold;
  transition: all 0.3s;
  white-space: nowrap;
}

.btn-tab.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
}

.result-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  transition: transform 0.2s, background 0.2s;
}

.result-card:hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.06);
}
</style>
