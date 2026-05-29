<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  comment: Object,
  feelingId: Number,
  isClan: Boolean
})

const emit = defineEmits(['refresh'])

const showReplyInput = ref(false)
const replyText = ref('')
const posting = ref(false)

async function sendReply() {
  if (!replyText.value.trim()) return
  
  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!user.id) {
    window.$toast.add('Você precisa estar logado para responder.', 'error')
    return
  }

  posting.value = true
  try {
    const res = await axios.post('/api/comments/add', {
      feeling_id: props.feelingId,
      user_id: user.id,
      coment: replyText.value,
      parent_id: props.comment.coment_id,
      is_clan: props.isClan
    })
    
    if (res.data.status === 'success') {
      replyText.value = ''
      showReplyInput.value = false
      window.$toast.add('Resposta enviada!', 'success')
      emit('refresh')
    }
  } catch (err) {
    console.error(err)
    window.$toast.add('Erro ao responder.', 'error')
  } finally {
    posting.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}
</script>

<template>
  <div class="comment-item-premium mb-4">
    <div class="d-flex gap-3">
      <img :src="avatar_url(comment.first_name + (comment.last_name ? ' ' + comment.last_name : ''))" class="comment-avatar-premium shadow" alt="Avatar">
      <div class="flex-grow-1 text-start">
        <div class="comment-bubble-premium p-3">
          <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-white-5">
            <h6 class="mb-0 author-name-premium fw-bold">{{ comment.first_name }} {{ comment.last_name }}</h6>
            <span class="text-muted extra-small">{{ formatDate(comment.created_at) }}</span>
          </div>
          <p class="mb-0 text-white-50 small content-text mt-2">{{ comment.coment }}</p>
        </div>
        
        <div class="comment-actions-premium mt-2 d-flex gap-4 px-2">
          <button class="action-link" @click="showReplyInput = !showReplyInput">
            <i class="bi bi-chat-dots"></i> Responder
          </button>
        </div>

        <!-- Input de Resposta -->
        <div v-if="showReplyInput" class="reply-input-area mt-3 animate__animated animate__fadeIn">
          <div class="d-flex gap-2">
            <input 
              v-model="replyText" 
              class="premium-reply-input" 
              placeholder="Sua resposta..."
              @keyup.enter="sendReply"
            >
            <button class="btn-send-reply" @click="sendReply" :disabled="posting || !replyText.trim()">
              <i class="bi bi-send-fill" v-if="!posting"></i>
              <span v-else class="spinner-border spinner-border-sm"></span>
            </button>
          </div>
        </div>

        <!-- Respostas (Recursão) -->
        <div v-if="comment.replies && comment.replies.length > 0" class="replies-wrapper ms-2 mt-3 ps-3">
          <CommentItem 
            v-for="reply in comment.replies" 
            :key="reply.coment_id" 
            :comment="reply" 
            :feeling-id="feelingId"
            :is-clan="isClan"
            @refresh="emit('refresh')"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.comment-item-premium {
  position: relative;
}

.comment-avatar-premium {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.05);
}

.comment-bubble-premium {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  border-top-left-radius: 4px;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.author-name-premium {
  color: #818cf8 !important;
  font-size: 0.85rem !important;
  letter-spacing: 0.5px;
}

.comment-bubble-premium:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(99, 102, 241, 0.2);
}

.content-text {
  line-height: 1.5;
  color: #cbd5e1 !important;
}

.action-link {
  background: transparent;
  border: none;
  color: #64748b;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 0;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s;
}

.action-link:hover { color: #818cf8; }

.premium-reply-input {
  width: 100%;
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  padding: 8px 15px;
  color: white;
  font-size: 0.85rem;
}
.premium-reply-input:focus { outline: none; border-color: #6366f1; }

.btn-send-reply {
  background: #6366f1;
  color: white;
  border: none;
  width: 35px;
  height: 35px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
}

.replies-wrapper {
  border-left: 2px solid rgba(255, 255, 255, 0.05);
}

.border-white-5 { border-color: rgba(255, 255, 255, 0.05) !important; }

.extra-small { font-size: 0.65rem; }
</style>
