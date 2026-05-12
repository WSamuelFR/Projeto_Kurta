<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import PostCard from '../components/PostCard.vue'

const router = useRouter()
const feelings = ref([])
const loading = ref(true)
const newFeeling = ref('')
const posting = ref(false)
const trending = ref([])
const user = ref(JSON.parse(localStorage.getItem('fellit_user') || '{}'))

onMounted(() => {
  if (!user.value.id) {
    router.push('/login')
    return
  }
  fetchFeed()
  fetchTrending()
})

async function fetchFeed() {
  loading.value = true
  try {
    console.log('Buscando feed global para o visitante:', user.value.id);
    const res = await axios.get('/api/feelings/global', {
      params: { 
        visitorId: user.value.id || 0,
        _t: Date.now()
      }
    })
    console.log('Resultado do feed:', res.data);
    if (res.data.status === 'success') {
      feelings.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao carregar feed:', err)
    window.$toast.add('Erro ao conectar com o feed.', 'error')
  } finally {
    loading.value = false
  }
}

async function fetchTrending() {
  try {
    const res = await axios.get('/api/feelings/trending', {
      params: { _t: Date.now() }
    })
    if (res.data.status === 'success') {
      trending.value = res.data.data
    }
  } catch (err) {
    console.error('Erro ao buscar trending:', err)
  }
}

async function shareFeeling() {
  if (!newFeeling.value.trim()) return
  
  posting.value = true
  try {
    const res = await axios.post('/api/feelings/create', {
      feeling: newFeeling.value,
      user_id: user.value.id,
      visibility: 'public'
    })
    
    if (res.data.status === 'success') {
      newFeeling.value = ''
      window.$toast.add('Sentimento compartilhado!', 'success')
      await fetchFeed() 
    } else {
      window.$toast.add(res.data.message || 'Erro ao compartilhar.', 'error')
    }
  } catch (err) {
    console.error('ERRO AO POSTAR FEELING:', err);
    window.$toast.add('Não foi possível postar seu sentimento.', 'error')
  } finally {
    posting.value = false
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}
</script>

<template>
  <div class="premium-home">
    <div class="container py-5">
      <div class="row g-4">
        <!-- Sidebar Left -->
        <div class="col-lg-3 d-none d-lg-block">
          <div class="glass-card p-4 sticky-top" style="top: 100px;">
            <div class="profile-mini text-center mb-4">
              <img :src="avatar_url(user.first_name + (user.last_name ? ' ' + user.last_name : ''))" class="avatar-sm mb-3 shadow" alt="Me">
              <h6 class="fw-bold text-white mb-0">{{ user.first_name }}</h6>
              <p class="text-muted small">@{{ user.first_name?.toLowerCase() }}</p>
            </div>
            <nav class="side-nav">
              <router-link to="/home" class="nav-item-premium active">
                <i class="bi bi-house-door-fill"></i> Início
              </router-link>
              <router-link to="/profile" class="nav-item-premium">
                <i class="bi bi-person-fill"></i> Perfil
              </router-link>
              <router-link to="/search" class="nav-item-premium">
                <i class="bi bi-compass-fill"></i> Explorar
              </router-link>
              <router-link to="/clans" class="nav-item-premium">
                <i class="bi bi-shield-shaded"></i> Clãs
              </router-link>
            </nav>
          </div>
        </div>

        <!-- Feed Center -->
        <div class="col-lg-6">
          <!-- Create Post -->
          <div class="glass-card p-4 mb-4 animate__animated animate__fadeInDown">
            <div class="d-flex gap-3">
              <img :src="avatar_url(user.first_name + (user.last_name ? ' ' + user.last_name : ''))" class="avatar-xs" alt="User">
              <div class="flex-grow-1">
                <textarea 
                  v-model="newFeeling" 
                  class="premium-textarea" 
                  rows="3" 
                  placeholder="O que você está sentindo agora?"
                ></textarea>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-white-50">
                  <div class="d-flex gap-2 text-muted">
                    <button class="btn-icon"><i class="bi bi-image"></i></button>
                    <button class="btn-icon"><i class="bi bi-emoji-smile"></i></button>
                  </div>
                  <button 
                    @click="shareFeeling" 
                    class="btn-premium-sm" 
                    :disabled="posting || !newFeeling.trim()"
                  >
                    <span v-if="posting" class="spinner-border spinner-border-sm me-1"></span>
                    Postar
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Feed List -->
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-premium"></div>
          </div>

          <div v-else-if="feelings.length === 0" class="empty-feed text-center py-5">
            <i class="bi bi-moon-stars fs-1 mb-3"></i>
            <p>O silêncio é profundo por aqui... <br>Que tal compartilhar o primeiro sentimento?</p>
          </div>

          <div v-else class="feed-container animate__animated animate__fadeIn">
            <PostCard v-for="post in feelings" :key="post.feeling_id" :post="post" />
          </div>
        </div>

        <!-- Sidebar Right -->
        <div class="col-lg-3 d-none d-lg-block">
          <div class="glass-card p-4 sticky-top" style="top: 100px;">
            <h6 class="fw-bold text-white mb-4"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Trending Top 3</h6>
            <div class="trending-list d-flex flex-column gap-3">
              <div v-for="(t, index) in trending" :key="t.feeling_id" class="trending-item d-flex align-items-center gap-3">
                <div class="rank-number">{{ index + 1 }}</div>
                <div class="flex-grow-1 overflow-hidden">
                  <p class="mb-0 text-white small fw-bold text-truncate">{{ t.feeling }}</p>
                  <div class="d-flex gap-2 extra-small text-muted">
                    <span><i class="bi bi-heart-fill text-danger me-1"></i>{{ t.total_likes }}</span>
                    <span><i class="bi bi-chat-fill text-primary me-1"></i>{{ t.total_comments }}</span>
                  </div>
                </div>
              </div>
              <div v-if="trending.length === 0" class="empty-state py-3">
                <p class="text-muted small italic">Nenhum assunto em alta no momento.</p>
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

.premium-home {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  color: #f8fafc;
}

.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
}

/* Avatars */
.avatar-sm { width: 80px; height: 80px; border-radius: 25px; object-fit: cover; border: 3px solid rgba(99, 102, 241, 0.2); }
.avatar-xs { width: 45px; height: 45px; border-radius: 12px; object-fit: cover; }

/* Nav Items */
.nav-item-premium {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #94a3b8;
  text-decoration: none;
  font-weight: 600;
  border-radius: 14px;
  transition: all 0.3s;
  margin-bottom: 5px;
}

.nav-item-premium i { font-size: 1.2rem; }
.nav-item-premium:hover, .nav-item-premium.active {
  background: rgba(99, 102, 241, 0.1);
  color: #818cf8;
}

/* Post Area */
.premium-textarea {
  width: 100%;
  background: transparent;
  border: none;
  color: white;
  font-size: 1.1rem;
  resize: none;
}

.premium-textarea:focus { outline: none; }

.btn-icon {
  background: transparent;
  border: none;
  color: #64748b;
  font-size: 1.2rem;
  padding: 5px 10px;
  border-radius: 10px;
  transition: all 0.2s;
}

.btn-icon:hover { background: rgba(255, 255, 255, 0.05); color: white; }

.btn-premium-sm {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border: none;
  padding: 8px 24px;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  transition: all 0.3s;
}

.btn-premium-sm:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4); }

/* Loader */
.spinner-premium {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(99, 102, 241, 0.1);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 2rem auto;
}

.feed-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

@keyframes spin { to { transform: rotate(360deg); } }

.empty-feed {
  color: #64748b;
  font-style: italic;
}

.trending-item {
  padding: 10px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.02);
  transition: background 0.3s;
}

.trending-item:hover {
  background: rgba(255, 255, 255, 0.05);
}

.rank-number {
  font-size: 1.2rem;
  font-weight: 900;
  color: #6366f1;
  opacity: 0.5;
  min-width: 25px;
}

.extra-small {
  font-size: 0.75rem;
}
</style>
