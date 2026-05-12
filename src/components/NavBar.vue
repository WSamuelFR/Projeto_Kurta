<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '../stores/notificationStore'

const router = useRouter()
const notifStore = useNotificationStore()
const showDropdown = ref(false)
const searchQuery = ref('')

function performSearch() {
  if (searchQuery.value.trim().length > 1) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
    searchQuery.value = ''
  }
}

onMounted(() => {
  notifStore.fetchNotifications()
  // Polling simples a cada 30 segundos para novas notificações
  setInterval(() => {
    notifStore.fetchNotifications()
  }, 30000)
})

async function handleAction(notif, action) {
  const success = await notifStore.respond(notif, action)
  if (success) {
    // Feedback visual opcional
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function logout() {
  localStorage.removeItem('fellit_token')
  localStorage.removeItem('fellit_user')
  router.push('/login')
}
</script>

<template>
  <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
      <div class="d-flex align-items-center gap-3">
        <router-link to="/home" class="navbar-brand fw-bold">feel.it</router-link>
        
        <!-- Barra de Busca -->
        <div class="search-bar-wrapper d-none d-md-block">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-dark-50 border-0 text-muted ps-3">
              <i class="bi bi-search"></i>
            </span>
            <input 
              v-model="searchQuery" 
              type="text" 
              class="form-control bg-dark-50 border-0 text-white py-2" 
              placeholder="Buscar amigos, clãs..."
              @keyup.enter="performSearch"
            >
          </div>
        </div>
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <!-- Sino de Notificações -->
        <div class="notif-wrapper">
          <button 
            class="btn-notif position-relative" 
            @click="showDropdown = !showDropdown"
          >
            <i class="bi bi-bell"></i>
            <span v-if="notifStore.unreadCount > 0" class="badge-dot"></span>
          </button>

          <!-- Dropdown Glassmorphism -->
          <div v-if="showDropdown" class="glass-dropdown animate__animated animate__fadeIn">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
              <span class="fw-bold">Notificações</span>
              <span class="badge bg-primary rounded-pill">{{ notifStore.unreadCount }}</span>
            </div>
            
            <div class="dropdown-body">
              <div v-if="notifStore.notifications.length === 0" class="empty-state">
                <i class="bi bi-info-circle mb-2 d-block fs-4"></i>
                <p>Nenhuma notificação nova</p>
              </div>

              <div v-else v-for="notif in notifStore.notifications" :key="notif.notif_id" class="notif-item p-3 d-flex align-items-center gap-3 border-bottom border-white-50">
                <img :src="avatar_url(notif.user_notification_sender_idTouser.first_name + (notif.user_notification_sender_idTouser.last_name ? ' ' + notif.user_notification_sender_idTouser.last_name : ''))" class="notif-avatar" alt="User">
                <div class="flex-grow-1 text-start">
                  <p class="mb-0 text-white small" v-if="notif.notif_type === 'friend_request'">
                    <i class="bi bi-person-plus text-info me-1"></i>
                    <strong class="text-info">{{ notif.user_notification_sender_idTouser.first_name }}</strong> enviou um convite de amizade.
                  </p>
                  <p class="mb-0 text-white small" v-else-if="notif.notif_type === 'clan_join'">
                    <i class="bi bi-shield-check text-warning me-1"></i>
                    <strong class="text-warning">{{ notif.user_notification_sender_idTouser.first_name }}</strong> entrou no clã.
                  </p>
                  <p class="mb-0 text-white small" v-else>
                    <i class="bi bi-info-circle text-muted me-1"></i>
                    Notificação de <strong>{{ notif.user_notification_sender_idTouser.first_name }}</strong>.
                  </p>
                  <span class="text-muted extra-small">{{ new Date(notif.created_at).toLocaleTimeString() }}</span>
                </div>
                <div class="d-flex gap-1" v-if="notif.notif_type === 'friend_request'">
                  <button class="btn btn-xs btn-success" @click="handleAction(notif, 'accept')">
                    <i class="bi bi-check-lg"></i>
                  </button>
                  <button class="btn btn-xs btn-danger" @click="handleAction(notif, 'reject')">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <router-link to="/profile" class="btn btn-outline-light btn-sm px-3 rounded-pill">
          Meu Perfil
        </router-link>

        <button @click="logout" class="btn btn-link text-danger p-0 ms-2">
          <i class="bi bi-box-arrow-right fs-5"></i>
        </button>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  z-index: 10000;
}

.navbar-brand {
  font-size: 1.5rem;
  background: linear-gradient(135deg, #60a5fa, #c084fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.search-bar-wrapper {
  min-width: 280px;
}

.bg-dark-50 {
  background: rgba(255, 255, 255, 0.08) !important;
  border: 1px solid rgba(255, 255, 255, 0.15) !important;
}

.bg-dark-50 i {
  color: white !important;
  opacity: 0.9;
}

.form-control::placeholder {
  color: rgba(255, 255, 255, 0.5) !important;
}

.input-group-text, .form-control {
  border-radius: 12px !important;
  color: white !important;
}

.btn-notif {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1.3rem;
  padding: 0;
  transition: color 0.3s;
}

.btn-notif:hover {
  color: white;
}

.badge-dot {
  position: absolute;
  top: 2px;
  right: -2px;
  width: 8px;
  height: 8px;
  background-color: #ef4444;
  border-radius: 50%;
  border: 2px solid #0f172a;
  box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
}

.notif-wrapper {
  position: relative;
}

.glass-dropdown {
  position: absolute;
  top: 45px;
  right: -80px;
  width: 320px;
  background: rgba(30, 41, 59, 0.9);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 18px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
  z-index: 9999;
  overflow: hidden;
}

.dropdown-header {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  color: #94a3b8;
}

.dropdown-body {
  max-height: 400px;
  overflow-y: auto;
}

.notif-item {
  transition: background 0.2s;
}

.notif-item:hover {
  background: rgba(255, 255, 255, 0.03);
}

.notif-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-xs {
  padding: 2px 6px;
  font-size: 0.75rem;
  border-radius: 6px;
}

.extra-small {
  font-size: 0.7rem;
}

.empty-state {
  padding: 30px;
  color: #64748b;
  font-size: 0.85rem;
}
</style>
