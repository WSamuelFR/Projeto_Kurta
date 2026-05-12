<script setup>
import { ref, onMounted } from 'vue'

const message = ref('Verificando conexão...')
const status = ref('loading')

onMounted(async () => {
  try {
    // Tenta uma chamada para o controller de login (simulado ou real)
    // Usamos o proxy configurado no vite.config.js
    const response = await fetch('/app/controller/auth_controller/process_login.php', {
      method: 'POST',
      body: JSON.stringify({}) // Envia vazio só para testar se o arquivo responde
    })
    
    if (response.status === 400 || response.status === 200) {
      status.value = 'success'
      message.value = 'Backend PHP detectado e acessível!'
    } else {
      status.value = 'error'
      message.value = 'Erro ao falar com o PHP (Status: ' + response.status + ')'
    }
  } catch (err) {
    status.value = 'error'
    message.value = 'Falha na conexão com o Backend: ' + err.message
  }
})
</script>

<template>
  <div :class="['status-card', status]">
    <h3>Status do Backend</h3>
    <p>{{ message }}</p>
  </div>
</template>

<style scoped>
.status-card {
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #ccc;
}
.success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
.loading { background: #e2e3e5; color: #383d41; }
</style>
