<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const alertMessage = ref('')
const alertType = ref('') 
const loading = ref(false)
const router = useRouter()

async function handleLogin() {
  if (!email.value || !password.value) {
    showAlert('Preencha os campos para mergulhar.', 'error')
    return
  }

  loading.value = true
  alertMessage.value = ''

  try {
    const response = await axios.post('/api/auth/login', {
      email: email.value,
      password: password.value
    })

    if (response.data.status === 'success') {
      localStorage.setItem('fellit_token', response.data.token)
      localStorage.setItem('fellit_user', JSON.stringify(response.data.user))
      showAlert('Conexão estabelecida! Entrando...', 'success')
      setTimeout(() => {
        router.push('/home')
      }, 800)
    } else {
      showAlert(response.data.message || 'Credenciais inválidas.', 'error')
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Erro de conexão com a rede.'
    showAlert(msg, 'error')
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
  <div class="premium-login">
    <!-- Efeitos de Fundo Dinâmicos -->
    <div class="blob-container">
      <div class="blob blob-purple"></div>
      <div class="blob blob-blue"></div>
      <div class="blob blob-pink"></div>
    </div>

    <div class="content-wrapper animate__animated animate__zoomIn">
      <div class="glass-container">
        <div class="branding text-center mb-5">
          <div class="logo-icon mb-3">
            <i class="bi bi-heart-pulse-fill"></i>
          </div>
          <h1 class="brand-name">feel.it</h1>
          <p class="brand-tagline">Conecte-se com a sua essência.</p>
        </div>

        <form @submit.prevent="handleLogin" class="modern-form">
          <div class="input-group-modern mb-4">
            <label>E-mail</label>
            <div class="input-wrapper">
              <i class="bi bi-envelope"></i>
              <input type="email" v-model="email" placeholder="nome@exemplo.com" required>
            </div>
          </div>

          <div class="input-group-modern mb-3">
            <label>Senha</label>
            <div class="input-wrapper">
              <i class="bi bi-lock"></i>
              <input type="password" v-model="password" placeholder="Sua senha secreta" required>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-5 px-1">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember">
              <label class="form-check-label small text-white-50" for="remember">Lembrar de mim</label>
            </div>
            <a href="#" class="forgot-link small">Esqueceu?</a>
          </div>

          <div v-if="alertMessage" :class="['modern-alert', alertType]">
             <i :class="alertType === 'error' ? 'bi bi-exclamation-circle' : 'bi bi-check-circle'"></i>
             {{ alertMessage }}
          </div>

          <button type="submit" class="btn-premium" :disabled="loading">
            <span v-if="!loading">Entrar no Fluxo</span>
            <span v-else class="spinner-border spinner-border-sm"></span>
          </button>
        </form>

        <div class="footer-action mt-5 text-center">
          <p class="text-white-50 small">
            Novo por aqui? 
            <router-link to="/register" class="signup-link">Criar conta gratuita</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.premium-login {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  overflow: hidden;
}

/* Blobs Animados */
.blob-container {
  position: absolute;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.5;
  animation: float 20s infinite alternate;
}

.blob-purple {
  width: 500px;
  height: 500px;
  background: #7c3aed;
  top: -10%;
  right: -5%;
}

.blob-blue {
  width: 400px;
  height: 400px;
  background: #2563eb;
  bottom: -10%;
  left: -5%;
  animation-delay: -5s;
}

.blob-pink {
  width: 300px;
  height: 300px;
  background: #db2777;
  top: 40%;
  left: 20%;
  animation-delay: -10s;
}

@keyframes float {
  0% { transform: translate(0, 0) scale(1); }
  100% { transform: translate(100px, 50px) scale(1.2); }
}

/* Glassmorphism Card */
.glass-container {
  position: relative;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 32px;
  padding: 4rem 3rem;
  width: 100%;
  max-width: 460px;
  z-index: 1;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
}

.logo-icon {
  font-size: 2.5rem;
  color: #60a5fa;
  filter: drop-shadow(0 0 10px rgba(96, 165, 250, 0.5));
}

.brand-name {
  font-size: 3rem;
  font-weight: 700;
  color: white;
  letter-spacing: -2px;
  margin-bottom: 0;
}

.brand-tagline {
  color: #94a3b8;
  font-weight: 300;
}

/* Form Styling */
.input-group-modern label {
  display: block;
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 8px;
  padding-left: 5px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper i {
  position: absolute;
  left: 15px;
  color: #64748b;
  font-size: 1.1rem;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 14px 14px 45px;
  background: rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 14px;
  color: white;
  transition: all 0.3s;
}

.input-wrapper input:focus {
  outline: none;
  border-color: #6366f1;
  background: rgba(2, 6, 23, 0.6);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.forgot-link, .signup-link {
  color: #6366f1;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s;
}

.signup-link:hover { color: #818cf8; text-decoration: underline; }

.btn-premium {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border: none;
  border-radius: 16px;
  color: white;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.3s;
  box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.5);
}

.btn-premium:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.6);
}

.btn-premium:active { transform: translateY(0); }

/* Alerts */
.modern-alert {
  padding: 12px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.modern-alert.error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

.modern-alert.success {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #86efac;
}
</style>

