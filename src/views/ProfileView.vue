<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import FriendList from '../components/FriendList.vue'
import ClanList from '../components/ClanList.vue'
import UserSearch from '../components/UserSearch.vue'
import PostCard from '../components/PostCard.vue'

const router = useRouter()
const user = ref({
  user_id: 0,
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  profile_pic: '',
  wallpaper_pic: '',
  created_at: null
})

const editedUser = ref({ ...user.value, password: '' })
const avatarPreview = ref(null)
const avatarFile = ref(null)

const loading = ref(true)
const saving = ref(false)
const activeTab = ref('feed')
const feelings = ref([])
const newFeeling = ref('')
const posting = ref(false)

onMounted(async () => {
  await fetchProfile()
  await fetchUserFeelings()
})

async function fetchProfile() {
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || 'null')
  if (!userLocal || !userLocal.id) {
    router.push('/login')
    return
  }

  try {
    const res = await axios.get('/api/profile', {
      params: { target_id: userLocal.id, my_id: userLocal.id }
    })
    if (res.data.success) {
      user.value = res.data.data
      resetEdit()
    }
  } catch (err) {
    console.error('Erro ao carregar perfil:', err)
    window.$toast.add('Erro ao carregar perfil.', 'error')
  } finally {
    loading.value = false
  }
}

async function fetchUserFeelings() {
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  const targetId = user.value.user_id || userLocal.id
  
  if (!targetId || targetId === 0) {
    console.warn('ID de usuário não disponível para buscar feelings.')
    return
  }

  console.log('Buscando sentimentos do perfil para ID:', targetId)
  try {
    const res = await axios.get(`/api/feelings/user/${targetId}`, {
      params: { visitorId: userLocal.id || 0 }
    })
    console.log('Resultado dos feelings do perfil:', res.data)
    if (res.data.status === 'success') {
      feelings.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao buscar feelings do usuário:', err)
  }
}

async function shareFeeling() {
  if (!newFeeling.value.trim()) return
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  const myId = user.value.user_id || userLocal.id

  if (!myId) {
    window.$toast.add('Erro de sessão. Reconecte-se.', 'error')
    return
  }

  posting.value = true
  try {
    const res = await axios.post('/api/feelings/create', {
      feeling: newFeeling.value,
      user_id: myId,
      visibility: 'public'
    })
    if (res.data.status === 'success') {
      newFeeling.value = ''
      window.$toast.add('Sentimento compartilhado!', 'success')
      await fetchUserFeelings()
    }
  } catch (err) {
    window.$toast.add('Erro ao postar sentimento.', 'error')
  } finally {
    posting.value = false
  }
}

function resetEdit() {
  editedUser.value = { ...user.value, password: '' }
  avatarPreview.value = null
  avatarFile.value = null
}

function onFileChange(type, e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (event) => {
    if (type === 'avatar') {
      avatarPreview.value = event.target.result
      avatarFile.value = file
    }
  }
  reader.readAsDataURL(file)
}

async function saveChanges() {
  saving.value = true
  const formData = new FormData()
  formData.append('user_id', user.value.user_id)
  formData.append('first_name', editedUser.value.first_name)
  formData.append('last_name', editedUser.value.last_name || '')
  formData.append('phone', editedUser.value.phone || '')
  formData.append('password', editedUser.value.password || '')
  
  if (avatarFile.value) formData.append('avatar', avatarFile.value)

  try {
    const res = await axios.post('/api/profile/update', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (res.data.success) {
      window.$toast.add('Perfil atualizado com sucesso!', 'success')
      await fetchProfile()
    } else {
      window.$toast.add(res.data.message, 'error')
    }
  } catch (err) {
    window.$toast.add('Falha na atualização do perfil.', 'error')
  } finally {
    saving.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return 'Membro Recente'
  try {
    return new Date(dateStr).toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' })
  } catch (e) {
    return 'Membro Recente'
  }
}

function avatar_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

</script>

<template>
  <div class="premium-profile">
    <div v-if="loading" class="loader-container">
      <div class="spinner-premium"></div>
    </div>

    <template v-else>
      <!-- Header: Avatar + Meta -->
      <div class="profile-header py-5 border-bottom border-white-5">
        <div class="container header-content">
          <div class="avatar-container">
            <img :src="avatar_pic_url(user.first_name + (user.last_name ? ' ' + user.last_name : ''))" alt="Avatar" class="avatar-img shadow-lg">
          </div>
          
          <div class="user-meta text-center text-md-start">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 mb-1">
              <h1 class="user-name mb-0">{{ user.first_name }} {{ user.last_name }}</h1>
              <div class="verified-badge-premium">
                <i class="bi bi-patch-check-fill"></i>
                <span>OFICIAL</span>
              </div>
            </div>
            <p class="user-handle">@{{ user.first_name?.toLowerCase() }}{{ user.user_id }}</p>
          </div>
        </div>
      </div>

      <!-- Main Layout -->
      <div class="container main-layout mt-5">
        <div class="row g-4">
          <!-- Sidebar -->
          <div class="col-lg-4">
            <div class="glass-card p-4">
              <h5 class="fw-bold text-white mb-4"><i class="bi bi-info-circle me-2"></i>Sobre</h5>
              <div class="info-list">
                <div class="info-row">
                  <span class="label">E-mail</span>
                  <span class="value">{{ user.email }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Telefone</span>
                  <span class="value">{{ user.phone || 'Não definido' }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Desde</span>
                  <span class="value text-primary">{{ formatDate(user.created_at) }}</span>
                </div>
              </div>
              <button class="btn-action-outline w-100 mt-4" @click="activeTab = 'settings'">
                <i class="bi bi-gear-fill me-2"></i>Ajustar Perfil
              </button>
            </div>
          </div>

          <!-- Content Tabs -->
          <div class="col-lg-8">
            <div class="glass-card overflow-hidden">
              <div class="tab-header-premium">
                <button class="premium-tab" :class="{ active: activeTab === 'feed' }" @click="activeTab = 'feed'">
                  <i class="bi bi-chat-quote-fill"></i>
                  <span>SENTIMENTOS</span>
                </button>
                <button class="premium-tab" :class="{ active: activeTab === 'friends' }" @click="activeTab = 'friends'">
                  <i class="bi bi-people-fill"></i>
                  <span>CONEXÕES</span>
                </button>
                <button class="premium-tab" :class="{ active: activeTab === 'clans' }" @click="activeTab = 'clans'">
                  <i class="bi bi-shield-shaded"></i>
                  <span>CLÃS</span>
                </button>
                <button class="premium-tab" :class="{ active: activeTab === 'settings' }" @click="activeTab = 'settings'">
                  <i class="bi bi-gear-wide-connected"></i>
                  <span>SISTEMA</span>
                </button>
              </div>

              <div class="tab-body p-4 text-start">
                <!-- Feed Section -->
                <div v-if="activeTab === 'feed'" class="animate__animated animate__fadeIn">
                   <!-- Posting Area -->
                   <div class="post-creator-premium p-4 mb-5 shadow">
                      <div class="d-flex gap-3">
                        <img :src="avatar_pic_url(user.first_name + (user.last_name ? ' ' + user.last_name : ''))" class="creator-avatar" alt="Avatar">
                        <div class="flex-grow-1">
                          <textarea 
                            v-model="newFeeling" 
                            class="creator-textarea" 
                            placeholder="O que está em seu coração?"
                            rows="3"
                          ></textarea>
                          <div class="d-flex justify-content-end mt-3">
                             <button class="btn-premium-action py-2 px-4" @click="shareFeeling" :disabled="posting">
                                <span v-if="!posting">COMPARTILHAR</span>
                                <span v-else class="spinner-border spinner-border-sm"></span>
                             </button>
                          </div>
                        </div>
                      </div>
                   </div>

                   <!-- List of Feelings -->
                   <div v-if="feelings.length > 0" class="feeling-list d-flex flex-column gap-4">
                      <PostCard v-for="post in feelings" :key="post.feeling_id" :post="post" />
                   </div>
                   <div v-else class="empty-state">
                      <i class="bi bi-chat-heart"></i>
                      <p>Você ainda não compartilhou nenhum sentimento.</p>
                   </div>
                </div>
                
                <!-- Social -->
                <div v-if="activeTab === 'friends'">
                  <UserSearch />
                  <div class="mt-4">
                    <FriendList :user-id="user.user_id" v-if="user.user_id" />
                  </div>
                </div>

                <div v-if="activeTab === 'clans'">
                  <ClanList />
                </div>

                <!-- Settings -->
                <div v-if="activeTab === 'settings'" class="animate__animated animate__fadeIn">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Primeiro Nome</label>
                      <input type="text" v-model="editedUser.first_name" class="premium-input">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Sobrenome</label>
                      <input type="text" v-model="editedUser.last_name" class="premium-input">
                    </div>
                    <div class="col-md-12">
                      <label class="form-label small fw-bold">Nova Senha</label>
                      <input type="password" v-model="editedUser.password" class="premium-input" placeholder="••••••••">
                    </div>
                  </div>
                  <div class="mt-4 d-flex gap-2">
                    <button class="btn-premium-action" @click="saveChanges" :disabled="saving">
                      <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                      Confirmar Mudanças
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.premium-profile {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  color: #f8fafc;
  padding-bottom: 5rem;
}

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.spinner-premium {
  width: 50px;
  height: 50px;
  border: 3px solid rgba(99, 102, 241, 0.2);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Header */
.profile-header {
  position: relative;
  background: rgba(15, 23, 42, 0.4);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 2rem;
  position: relative;
  z-index: 2;
}

.avatar-container {
  position: relative;
  width: 160px;
  height: 160px;
}

.avatar-img {
  width: 160px;
  height: 160px;
  border-radius: 40px;
  border: 4px solid #050810;
  object-fit: cover;
}

.avatar-edit-badge {
  position: absolute;
  bottom: -5px;
  right: -5px;
  background: #6366f1;
  color: white;
  width: 35px;
  height: 35px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 3px solid #050810;
}

.user-name {
  font-size: 2.8rem;
  font-weight: 700;
  margin-bottom: 0.2rem;
  background: linear-gradient(135deg, #fff, #94a3b8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -1.5px;
}

.user-badge {
  font-size: 0.9rem;
  color: #94a3b8;
  font-weight: 500;
}

/* Glass Cards */
.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
}

.info-row {
  margin-bottom: 1.5rem;
  text-align: left;
}

.info-row .label {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.info-row .value {
  font-size: 1rem;
  font-weight: 500;
  color: #f1f5f9;
}

/* Post Creator */
.post-creator-premium {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
}

.creator-avatar { width: 50px; height: 50px; border-radius: 15px; }

.creator-textarea {
  width: 100%;
  background: transparent;
  border: none;
  color: white;
  font-size: 1.1rem;
  resize: none;
  padding: 10px 0;
}
.creator-textarea:focus { outline: none; }

/* Tabs Premium */
.tab-header-premium {
  display: flex;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: relative;
  overflow-x: auto;
  scrollbar-width: none;
}
.tab-header-premium::-webkit-scrollbar { display: none; }

.premium-tab {
  flex: 1;
  min-width: 120px;
  background: transparent;
  border: none;
  padding: 18px 10px;
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 1px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  transition: all 0.3s;
  position: relative;
}

.premium-tab i { font-size: 1.2rem; transition: transform 0.3s; color: #475569; }

.premium-tab:hover { color: white; }
.premium-tab:hover i { transform: translateY(-3px); color: #818cf8; }

.premium-tab.active {
  color: #818cf8;
  background: rgba(99, 102, 241, 0.05);
}
.premium-tab.active i { color: #818cf8; }

.premium-tab.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, #6366f1, transparent);
  box-shadow: 0 -2px 10px rgba(99, 102, 241, 0.5);
}

.verified-badge-premium {
  background: rgba(99, 102, 241, 0.15);
  color: #818cf8;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 6px;
  border: 1px solid rgba(99, 102, 241, 0.3);
  letter-spacing: 1px;
}

.user-handle {
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  margin-top: 4px;
}

/* Inputs & Buttons */
.form-label {
  display: block;
  color: #94a3b8;
  font-size: 0.85rem;
  margin-bottom: 8px;
  padding-left: 5px;
}

.premium-input {
  width: 100%;
  padding: 14px 16px;
  background: rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  color: white;
  transition: all 0.3s;
}

.premium-input:focus {
  outline: none;
  border-color: #6366f1;
  background: rgba(2, 6, 23, 0.6);
}

.btn-premium-action {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border: none;
  padding: 14px 32px;
  border-radius: 16px;
  color: white;
  font-weight: 700;
  transition: all 0.3s;
  box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
}

.btn-premium-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.5);
}

.btn-action-outline {
  background: transparent;
  border: 1px solid rgba(99, 102, 241, 0.3);
  padding: 14px;
  border-radius: 16px;
  color: #818cf8;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-action-outline:hover {
  background: rgba(99, 102, 241, 0.1);
  border-color: #6366f1;
}

.empty-state {
  text-align: center;
  padding: 5rem 2rem;
  color: #64748b;
}

.empty-state i {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  display: block;
  opacity: 0.4;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
  }
  .user-name { font-size: 2rem; }
}
</style>
