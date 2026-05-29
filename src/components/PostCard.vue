<script setup>
import { ref } from 'vue'
import axios from 'axios'
import CommentSection from './CommentSection.vue'
import ShareModal from './ShareModal.vue'
import { encodeId } from '../utils/obfuscator'

const props = defineProps({
  post: Object
})

const localLikes = ref(props.post.total_likes || 0)
const liked = ref(props.post.user_liked > 0)
const showComments = ref(false)
const showShareModal = ref(false)

async function toggleLike() {
  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!user.id) return

  try {
    const res = await axios.post('/api/feelings/like', {
      feeling_id: props.post.feeling_id,
      user_id: user.id,
      is_clan: !!props.post.cla_id
    })
    if (res.data.status === 'success') {
      if (res.data.action === 'liked') {
        localLikes.value++
        liked.value = true
      } else {
        localLikes.value--
        liked.value = false
      }
    }
  } catch (err) {
    console.error('Erro ao curtir:', err)
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function formatDate(dateStr) {
  if (!dateStr) return 'Agora'
  const date = new Date(dateStr)
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <div class="feeling-card glass-card animate__animated animate__fadeInUp">
    <!-- Header -->
    <div class="card-header-premium p-3 d-flex align-items-center gap-3">
      <img :src="avatar_url(post.first_name + (post.last_name ? ' ' + post.last_name : ''))" class="author-avatar shadow" alt="Avatar">
      <div class="flex-grow-1 text-start">
        <router-link :to="'/user/' + encodeId(post.user_id)" class="author-name-link">
          <h6 class="mb-0 fw-bold">{{ post.first_name }} {{ post.last_name }}</h6>
        </router-link>
        <span class="text-muted extra-small">{{ formatDate(post.created_at) }}</span>
      </div>
      <div class="options">
        <button class="btn-dots"><i class="bi bi-three-dots-vertical"></i></button>
      </div>
    </div>

    <!-- Content -->
    <div class="card-body-premium px-4 pb-3 text-start">
      <p class="feeling-text">{{ post.feeling }}</p>
      
      <!-- Quote Box -->
      <div v-if="post.original_feeling" class="quote-box p-3 mt-3">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-quote text-primary"></i>
          <span class="text-primary small fw-bold">{{ post.original_author_name }}</span>
        </div>
        <p class="text-white-50 small mb-0">{{ post.original_feeling }}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="card-footer-premium px-3 py-2 d-flex justify-content-between align-items-center border-top border-white-5">
      <div class="d-flex gap-2">
        <button 
          @click="toggleLike" 
          class="interaction-btn" 
          :class="{ 'active-like': liked }"
        >
          <i :class="liked ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
          <span>{{ localLikes }}</span>
        </button>
        
        <button 
          @click="showComments = !showComments" 
          class="interaction-btn"
          :class="{ 'active-comment': showComments }"
        >
          <i class="bi bi-chat-text"></i>
          <span>{{ post.total_comments || 0 }}</span>
        </button>
      </div>

      <button class="interaction-btn" @click="showShareModal = true">
        <i class="bi bi-share"></i>
      </button>
    </div>

    <!-- Comments Section -->
    <div v-if="showComments" class="comment-area-wrapper border-top border-white-5 animate__animated animate__slideInDown">
      <CommentSection :feeling-id="post.feeling_id" :is-clan="!!post.cla_id" />
    </div>

    <!-- Modals -->
    <ShareModal v-if="showShareModal" :post="post" @close="showShareModal = false" />
  </div>
</template>

<style scoped>
.feeling-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}

.feeling-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
}

.author-avatar {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  object-fit: cover;
}

.author-name-link {
  text-decoration: none;
  color: white;
  transition: color 0.2s;
}

.author-name-link:hover { color: #818cf8; }

.extra-small { font-size: 0.75rem; }

.feeling-text {
  font-size: 1.1rem;
  line-height: 1.6;
  color: #e2e8f0;
  white-space: pre-wrap;
}

.quote-box {
  background: rgba(255, 255, 255, 0.03);
  border-left: 3px solid #6366f1;
  border-radius: 12px;
}

.interaction-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 8px 14px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.interaction-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.active-like { color: #f43f5e !important; }
.active-like i { filter: drop-shadow(0 0 5px rgba(244, 63, 94, 0.5)); }

.active-comment { color: #6366f1 !important; }

.btn-dots {
  background: transparent;
  border: none;
  color: #64748b;
  padding: 5px;
}

.border-white-5 { border-color: rgba(255, 255, 255, 0.05) !important; }
</style>
