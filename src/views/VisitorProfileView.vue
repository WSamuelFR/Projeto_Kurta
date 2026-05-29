<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import PostCard from '../components/PostCard.vue'
import { encodeId, decodeId } from '../utils/obfuscator'

const route = useRoute()
const router = useRouter()
const userId = ref(decodeId(route.params.id))
const user = ref(null)
const feelings = ref([])
const userClans = ref([])
const loading = ref(true)
const activeTab = ref('mural')
const friendshipStatus = ref('none')
const friendshipId = ref(null)

onMounted(fetchData)

watch(() => route.params.id, (newId) => {
  userId.value = decodeId(newId)
  fetchData()
})

async function fetchData() {
  loading.value = true
  const myUser = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  
  try {
    const res = await axios.get('/api/profile', {
      params: { target_id: userId.value, my_id: myUser.id }
    })
    
    if (res.data.success) {
      if (res.data.is_own_profile) {
        router.push('/profile')
        return
      }
      user.value = res.data.data
      userClans.value = res.data.clans || []
      friendshipStatus.value = res.data.friendship_status
      friendshipId.value = res.data.friendship_id
      fetchFeelings()
    } else {
      router.push('/home')
    }
  } catch (err) {
    console.error(err)
    router.push('/home')
  } finally {
    loading.value = false
  }
}

async function fetchFeelings() {
  try {
    const res = await axios.get(`/api/feelings/user/${userId.value}`, {
      params: { visitorId: JSON.parse(localStorage.getItem('fellit_user') || '{}').id || 0 } 
    })
    if (res.data.status === 'success') {
      feelings.value = res.data.data
    }
  } catch (err) {
    console.error(err)
  }
}

async function handleSocialAction() {
  const myUser = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  let endpoint = ''
  const targetIdInt = parseInt(userId.value)
  let data = { sender_id: myUser.id, receiver_id: targetIdInt }

  if (friendshipStatus.value === 'none') {
    endpoint = '/api/social/add-friend'
  } else if (friendshipStatus.value === 'pending_received') {
    endpoint = '/api/social/respond-friend'
    data = { user_id: myUser.id, friendship_id: friendshipId.value, action: 'accepted' }
  } else if (friendshipStatus.value === 'accepted') {
    if (!confirm('Deseja remover amizade?')) return
    endpoint = '/api/social/remove-friend'
    data = { user_id: myUser.id, friend_id: targetIdInt }
  }

  if (!endpoint) return

  try {
    const res = await axios.post(endpoint, data)
    if (res.data.status === 'success') {
      window.$toast.add('Ação realizada com sucesso!', 'success')
      fetchData() 
    }
  } catch (err) {
    console.error(err)
    window.$toast.add('Erro ao processar ação social.', 'error')
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function clan_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'Clan')}&backgroundColor=1e293b,334155,0f172a`
}


</script>

<template>
  <div class="premium-visitor">
    <div v-if="loading" class="loader-container">
      <div class="spinner-premium"></div>
    </div>

    <div v-else-if="user" class="profile-content animate__animated animate__fadeIn">
      <!-- Header Area: Avatar + Meta -->
      <div class="profile-header py-5 border-bottom border-white-5">
        <div class="container header-main">
          <div class="avatar-container">
            <img :src="avatar_url(user.first_name + (user.last_name ? ' ' + user.last_name : ''))" alt="Avatar" class="avatar-img shadow-lg">
          </div>
          
          <div class="user-meta-row d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div class="user-info">
              <h1 class="user-name">{{ user.first_name }} {{ user.last_name }}</h1>
              <p class="user-status"><i class="bi bi-circle-fill text-success me-2" style="font-size: 0.6rem;"></i>Online agora</p>
            </div>
            
            <div class="social-actions">
              <button 
                @click="handleSocialAction"
                class="btn-social-premium"
                :class="friendshipStatus"
              >
                <i v-if="friendshipStatus === 'none'" class="bi bi-person-plus-fill"></i>
                <i v-else-if="friendshipStatus === 'pending_sent'" class="bi bi-hourglass-split"></i>
                <i v-else-if="friendshipStatus === 'pending_received'" class="bi bi-check2-circle"></i>
                <i v-else class="bi bi-people-fill"></i>
                
                <span>
                  {{ 
                    friendshipStatus === 'none' ? 'Conectar' : 
                    friendshipStatus === 'pending_sent' ? 'Pendente' : 
                    friendshipStatus === 'pending_received' ? 'Aceitar' : 'Amigos' 
                  }}
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Layout -->
      <div class="container mt-5">
        <div class="row g-4">
          <!-- Sidebar -->
          <div class="col-lg-4">
            <div class="glass-card p-4">
              <h5 class="text-white fw-bold mb-4">Informações</h5>
              <div class="info-list">
                <div class="info-row">
                  <span class="label">Membro fell.it</span>
                  <span class="value">Explorador de Sentimentos</span>
                </div>
                <div class="info-row">
                  <span class="label">Localização</span>
                  <span class="value">Brasil</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Feed/Mural -->
          <div class="col-lg-8 text-start">
            <div class="glass-card mb-4">
              <div class="tab-header d-flex gap-4 p-3 border-bottom border-white-5">
                <button class="tab-btn" :class="{ active: activeTab === 'mural' }" @click="activeTab = 'mural'">Mural de Sentimentos</button>
                <button class="tab-btn" :class="{ active: activeTab === 'clans' }" @click="activeTab = 'clans'">Clãs</button>
              </div>

              <div class="p-4">
                <div v-if="activeTab === 'mural'">
                  <div v-if="feelings.length > 0" class="feed-list d-flex flex-column gap-4">
                    <PostCard v-for="post in feelings" :key="post.feeling_id" :post="post" />
                  </div>
                  <div v-else class="empty-state py-5 text-center">
                    <i class="bi bi-chat-dots-fill opacity-25 fs-1 mb-3"></i>
                    <p class="text-muted italic">Este usuário ainda não abriu o coração no mural.</p>
                  </div>
                </div>

                <div v-if="activeTab === 'clans'">
                  <div v-if="userClans.length > 0" class="row g-3">
                    <div v-for="c in userClans" :key="c.clan_id" class="col-md-6">
                       <router-link :to="'/clan/' + encodeId(c.clan_id)" class="text-decoration-none">
                         <div class="result-card p-3 d-flex align-items-center gap-3">
                           <img :src="clan_pic_url(c.name_clan)" class="rounded-3 shadow" style="width: 50px; height: 50px; object-fit: cover;">
                           <div class="text-start flex-grow-1 overflow-hidden">
                             <h6 class="text-white mb-0 fw-bold text-truncate">{{ c.name_clan }}</h6>
                             <span class="text-muted small">Ver Clã</span>
                           </div>
                         </div>
                       </router-link>
                    </div>
                  </div>
                  <div v-else class="empty-state py-5 text-center">
                    <i class="bi bi-shield-slash opacity-25 fs-1 mb-3"></i>
                    <p class="text-muted italic">Este usuário ainda não faz parte de nenhum clã.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.premium-visitor {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  color: #f8fafc;
}

.loader-container { display: flex; justify-content: center; align-items: center; height: 100vh; }
.spinner-premium { width: 50px; height: 50px; border: 3px solid rgba(99, 102, 241, 0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Header */
.profile-header { position: relative; background: rgba(15, 23, 42, 0.4); }

.header-main { position: relative; z-index: 5; }
.avatar-container { width: 160px; height: 160px; margin-bottom: 1.5rem; }
.avatar-img { width: 160px; height: 160px; border-radius: 40px; border: 4px solid #050810; object-fit: cover; }

.user-name { font-size: 2.8rem; font-weight: 700; letter-spacing: -1.5px; background: linear-gradient(to bottom, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.user-status { font-size: 0.9rem; color: #94a3b8; font-weight: 500; }

/* Social Button */
.btn-social-premium {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 28px;
  border-radius: 16px;
  font-weight: 700;
  transition: all 0.3s;
  border: none;
}

.btn-social-premium.none { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); }
.btn-social-premium.pending_sent { background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.1); cursor: default; }
.btn-social-premium.pending_received { background: #10b981; color: white; }
.btn-social-premium.accepted { background: transparent; border: 1px solid rgba(255, 255, 255, 0.2); color: white; }

.btn-social-premium:hover:not(.pending_sent) { transform: translateY(-2px); filter: brightness(1.1); }

/* Cards */
.glass-card { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px; }
.info-row { margin-bottom: 1.5rem; text-align: left; }
.info-row .label { display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
.info-row .value { font-size: 1rem; color: #f1f5f9; }

.tab-btn { background: transparent; border: none; color: #94a3b8; font-weight: 600; padding: 10px 0; border-bottom: 2px solid transparent; transition: all 0.3s; }
.tab-btn.active { color: #818cf8; border-bottom-color: #818cf8; }

.border-white-5 { border-color: rgba(255, 255, 255, 0.05) !important; }
</style>
