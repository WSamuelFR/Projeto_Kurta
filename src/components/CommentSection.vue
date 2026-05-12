<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import CommentItem from './CommentItem.vue'

const props = defineProps({
  feelingId: Number
})

const comments = ref([])
const loading = ref(true)
const newComment = ref('')
const posting = ref(false)

onMounted(fetchComments)

async function fetchComments() {
  try {
    const res = await axios.get('/api/comments', {
      params: { feeling_id: props.feelingId }
    })
    if (res.data.status === 'success') {
      comments.value = buildTree(res.data.data)
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function buildTree(list) {
  const map = {}
  const roots = []
  
  list.forEach(item => {
    item.replies = []
    map[item.coment_id] = item
  })
  
  list.forEach(item => {
    if (item.parent_id) {
      if (map[item.parent_id]) {
        map[item.parent_id].replies.push(item)
      }
    } else {
      roots.push(item)
    }
  })
  
  return roots
}

async function sendComment() {
  if (!newComment.value.trim()) return
  
  const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
  if (!user.id) {
    window.$toast.add('Você precisa estar logado para comentar.', 'error')
    return
  }

  posting.value = true
  try {
    const res = await axios.post('/api/comments/add', {
      feeling_id: props.feelingId,
      user_id: user.id,
      coment: newComment.value
    })
    
    if (res.data.status === 'success') {
      newComment.value = ''
      window.$toast.add('Comentário enviado!', 'success')
      fetchComments()
    }
  } catch (err) {
    console.error(err)
    window.$toast.add('Erro ao comentar.', 'error')
  } finally {
    posting.value = false
  }
}
</script>

<template>
  <div class="comment-section-premium p-3">
    <!-- Novo Comentário -->
    <div class="comment-input-wrapper mb-4">
      <div class="d-flex align-items-center gap-3">
        <div class="flex-grow-1 position-relative">
          <input 
            v-model="newComment" 
            class="premium-comment-input" 
            placeholder="Escreva algo brilhante..."
            @keyup.enter="sendComment"
          >
          <div class="input-glow"></div>
        </div>
        <button class="btn-send-premium" @click="sendComment" :disabled="posting || !newComment.trim()">
          <i class="bi bi-send-fill" v-if="!posting"></i>
          <span v-else class="spinner-border spinner-border-sm"></span>
        </button>
      </div>
    </div>

    <!-- Lista de Comentários -->
    <div v-if="loading" class="text-center py-4">
      <div class="spinner-premium-sm"></div>
    </div>
    
    <div v-else-if="comments.length === 0" class="empty-comments">
       <p>Nenhum eco por aqui... Seja o primeiro!</p>
    </div>

    <div v-else class="comments-scroll">
      <CommentItem 
        v-for="comment in comments" 
        :key="comment.coment_id" 
        :comment="comment" 
        :feeling-id="feelingId"
        @refresh="fetchComments"
      />
    </div>
  </div>
</template>

<style scoped>
.comment-section-premium {
  background: rgba(0, 0, 0, 0.2);
  border-radius: 0 0 24px 24px;
}

.comment-input-wrapper {
  position: relative;
  z-index: 1;
}

.premium-comment-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 12px 20px;
  color: white;
  font-size: 0.95rem;
  transition: all 0.3s;
}

.premium-comment-input:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.06);
  border-color: #6366f1;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}

.btn-send-premium {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  border: none;
  width: 45px;
  height: 45px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.btn-send-premium:hover:not(:disabled) {
  transform: scale(1.05) translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.btn-send-premium:disabled {
  opacity: 0.5;
  filter: grayscale(1);
}

.comments-scroll {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
  text-align: left;
}

.comments-scroll::-webkit-scrollbar { width: 4px; }
.comments-scroll::-webkit-scrollbar-track { background: transparent; }
.comments-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}

.empty-comments {
  text-align: center;
  color: #64748b;
  font-size: 0.9rem;
  padding: 20px 0;
  font-style: italic;
}

.spinner-premium-sm {
  width: 25px;
  height: 25px;
  border: 2px solid rgba(99, 102, 241, 0.1);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
