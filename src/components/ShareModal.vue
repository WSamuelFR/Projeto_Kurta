<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  post: Object
})

const emit = defineEmits(['close', 'success'])

const caption = ref('')
const loading = ref(false)

async function confirmShare() {
  loading.value = true
  try {
    const res = await axios.post('/app/controller/feeling_controller/share_feeling.php', {
      feeling_id: props.post.feeling_id,
      caption: caption.value
    })
    
    if (res.data.status === 'success') {
      emit('success')
      emit('close')
    } else {
      alert(res.data.message)
    }
  } catch (err) {
    alert('Erro ao compartilhar.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="modal-overlay d-flex align-items-center justify-content-center p-3">
    <div class="glass-modal p-4 shadow-lg animate__animated animate__zoomIn">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="text-white fw-bold mb-0">Compartilhar Sentimento</h5>
        <button class="btn-close btn-close-white" @click="emit('close')"></button>
      </div>

      <div class="mb-4 text-start">
        <label class="form-label text-muted small fw-bold">Sua Legenda (Opcional)</label>
        <textarea 
          v-model="caption" 
          class="form-control bg-dark border-secondary text-white" 
          rows="3" 
          placeholder="O que você achou disso?"
        ></textarea>
      </div>

      <!-- Preview do Post Original -->
      <div class="original-preview p-3 mb-4 text-start border border-white-50 rounded">
        <div class="d-flex align-items-center gap-2 mb-2">
          <img :src="post.profile_pic ? '/' + post.profile_pic : 'https://api.dicebear.com/7.x/avataaars/svg?seed=Felix'" class="rounded-circle" style="width: 24px; height: 24px;">
          <span class="text-white small fw-bold">{{ post.first_name }}</span>
        </div>
        <p class="text-white-50 small mb-0 text-truncate">{{ post.feeling }}</p>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-outline-light flex-grow-1" @click="emit('close')">Cancelar</button>
        <button class="btn btn-primary flex-grow-1 fw-bold" @click="confirmShare" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
          COMPARTILHAR
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  z-index: 2000;
}

.glass-modal {
  background: rgba(30, 41, 59, 0.9);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 25px;
  width: 100%;
  max-width: 450px;
}

.original-preview {
  background: rgba(255, 255, 255, 0.03);
}
</style>
