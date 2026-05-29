<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { encodeId } from '../utils/obfuscator'

const router = useRouter()
const loading = ref(false)
const previewImage = ref(null)
const user = ref(JSON.parse(localStorage.getItem('fellit_user') || '{}'))

onMounted(() => {
  if (!user.value.id) router.push('/login')
})

const clanForm = ref({
  name: '',
  description: '',
  visibility: 'public'
})

async function criarClan() {
  if (!clanForm.value.name) {
    window.$toast.add('Por favor, dê um nome épico ao seu clã!', 'error')
    return
  }

  loading.value = true
  try {
    const res = await axios.post('/api/clans/create', {
      name_clan: clanForm.value.name,
      description: clanForm.value.description,
      visibility: clanForm.value.visibility,
      user_id: user.value.id
    })
    
    if (res.data.status === 'success') {
      window.$toast.add('Seu clã foi criado com sucesso!', 'success')
      router.push(`/clan/${encodeId(res.data.data.clan_id)}`)
    } else {
      window.$toast.add(res.data.message || 'Erro ao criar clã.', 'error')
    }
  } catch (err) {
    console.error(err)
    window.$toast.add('Erro ao criar o clã.', 'error')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="premium-create-clan">
    <!-- Fundo Dinâmico -->
    <div class="blob-bg">
      <div class="blob b1"></div>
      <div class="blob b2"></div>
    </div>

    <div class="container py-5 content-pos">
      <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
          <div class="glass-container p-4 p-md-5 animate__animated animate__fadeInUp">
            <div class="text-center mb-5">
              <div class="icon-badge mb-3">
                <i class="bi bi-shield-shaded"></i>
              </div>
              <h1 class="text-white fw-bold display-6">Novo Clã</h1>
              <p class="text-white-50">Crie uma nova comunidade no fell.it.</p>
            </div>

            <form @submit.prevent="criarClan" class="modern-form">
              <!-- Brasão Automático (Preview) -->
              <div class="text-center mb-4">
                <div class="brasao-preview shadow-lg">
                  <img :src="`https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(clanForm.name || 'Clan')}&backgroundColor=1e293b`" class="preview-img" alt="Logo">
                </div>
                <span class="d-block small text-white-50 mt-2">Logo do Clã</span>
              </div>

              <!-- Inputs -->
              <div class="mb-4">
                <label class="label-premium">Nome do Clã</label>
                <div class="input-glass-wrapper">
                  <i class="bi bi-fonts"></i>
                  <input type="text" v-model="clanForm.name" placeholder="Ex: Cavaleiros da Alvorada" required>
                </div>
              </div>

              <div class="mb-4">
                <label class="label-premium">Visão / Descrição</label>
                <div class="input-glass-wrapper align-items-start pt-3">
                  <i class="bi bi-card-text"></i>
                  <textarea v-model="clanForm.description" rows="4" placeholder="Qual o propósito do seu clã?"></textarea>
                </div>
              </div>

              <div class="mb-5">
                <label class="label-premium">Privacidade</label>
                <div class="btn-toggle-premium">
                  <button 
                    type="button" 
                    class="toggle-btn" 
                    :class="{ active: clanForm.visibility === 'public' }"
                    @click="clanForm.visibility = 'public'"
                  >
                    <i class="bi bi-globe me-2"></i>
                    <span>Público</span>
                    <small class="d-block text-white-50 extra-small mt-1">Qualquer pessoa pode entrar</small>
                  </button>
                  <button 
                    type="button" 
                    class="toggle-btn" 
                    :class="{ active: clanForm.visibility === 'private' }"
                    @click="clanForm.visibility = 'private'"
                  >
                    <i class="bi bi-shield-lock me-2"></i>
                    <span>Privado</span>
                    <small class="d-block text-white-50 extra-small mt-1">Requer solicitação de entrada</small>
                  </button>
                </div>
              </div>

              <button type="submit" class="btn-premium-action w-100" :disabled="loading">
                <span v-if="!loading">CRIAR CLÃ</span>
                <span v-else class="spinner-border spinner-border-sm"></span>
              </button>

              <div class="text-center mt-4">
                <router-link to="/home" class="text-white-50 text-decoration-none small hover-white">
                  <i class="bi bi-arrow-left me-1"></i> Voltar para casa
                </router-link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

.premium-create-clan {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
  position: relative;
  overflow: hidden;
}

.blob-bg { position: absolute; width: 100%; height: 100%; z-index: 0; }
.blob { position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.3; }
.b1 { width: 500px; height: 500px; background: #6366f1; top: -10%; right: -10%; }
.b2 { width: 400px; height: 400px; background: #c084fc; bottom: -10%; left: -10%; }

.content-pos { position: relative; z-index: 5; }

.glass-container {
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(25px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 40px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.5);
}

.icon-badge {
  width: 70px;
  height: 70px;
  background: rgba(99, 102, 241, 0.1);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-size: 2rem;
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.2);
}

/* Brasão Preview */
.brasao-preview {
  width: 140px;
  height: 140px;
  background: rgba(30, 41, 59, 0.8);
  border-radius: 35px;
  margin: 0 auto;
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.preview-img { width: 100%; height: 100%; object-fit: cover; }

/* Form */
.label-premium {
  display: block;
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 10px;
  padding-left: 5px;
}

.input-glass-wrapper {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 18px;
  display: flex;
  align-items: center;
  padding: 5px 15px;
  transition: all 0.3s;
}

.input-glass-wrapper:focus-within {
  background: rgba(15, 23, 42, 0.8);
  border-color: #6366f1;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.2);
}

.input-glass-wrapper i { color: #64748b; font-size: 1.2rem; margin-right: 12px; }
.input-glass-wrapper input, .input-glass-wrapper textarea, .input-glass-wrapper select {
  background: transparent;
  border: none;
  color: white;
  width: 100%;
  padding: 12px 0;
}

.input-glass-wrapper input:focus, .input-glass-wrapper textarea:focus, .input-glass-wrapper select:focus {
  outline: none;
}

.btn-premium-action {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  border: none;
  padding: 16px;
  border-radius: 20px;
  font-weight: 700;
  letter-spacing: 1px;
  transition: all 0.3s;
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4);
}

.btn-premium-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 30px rgba(79, 70, 229, 0.6);
}

.hover-white:hover { color: white !important; }

.btn-toggle-premium {
  display: flex;
  gap: 15px;
}

.toggle-btn {
  flex: 1;
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 16px;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: center;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.2);
}

.toggle-btn.active {
  background: rgba(99, 102, 241, 0.15);
  border-color: #6366f1;
  color: white;
  box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
}

.toggle-btn i {
  font-size: 1.3rem;
  display: block;
  margin-bottom: 6px;
  transition: transform 0.3s;
}

.toggle-btn.active i {
  color: #6366f1;
  transform: scale(1.1);
}

.extra-small {
  font-size: 0.75rem;
}
</style>
