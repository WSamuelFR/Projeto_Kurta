<script setup>
import { ref } from 'vue'
import axios from 'axios'

const query = ref('')
const results = ref([])
const searching = ref(false)

async function searchUsers() {
  if (query.value.length < 3) {
    results.value = []
    return
  }
  
  searching.value = true
  try {
    const res = await axios.get('/api/search', {
      params: { q: query.value }
    })
    if (res.data.status === 'success') {
      results.value = res.data.data.users // Pega apenas a lista de usuários da busca global
    }
  } catch (err) {
    console.error('Erro na busca:', err)
  } finally {
    searching.value = false
  }
}

async function sendInvite(userId) {
  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!user.id) {
    window.$toast.add('Você precisa estar logado.', 'error')
    return
  }
  try {
    const res = await axios.post('/api/social/add-friend', {
      sender_id: user.id,
      receiver_id: parseInt(userId)
    })
    if (res.data.status === 'success') {
      window.$toast.add('Convite enviado!', 'success')
    } else {
      window.$toast.add(res.data.message || 'Não foi possível enviar o convite.', 'error')
    }
  } catch (err) {
    window.$toast.add('Erro ao enviar convite.', 'error')
  }
}

function avatar_url(user) {
  const name = (user.first_name || '') + (user.last_name ? ' ' + user.last_name : '')
  const seed = name.trim() || 'User'
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(seed)}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}
</script>

<template>
  <div class="user-search mb-4">
    <div class="input-group">
      <span class="input-group-text bg-dark border-secondary text-white">
        <i class="bi bi-search"></i>
      </span>
      <input 
        type="text" 
        v-model="query" 
        @input="searchUsers" 
        class="form-control bg-dark border-secondary text-white" 
        placeholder="Buscar novos amigos (mín. 3 letras)..."
      >
    </div>

    <div v-if="searching" class="text-center mt-2">
      <div class="spinner-border spinner-border-sm text-primary"></div>
    </div>

    <div v-if="results.length > 0" class="search-results mt-2 glass-card p-2 shadow animate__animated animate__fadeIn">
      <div v-for="user in results" :key="user.user_id" class="search-item d-flex align-items-center gap-3 p-2 border-bottom border-white-50">
        <img :src="avatar_url(user)" class="search-avatar" alt="Avatar">
        <div class="flex-grow-1 text-start">
          <h6 class="mb-0 text-white fw-bold">{{ user.first_name }} {{ user.last_name }}</h6>
          <span class="text-muted small">@{{ user.first_name.toLowerCase() }}{{ user.user_id }}</span>
        </div>
        <button class="btn btn-sm btn-primary" @click="sendInvite(user.user_id)">
          <i class="bi bi-person-plus"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.search-results {
  max-height: 300px;
  overflow-y: auto;
  position: absolute;
  width: 100%;
  z-index: 100;
  background: rgba(15, 23, 42, 0.95);
}
.search-item:last-child {
  border-bottom: none !important;
}
.search-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}
.user-search {
  position: relative;
}
</style>
