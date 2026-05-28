<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import PostCard from '../components/PostCard.vue'

const route = useRoute()
const router = useRouter()
const clanId = route.params.id

const clan = ref(null)
const members = ref([])
const feelings = ref([])
const viewerRole = ref(null)
const loading = ref(true)
const joining = ref(false)
const posting = ref(false)
const newFeeling = ref('')

// Controle de abas no celular
const activeMobileTab = ref('feed') // 'feed' ou 'about'

onMounted(async () => {
  await fetchClanData()
  await fetchClanFeelings()
})

async function fetchClanData() {
  const token = localStorage.getItem('fellit_token')
  if (!token) return router.push('/login')

  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  try {
    const resClan = await axios.get(`/api/clans/${clanId}`)
    if (resClan.data.status === 'success') {
      clan.value = resClan.data.data
    }

    const resMembers = await axios.get(`/api/clans/${clanId}/members`, {
      params: { user_id: userLocal.id }
    })
    if (resMembers.data.status === 'success') {
      members.value = resMembers.data.data
      viewerRole.value = resMembers.data.viewerRole
    }
  } catch (err) {
    console.error('Erro ao carregar clã:', err)
  } finally {
    loading.value = false
  }
}

async function fetchClanFeelings() {
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  try {
    const res = await axios.get(`/api/feelings/clan/${clanId}`, {
      params: { visitorId: userLocal.id || 0 }
    })
    if (res.data.status === 'success') {
      feelings.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao buscar feelings do clã:', err)
  }
}

async function joinClan() {
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!userLocal.id) return router.push('/login')

  joining.value = true
  try {
    const res = await axios.post('/api/clans/join', {
      clan_id: parseInt(clanId),
      user_id: userLocal.id
    })
    if (res.data.status === 'success') {
      window.$toast.add('Bem-vindo ao Clã!', 'success')
      await fetchClanData()
    } else {
      window.$toast.add(res.data.message || 'Não foi possível entrar no clã.', 'error')
    }
  } catch (err) {
    console.error('Erro ao entrar no clã:', err)
    window.$toast.add('Erro ao processar sua entrada.', 'error')
  } finally {
    joining.value = false
  }
}

async function shareFeeling() {
  if (!newFeeling.value.trim()) return
  const userLocal = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!userLocal.id) return
  
  posting.value = true
  try {
    const res = await axios.post('/api/feelings/create', {
      feeling: newFeeling.value,
      user_id: userLocal.id,
      clan_id: parseInt(clanId),
      visibility: 'public'
    })
    if (res.data.status === 'success') {
      newFeeling.value = ''
      window.$toast.add('Sentimento enviado ao Clã!', 'success')
      await fetchClanFeelings()
    }
  } catch (err) {
    window.$toast.add('Erro ao postar no clã.', 'error')
  } finally {
    posting.value = false
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function clan_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'Clan')}&backgroundColor=1e293b,334155,0f172a`
}

function getRoleBadge(role) {
  if (role === 'rei') return { label: 'REI', class: 'bg-warning' }
  if (role === 'lider') return { label: 'LÍDER', class: 'bg-info' }
  return { label: 'ALDEÃO', class: 'bg-secondary' }
}
</script>

<template>
  <div class="premium-clan-detail">
    <div v-if="loading" class="loader-container">
      <div class="spinner-premium"></div>
    </div>

    <template v-else-if="clan">
      <!-- Main Layout: 2 colunas, sem banner herói grande e igual ao feed da Home -->
      <div class="container py-5">
        <!-- Tabs para Mobile -->
        <div class="mobile-tabs-container d-lg-none d-flex mb-4">
          <button 
            class="mobile-tab-btn" 
            :class="{ active: activeMobileTab === 'feed' }" 
            @click="activeMobileTab = 'feed'"
          >
            <i class="bi bi-chat-quote-fill me-2"></i>Mural
          </button>
          <button 
            class="mobile-tab-btn" 
            :class="{ active: activeMobileTab === 'about' }" 
            @click="activeMobileTab = 'about'"
          >
            <i class="bi bi-info-circle-fill me-2"></i>Sobre
          </button>
        </div>

        <div class="row g-4">
          <!-- Coluna Esquerda: Informações + Status do Clã (Visível no desktop ou aba Sobre no celular) -->
          <div class="col-lg-4 col-12" :class="{ 'd-none d-lg-block': activeMobileTab !== 'about' }">
            <!-- Bloco Info Clã -->
            <div class="glass-card overflow-hidden mb-4">
              <!-- Capa em miniatura -->
              <div class="mini-cover">
                <img src="https://images.unsplash.com/photo-1533107862482-0e6974b06ec4?q=80&w=1974" alt="Banner" class="mini-cover-img">
                <div class="mini-cover-mask"></div>
              </div>

              <!-- Conteúdo da Info -->
              <div class="p-4 pt-0 text-center position-relative">
                <div class="clan-logo-wrapper shadow-lg mx-auto">
                  <img :src="clan_pic_url(clan.name_clan)" class="clan-logo-img" alt="Logo">
                </div>

                <div class="mt-3">
                  <h3 class="clan-name-text text-white fw-bold mb-1">{{ clan.name_clan }}</h3>
                  <span class="badge-visibility d-inline-block mb-3" :class="clan.visibility">{{ clan.visibility }}</span>
                  <p class="clan-desc-text text-muted text-start">{{ clan.description || 'Uma união lendária no fell.it.' }}</p>
                </div>

                <div class="clan-actions-wrapper mt-4 pt-3 border-top border-white-5">
                  <router-link v-if="viewerRole === 'rei'" :to="`/clan/${clanId}/manage`" class="btn-premium-outline w-100 d-block text-center mb-2">
                    <i class="bi bi-gear-fill me-2"></i>Gerenciar Clã
                  </router-link>
                  <button 
                    v-if="!viewerRole" 
                    class="btn-premium-action w-100 py-3" 
                    @click="joinClan"
                    :disabled="joining"
                  >
                    <i class="bi bi-shield-lock-fill me-2"></i>Participar do Clã
                  </button>
                  <span v-else class="badge-member-label d-block text-center py-3">
                    <i class="bi bi-check-circle-fill me-2"></i>VOCÊ É MEMBRO
                  </span>
                </div>
              </div>
            </div>

            <!-- Bloco Status Clã -->
            <div class="glass-card p-4">
              <h5 class="fw-bold text-white mb-4"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Status do Clã</h5>
              <div class="stat-list">
                <div class="stat-row">
                  <div class="stat-icon"><i class="bi bi-activity"></i></div>
                  <div class="stat-info text-start">
                    <span class="label">Nível de Atividade</span>
                    <span class="value text-success">Muito Alto</span>
                  </div>
                </div>
                <div class="stat-row">
                  <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
                  <div class="stat-info text-start">
                    <span class="label">Fundado em</span>
                    <span class="value">Maio, 2024</span>
                  </div>
                </div>
                <div class="stat-row">
                  <div class="stat-icon"><i class="bi bi-globe"></i></div>
                  <div class="stat-info text-start">
                    <span class="label">Região</span>
                    <span class="value">Global / fell.it</span>
                  </div>
                </div>
              </div>
              
              <div class="mt-4 pt-3 border-top border-white-5">
                <p class="text-muted small italic text-start">O poder deste clã reside na união dos seus membros e na pureza dos seus sentimentos compartilhados.</p>
              </div>
            </div>

            <!-- Integrantes (Exibido na aba 'Sobre' no celular) -->
            <div class="glass-card p-4 mt-4 d-lg-none">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Integrantes</h5>
                <span class="text-muted small">{{ members.length }} guerreiros</span>
              </div>
              
              <div class="member-grid">
                <div v-for="member in members" :key="member.user_id" class="member-card-premium">
                  <div class="d-flex align-items-center gap-3">
                    <img :src="avatar_url(member.first_name + (member.last_name ? ' ' + member.last_name : ''))" class="member-avatar" alt="Avatar">
                    <div class="flex-grow-1 text-start">
                      <h6 class="mb-0 text-white fw-bold">{{ member.first_name }} {{ member.last_name }}</h6>
                      <span class="role-badge" :class="getRoleBadge(member.role).class">
                        {{ getRoleBadge(member.role).label }}
                      </span>
                    </div>
                    <router-link :to="`/user/${member.user_id}`" class="btn-view-member">
                      <i class="bi bi-arrow-right"></i>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Coluna Direita: Mural + Feed + Integrantes (Desktop) (Visível no desktop ou aba Mural no celular) -->
          <div class="col-lg-8 col-12" :class="{ 'd-none d-lg-block': activeMobileTab !== 'feed' }">
            <!-- Posting Area (Only for members) -->
            <div v-if="viewerRole" class="glass-card p-4 mb-4 shadow animate__animated animate__fadeIn">
               <h6 class="text-white-50 mb-3 small fw-bold text-start">MURAL DO CLÃ</h6>
               <div class="d-flex gap-3">
                 <div class="flex-grow-1">
                   <textarea 
                     v-model="newFeeling" 
                     class="clan-post-textarea" 
                     :placeholder="'O que o clã ' + clan.name_clan + ' precisa saber?'"
                     rows="3"
                   ></textarea>
                   <div class="d-flex justify-content-end mt-3">
                      <button class="btn-premium-action py-2 px-4" @click="shareFeeling" :disabled="posting">
                         <span v-if="!posting">COMPARTILHAR NO CLÃ</span>
                         <span v-else class="spinner-border spinner-border-sm"></span>
                      </button>
                   </div>
                 </div>
               </div>
            </div>

            <!-- Feed do Clã -->
            <div class="clan-feed mb-4">
               <h5 class="fw-bold text-white mb-4 text-start"><i class="bi bi-chat-left-dots-fill me-2 text-primary"></i>Histórias do Império</h5>
               <div v-if="feelings.length > 0">
                 <div v-for="post in feelings" :key="post.feeling_id" class="mb-4 text-start">
                    <PostCard :post="post" />
                 </div>
               </div>
               <div v-else class="glass-card p-5 text-center text-white-50 italic">
                  Ainda não há registros no mural deste clã.
               </div>
            </div>

            <!-- Integrantes (Visível no desktop na coluna principal) -->
            <div class="glass-card p-4 d-none d-lg-block">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Integrantes</h5>
                <span class="text-muted small">{{ members.length }} guerreiros</span>
              </div>
              
              <div class="member-grid">
                <div v-for="member in members" :key="member.user_id" class="member-card-premium">
                  <div class="d-flex align-items-center gap-3">
                    <img :src="avatar_url(member.first_name + (member.last_name ? ' ' + member.last_name : ''))" class="member-avatar" alt="Avatar">
                    <div class="flex-grow-1 text-start">
                      <h6 class="mb-0 text-white fw-bold">{{ member.first_name }} {{ member.last_name }}</h6>
                      <span class="role-badge" :class="getRoleBadge(member.role).class">
                        {{ getRoleBadge(member.role).label }}
                      </span>
                    </div>
                    <router-link :to="`/user/${member.user_id}`" class="btn-view-member">
                      <i class="bi bi-arrow-right"></i>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-5">
      <div class="empty-state">
        <i class="bi bi-shield-slash fs-1 text-muted mb-3"></i>
        <h2 class="text-white">Clã não encontrado.</h2>
        <router-link to="/home" class="btn-premium-outline mt-3">Voltar ao Início</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.premium-clan-detail {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  color: #f8fafc;
  padding-bottom: 5rem;
}

.loader-container { display: flex; justify-content: center; align-items: center; height: 100vh; }
.spinner-premium { width: 50px; height: 50px; border: 3px solid rgba(99, 102, 241, 0.1); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Mini Cover inside Left Card */
.mini-cover {
  height: 150px;
  position: relative;
  overflow: hidden;
}

.mini-cover-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: brightness(0.6);
}

.mini-cover-mask {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, transparent, rgba(15, 23, 42, 0.95));
}

/* Clan Logo overlay mini-cover */
.clan-logo-wrapper {
  width: 120px;
  height: 120px;
  border-radius: 30px;
  overflow: hidden;
  border: 4px solid #050810;
  margin-top: -60px;
  z-index: 10;
  position: relative;
}
.clan-logo-img { width: 100%; height: 100%; object-fit: cover; }

.clan-name-text { font-size: 1.8rem; letter-spacing: -1.2px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
.clan-desc-text { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; }

.badge-visibility { font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; }
.badge-visibility.public { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }

/* Posting */
.clan-post-textarea {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 15px;
  color: white;
  font-size: 1rem;
  transition: all 0.3s;
  resize: none;
}
.clan-post-textarea:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.06);
  border-color: #6366f1;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}

/* Actions */
.btn-premium-action { background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; padding: 12px 28px; border-radius: 16px; color: white; font-weight: 700; transition: all 0.3s; }
.btn-premium-outline { background: transparent; border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px 28px; border-radius: 16px; color: white; font-weight: 700; transition: all 0.3s; text-decoration: none; }
.btn-premium-action:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4); }

.badge-member-label { background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); padding: 12px 24px; border-radius: 16px; font-weight: 700; }

/* Member List */
.member-grid { display: grid; gap: 1rem; }
.member-card-premium { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; padding: 12px 20px; transition: all 0.3s; }
.member-card-premium:hover { background: rgba(255, 255, 255, 0.06); transform: translateX(5px); }
.member-avatar { width: 50px; height: 50px; border-radius: 14px; object-fit: cover; }

.role-badge { font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; letter-spacing: 0.5px; }
.btn-view-member { color: #64748b; font-size: 1.2rem; transition: color 0.2s; }
.btn-view-member:hover { color: #818cf8; }

/* Stats Sidebar */
.glass-card { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 24px; }
.stat-row { display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem; }
.stat-icon { width: 40px; height: 40px; background: rgba(99, 102, 241, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #818cf8; font-size: 1.2rem; }
.stat-info .label { display: block; font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
.stat-info .value { font-size: 1.1rem; font-weight: 600; color: #f1f5f9; }

.border-white-5 { border-color: rgba(255, 255, 255, 0.05) !important; }

/* Mobile Tabs Styling */
.mobile-tabs-container {
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 6px;
  width: 100%;
}

.mobile-tab-btn {
  flex: 1;
  background: transparent;
  border: none;
  padding: 12px;
  color: #94a3b8;
  font-weight: 700;
  font-size: 0.9rem;
  border-radius: 12px;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.3s;
  gap: 6px;
}

.mobile-tab-btn.active {
  background: rgba(99, 102, 241, 0.15);
  color: #818cf8;
}
</style>
