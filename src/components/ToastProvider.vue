<script setup>
import { ref, onMounted } from 'vue'

const toasts = ref([])

function add(message, type = 'info', duration = 4000) {
  const id = Date.now()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    remove(id)
  }, duration)
}

function remove(id) {
  toasts.value = toasts.value.filter(t => t.id !== id)
}

// Expor para uso global (via window por simplicidade ou injeção)
window.$toast = { add }
</script>

<template>
  <div class="toast-container-premium">
    <TransitionGroup name="toast">
      <div 
        v-for="toast in toasts" 
        :key="toast.id" 
        class="toast-item-premium"
        :class="toast.type"
      >
        <div class="toast-content">
          <i v-if="toast.type === 'success'" class="bi bi-check-circle-fill"></i>
          <i v-else-if="toast.type === 'error'" class="bi bi-exclamation-triangle-fill"></i>
          <i v-else class="bi bi-info-circle-fill"></i>
          <span>{{ toast.message }}</span>
        </div>
        <button class="btn-close-toast" @click="remove(toast.id)">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-container-premium {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 10001;
  display: flex;
  flex-direction: column-reverse;
  gap: 12px;
  pointer-events: none;
}

.toast-item-premium {
  pointer-events: auto;
  min-width: 300px;
  max-width: 450px;
  padding: 16px 20px;
  border-radius: 18px;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 500;
  font-size: 0.95rem;
}

.toast-item-premium.success { border-left: 4px solid #22c55e; }
.toast-item-premium.error { border-left: 4px solid #ef4444; }
.toast-item-premium.info { border-left: 4px solid #3b82f6; }

.toast-item-premium.success i { color: #22c55e; }
.toast-item-premium.error i { color: #ef4444; }
.toast-item-premium.info i { color: #3b82f6; }

.btn-close-toast {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.4);
  font-size: 1.2rem;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-close-toast:hover { color: white; }

/* Animações */
.toast-enter-active, .toast-leave-active {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100px) scale(0.8);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(50px) scale(0.9);
}
</style>
