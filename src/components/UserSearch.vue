<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { encodeId } from '../utils/obfuscator'

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
    <div class="input-glass-wrapper">
      <i class="bi bi-search"></i>
      <input 
        type="text" 
        v-model="query" 
        @input="searchUsers" 
        class="search-input" 
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
          <router-link :to="'/user/' + encodeId(user.user_id)" class="text-white text-decoration-none">
            <h6 class="mb-0 fw-bold hover-primary">{{ user.first_name }} {{ user.last_name }}</h6>
          </router-link>
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
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
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
.input-glass-wrapper {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 18px;
  display: flex;
  align-items: center;
  padding: 5px 15px;
  transition: all 0.3s;
}
.input-glass-wrapper:focus-within {
  background: rgba(15, 23, 42, 0.8);
  border-color: #6366f1;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}
.input-glass-wrapper i {
  color: #64748b;
  font-size: 1.2rem;
  margin-right: 12px;
}
.search-input {
  background: transparent;
  border: none;
  color: white;
  width: 100%;
  padding: 12px 0;
}
.search-input:focus {
  outline: none;
}
.hover-primary {
  transition: color 0.2s;
}
.hover-primary:hover {
  color: #6366f1 !important;
}
</style>
