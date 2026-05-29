<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { encodeId } from '../utils/obfuscator'

const props = defineProps({
  userId: Number
})

const friends = ref([])
const loading = ref(true)
const showUnfriendModal = ref(false)
const friendIdToUnfriend = ref(null)

onMounted(fetchFriends)

async function fetchFriends() {
  try {
    const res = await axios.get('/api/profile/friends', {
      params: { user_id: props.userId }
    })
    if (res.data.status === 'success') {
      friends.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao carregar amigos:', err)
  } finally {
    loading.value = false
  }
}

function requestRemoveFriend(friendId) {
  friendIdToUnfriend.value = friendId
  showUnfriendModal.value = true
}

async function confirmUnfriend() {
  showUnfriendModal.value = false
  if (!friendIdToUnfriend.value) return

  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  try {
    const res = await axios.post('/api/profile/remove-friend', {
      user_id: user.id,
      friend_id: friendIdToUnfriend.value
    })
    if (res.data.status === 'success') {
      window.$toast.add('Amizade removida.', 'success')
      fetchFriends()
    }
  } catch (err) {
    window.$toast.add('Erro ao remover amigo.', 'error')
  } finally {
    friendIdToUnfriend.value = null
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}
</script>

<template>
  <div class="friend-list">
    <div v-if="loading" class="text-center p-3">
      <div class="spinner-border spinner-border-sm text-primary"></div>
    </div>
    
    <div v-else-if="friends.length === 0" class="text-muted italic p-3">
      Nenhum amigo encontrado nesta lista.
    </div>

    <div v-else class="row g-3">
      <div v-for="friend in friends" :key="friend.user_id" class="col-md-6 col-lg-4">
        <div class="friend-card glass-card p-3 d-flex align-items-center gap-3">
          <img :src="avatar_url(friend.first_name + (friend.last_name ? ' ' + friend.last_name : ''))" class="friend-avatar" alt="Avatar">
          <div class="flex-grow-1 overflow-hidden">
            <router-link :to="'/user/' + encodeId(friend.user_id)" class="text-white text-decoration-none">
              <h6 class="mb-0 text-white text-truncate hover-primary fw-bold">{{ friend.first_name }} {{ friend.last_name }}</h6>
            </router-link>
            <span class="text-muted small">Amigo</span>
          </div>
          <button class="btn btn-sm btn-outline-danger" @click="requestRemoveFriend(friend.user_id)">
            <i class="bi bi-person-x"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação para Desfazer Amizade -->
    <div v-if="showUnfriendModal" class="custom-modal-overlay">
      <div class="custom-modal-content glass-card p-4 animate__animated animate__zoomIn">
        <h5 class="text-white fw-bold mb-3">Desfazer Amizade?</h5>
        <p class="text-white-50 small mb-4">Tem certeza que deseja remover esta pessoa da sua lista de amigos? Vocês não estarão mais conectados.</p>
        <div class="d-flex justify-content-center gap-3">
          <button class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-3 text-white" @click="showUnfriendModal = false">Cancelar</button>
          <button class="btn btn-sm btn-danger px-3 py-2 rounded-3" @click="confirmUnfriend">Remover Amigo</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.friend-card {
  transition: transform 0.2s;
  background: rgba(255, 255, 255, 0.05);
}
.friend-card:hover {
  transform: translateY(-3px);
  background: rgba(255, 255, 255, 0.1);
}
.friend-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.1);
}
.hover-primary {
  transition: color 0.2s;
}
.hover-primary:hover {
  color: #6366f1 !important;
}

.custom-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99999;
}
.custom-modal-content {
  width: 90%;
  max-width: 400px;
  background: rgba(15, 23, 42, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px !important;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5);
  text-align: center;
}
</style>
