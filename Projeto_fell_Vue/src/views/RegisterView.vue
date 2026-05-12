<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const firstName = ref('')
const lastName = ref('')
const phone = ref('')
const email = ref('')
const password = ref('')
const alertMessage = ref('')
const alertType = ref('')
const loading = ref(false)
const router = useRouter()

async function handleRegister() {
  if (!firstName.value || !email.value || !password.value) {
    showAlert('Nome, E-mail e Senha são obrigatórios.', 'error')
    return
  }

  loading.value = true
  alertMessage.value = ''

  try {
    const response = await axios.post('/app/controller/auth_controller/cadastro_process.php', {
      first_name: firstName.value,
      last_name: lastName.value,
      phone: phone.value,
      email: email.value,
      password: password.value
    })

    if (response.data.success) {
      showAlert(response.data.message, 'success')
      setTimeout(() => {
        router.push('/login')
      }, 1500)
    } else {
      showAlert(response.data.message || 'Erro ao realizar cadastro.', 'error')
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Erro de conexão com o servidor.'
    showAlert(msg, 'error')
    console.error('Register error:', error)
  } finally {
    loading.value = false
  }
}

function showAlert(message, type) {
  alertMessage.value = message
  alertType.value = type
}
</script>

<template>
  <div class="login-container">
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <div class="glass-card">
      <div class="logo-area mb-4">
        <h1>feel.it</h1>
        <p>Crie sua nova conta abaixo</p>
      </div>

      <form @submit.prevent="handleRegister" class="login-form">
        <div class="row mb-3">
          <div class="col-6">
            <label class="form-label text-muted small fw-bold">Primeiro Nome</label>
            <input type="text" v-model="firstName" class="form-control custom-input" placeholder="Ex: Lucas" required>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small fw-bold">Sobrenome</label>
            <input type="text" v-model="lastName" class="form-control custom-input" placeholder="Ex: Silva">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted small fw-bold">Telefone</label>
          <input type="tel" v-model="phone" class="form-control custom-input" placeholder="(11) 90000-0000">
        </div>

        <div class="mb-3">
          <label class="form-label text-muted small fw-bold">E-mail</label>
          <input type="email" v-model="email" class="form-control custom-input" placeholder="seu@email.com" required>
        </div>

        <div class="mb-4">
          <label class="form-label text-muted small fw-bold">Crie uma Senha</label>
          <input type="password" v-model="password" class="form-control custom-input" placeholder="••••••••" required>
        </div>

        <div v-if="alertMessage" :class="['alert-box', alertType === 'error' ? 'alert-error' : 'alert-success']">
          {{ alertMessage }}
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-2 fw-bold" :disabled="loading">
          <span v-if="!loading">Inscrever-se</span>
          <span v-else class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
      </form>

      <div class="register-area mt-4 text-center">
        <p class="text-muted small">
          Já possui uma conta? 
          <router-link to="/login" class="fw-bold text-primary-custom">Faça Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Reaproveitando os estilos base do LoginView */
.login-container {
    position: relative;
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #0f172a;
    overflow: hidden;
}

.blob-1, .blob-2 {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.6;
    animation: float 10s ease-in-out infinite alternate;
}

.blob-1 { width: 400px; height: 400px; background: #4f46e5; top: -10%; left: -10%; }
.blob-2 { width: 300px; height: 300px; background: #ec4899; bottom: -10%; right: -5%; animation-delay: -5s; }

@keyframes float {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(50px, 50px) scale(1.1); }
}

.glass-card {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 480px;
    z-index: 1;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    animation: slideUp 0.8s forwards;
}

@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.logo-area h1 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #60a5fa, #c084fc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.2rem;
}

.custom-input {
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #f8fafc;
    border-radius: 10px;
}

.custom-input:focus {
    background: rgba(15, 23, 42, 0.8);
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    color: white;
}

.btn-primary-custom {
    background: linear-gradient(to right, #6366f1, #4f46e5);
    border: none;
    color: white;
}

.btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4); color: white; }
.text-primary-custom { color: #6366f1; }

.alert-box { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; text-align: center; }
.alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; }
.alert-success { background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #86efac; }
</style>
