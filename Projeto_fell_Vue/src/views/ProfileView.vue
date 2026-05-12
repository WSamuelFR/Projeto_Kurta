<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const user = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  profile_pic: 'assets/files/default_avatar.png',
  wallpaper_pic: 'assets/files/default_wallpaper.png'
})

const editedUser = ref({ ...user.value, password: '' })
const avatarPreview = ref(null)
const wallpaperPreview = ref(null)
const avatarFile = ref(null)
const wallpaperFile = ref(null)

const loading = ref(true)
const saving = ref(false)
const activeTab = ref('feed')
const alert = ref({ show: false, message: '', type: '' })

onMounted(fetchProfile)

async function fetchProfile() {
  try {
    const res = await axios.get('/app/controller/perfil_controller/load_profile.php')
    if (res.data.success) {
      user.value = res.data.data
      resetEdit()
    }
  } catch (err) {
    showAlert('Erro ao carregar perfil.', 'error')
  } finally {
    loading.value = false
  }
}

function resetEdit() {
  editedUser.value = { ...user.value, password: '' }
  avatarPreview.value = null
  wallpaperPreview.value = null
  avatarFile.value = null
  wallpaperFile.value = null
}

function onFileChange(type, e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (event) => {
    if (type === 'avatar') {
      avatarPreview.value = event.target.result
      avatarFile.value = file
    } else {
      wallpaperPreview.value = event.target.result
      wallpaperFile.value = file
    }
  }
  reader.readAsDataURL(file)
}

async function saveChanges() {
  saving.value = true
  const formData = new FormData()
  formData.append('first_name', editedUser.value.first_name)
  formData.append('last_name', editedUser.value.last_name)
  formData.append('phone', editedUser.value.phone)
  formData.append('password', editedUser.value.password)
  
  if (avatarFile.value) formData.append('avatar', avatarFile.value)
  if (wallpaperFile.value) formData.append('wallpaper', wallpaperFile.value)

  try {
    const res = await axios.post('/app/controller/perfil_controller/update_profile.php', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (res.data.success) {
      showAlert(res.data.message, 'success')
      await fetchProfile()
    } else {
      showAlert(res.data.message, 'error')
    }
  } catch (err) {
    showAlert('Erro ao salvar alterações.', 'error')
  } finally {
    saving.value = false
  }
}

function showAlert(msg, type) {
  alert.value = { show: true, message: msg, type: type }
  setTimeout(() => alert.value.show = false, 4000)
}

function setTab(tab) {
  activeTab.value = tab
}
</script>

<template>
  <div class="profile-page">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <template v-else>
      <!-- Header: Wallpaper + Avatar -->
      <div class="profile-header shadow-sm">
        <div class="wallpaper-container">
          <img :src="wallpaperPreview || wallpaper_pic_url(user.wallpaper_pic)" alt="Capa" class="wallpaper-img">
          <div class="wallpaper-overlay"></div>
          <label class="btn btn-sm btn-dark btn-edit-wall">
            <i class="bi bi-pencil-square me-1"></i> Alterar Capa
            <input type="file" hidden @change="e => onFileChange('wallpaper', e)">
          </label>
        </div>
        
        <div class="container position-relative">
          <div class="avatar-wrapper">
            <img :src="avatarPreview || avatar_pic_url(user.profile_pic)" alt="Avatar" class="avatar-img border-4 border-white shadow">
            <label class="btn btn-sm btn-light btn-edit-avatar">
              <i class="bi bi-camera"></i>
              <input type="file" hidden @change="e => onFileChange('avatar', e)">
            </label>
          </div>
          
          <div class="profile-title mt-3">
            <h2 class="fw-bold mb-0 text-white">{{ user.first_name }} {{ user.last_name }}</h2>
            <p class="text-white-50">@{{ user.first_name.toLowerCase() }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="container mt-4">
        <!-- Alertas -->
        <div v-if="alert.show" :class="['alert', alert.type === 'error' ? 'alert-danger' : 'alert-success']" role="alert">
          {{ alert.message }}
        </div>

        <div class="row">
          <!-- Sidebar: Info -->
          <div class="col-lg-4">
            <div class="glass-card p-4 mb-4 text-start">
              <h5 class="fw-bold text-white mb-3">Informações</h5>
              <div class="info-item mb-2">
                <span class="text-muted small d-block">E-mail</span>
                <span class="text-white">{{ user.email }}</span>
              </div>
              <div class="info-item mb-2">
                <span class="text-muted small d-block">Telefone</span>
                <span class="text-white">{{ user.phone || 'Não informado' }}</span>
              </div>
              <div class="info-item mb-2">
                <span class="text-muted small d-block">Membro desde</span>
                <span class="text-white">{{ new Date(user.created_at).toLocaleDateString() }}</span>
              </div>
              <button class="btn btn-outline-primary w-100 mt-3" @click="setTab('settings')">
                Editar Perfil
              </button>
            </div>
          </div>

          <!-- Main Area: Tabs -->
          <div class="col-lg-8">
            <div class="glass-card">
              <ul class="nav nav-pills p-2 gap-2" id="profileTabs">
                <li class="nav-item">
                  <button class="nav-link text-white" :class="{ active: activeTab === 'feed' }" @click="setTab('feed')">Sentimentos</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link text-white" :class="{ active: activeTab === 'friends' }" @click="setTab('friends')">Amigos</button>
                </li>
                <li class="nav-item">
                  <button class="nav-link text-white" :class="{ active: activeTab === 'settings' }" @click="setTab('settings')">Configurações</button>
                </li>
              </ul>

              <div class="p-4 tab-content text-white text-start">
                <div v-if="activeTab === 'feed'">
                  <p class="text-muted italic">Você ainda não compartilhou sentimentos.</p>
                </div>
                
                <div v-if="activeTab === 'friends'">
                  <p class="text-muted italic">Lista de amigos vazia.</p>
                </div>

                <div v-if="activeTab === 'settings'">
                  <h6 class="mb-4 fw-bold border-bottom pb-2 border-white-50">Dados Pessoais</h6>
                  
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Primeiro Nome</label>
                      <input type="text" v-model="editedUser.first_name" class="form-control custom-input">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Sobrenome</label>
                      <input type="text" v-model="editedUser.last_name" class="form-control custom-input">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Telefone</label>
                      <input type="text" v-model="editedUser.phone" class="form-control custom-input">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold text-muted">Nova Senha (deixe em branco se não quiser mudar)</label>
                      <input type="password" v-model="editedUser.password" class="form-control custom-input" placeholder="••••••••">
                    </div>
                  </div>

                  <div class="mt-5 d-flex gap-2">
                    <button class="btn btn-primary-custom px-4" @click="saveChanges" :disabled="saving">
                      <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                      Salvar Alterações
                    </button>
                    <button class="btn btn-outline-light px-4" @click="resetEdit" :disabled="saving">Cancelar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
// Funções auxiliares para URLs de imagem
function avatar_pic_url(path) {
  return path ? `/${path}` : 'https://api.dicebear.com/7.x/avataaars/svg?seed=default'
}
function wallpaper_pic_url(path) {
  return path ? `/${path}` : 'https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=2029'
}
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background-color: #0f172a;
  padding-bottom: 3rem;
}

.profile-header {
  position: relative;
  background-color: #1e293b;
}

.wallpaper-container {
  height: 250px;
  width: 100%;
  overflow: hidden;
  position: relative;
}

.wallpaper-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.btn-edit-wall {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  z-index: 2;
}

.btn-edit-wall:hover {
  background: rgba(0, 0, 0, 0.8);
}

.wallpaper-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, transparent, rgba(15, 23, 42, 0.8));
}

.avatar-wrapper {
  position: relative;
  width: 150px;
  height: 150px;
  margin-top: -75px;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  background-color: #1e293b;
}

.btn-edit-avatar {
  position: absolute;
  bottom: 10px;
  right: 10px;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.glass-card {
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
}

.nav-pills .nav-link.active {
  background-color: #6366f1;
}

.btn-outline-primary {
  color: #6366f1;
  border-color: #6366f1;
}

.btn-outline-primary:hover {
  background-color: #6366f1;
  color: white;
}
</style>
