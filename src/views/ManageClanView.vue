<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const clanId = route.params.id

const clan = ref(null)
const members = ref([])
const loading = ref(true)
const saving = ref(false)
const userLocal = ref(JSON.parse(localStorage.getItem('fellit_user') || '{}'))

onMounted(async () => {
  if (!userLocal.value.id) return router.push('/login')
  await fetchClanAdminData()
})

async function fetchClanAdminData() {
  loading.value = true
  try {
    const resClan = await axios.get(`/api/clans/${clanId}`)
    if (resClan.data.status === 'success') {
      clan.value = resClan.data.data
    }

    const resMembers = await axios.get(`/api/clans/${clanId}/members`, {
      params: { user_id: userLocal.value.id }
    })
    if (resMembers.data.status === 'success') {
      members.value = resMembers.data.data
      
      // Check if user is the 'rei'
      if (resMembers.data.viewerRole !== 'rei') {
        window.$toast.add('Acesso negado: Somente o Rei pode gerenciar o clã.', 'error')
        router.push(`/clan/${clanId}`)
      }
    }
  } catch (err) {
    console.error(err)
    window.$toast.add('Erro ao carregar dados do clã.', 'error')
  } finally {
    loading.value = false
  }
}

async function saveChanges() {
  saving.value = true
  try {
    const res = await axios.post('/api/clans/update', {
      clan_id: clanId,
      name_clan: clan.value.name_clan,
      description: clan.value.description,
      visibility: clan.value.visibility
    })
    if (res.data.status === 'success') {
      window.$toast.add('Alterações salvas com sucesso!', 'success')
    }
  } catch (err) {
    window.$toast.add('Erro ao salvar alterações.', 'error')
  } finally {
    saving.value = false
  }
}

async function updateRole(userId, newRole) {
  try {
    const res = await axios.post('/api/clans/change-role', {
      clan_id: clanId,
      target_user_id: userId,
      new_role: newRole
    })
    if (res.data.status === 'success') {
      window.$toast.add(`Cargo atualizado para ${newRole}!`, 'success')
      fetchClanAdminData()
    }
  } catch (err) {
    window.$toast.add('Erro ao mudar cargo.', 'error')
  }
}

async function removeMember(userId) {
  if (!confirm('Deseja realmente expulsar este membro?')) return
  try {
    const res = await axios.post('/api/clans/remove-member', {
      clan_id: clanId,
      target_user_id: userId
    })
    if (res.data.status === 'success') {
      window.$toast.add('Membro removido do clã.', 'info')
      fetchClanAdminData()
    }
  } catch (err) {
    window.$toast.add('Erro ao remover membro.', 'error')
  }
}

function avatar_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'User')}&backgroundColor=00897b,1e88e5,5e35b1,d81b60,f4511e`
}

function clan_pic_url(name) {
  return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(name || 'Clan')}&backgroundColor=1e293b,334155,0f172a`
}
</script>

<template>
  <div class="premium-manage-clan">
    <div class="container py-5">
      <!-- Header -->
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4 mb-5 animate__animated animate__fadeIn">
        <div class="d-flex align-items-center gap-4">
          <router-link :to="`/clan/${clanId}`" class="btn-back">
            <i class="bi bi-chevron-left"></i>
          </router-link>
          <div>
            <h1 class="text-white fw-bold h3 mb-1">Gerenciamento do Clã</h1>
            <p class="text-white-50 mb-0">Comande e organize seu império.</p>
          </div>
        </div>
        <div v-if="clan" class="clan-badge-mini">
           <img :src="clan_pic_url(clan.name_clan)" alt="Clan">
           <span class="fw-bold">{{ clan.name_clan }}</span>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-premium"></div>
      </div>

      <div v-else-if="clan" class="row g-4 animate__animated animate__fadeInUp">
        <!-- Edit Section -->
        <div class="col-lg-5">
          <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-4 d-flex align-items-center gap-2">
              <i class="bi bi-gear-fill text-primary"></i> Configurações Base
            </h5>
            
            <div class="mb-4">
              <label class="label-premium">Nome do Clã</label>
              <div class="input-glass-wrapper">
                <input type="text" v-model="clan.name_clan">
              </div>
            </div>

            <div class="mb-4">
              <label class="label-premium">Descrição do Império</label>
              <div class="input-glass-wrapper py-2">
                <textarea v-model="clan.description" rows="5"></textarea>
              </div>
            </div>

            <div class="mb-5">
              <label class="label-premium">Privacidade</label>
              <div class="input-glass-wrapper">
                <select v-model="clan.visibility">
                  <option value="public">Público</option>
                  <option value="private">Privado</option>
                </select>
              </div>
            </div>

            <button class="btn-premium-action w-100" @click="saveChanges" :disabled="saving">
              <span v-if="!saving">SALVAR ALTERAÇÕES</span>
              <span v-else class="spinner-border spinner-border-sm"></span>
            </button>
          </div>
        </div>

        <!-- Members Section -->
        <div class="col-lg-7">
          <div class="glass-card p-4 h-100">
            <h5 class="fw-bold text-white mb-4 d-flex align-items-center gap-2">
              <i class="bi bi-people-fill text-primary"></i> Gestão de Membros
            </h5>
            
            <div class="member-scroll">
              <div v-for="mem in members" :key="mem.user_id" class="member-admin-card p-3 mb-3">
                <div class="d-flex align-items-center gap-3">
                  <img :src="avatar_url(mem.first_name + ' ' + mem.last_name)" class="member-avatar-md" alt="Avatar">
                  <div class="flex-grow-1">
                    <h6 class="text-white mb-1 fw-bold">{{ mem.first_name }} {{ mem.last_name }}</h6>
                    <span class="role-badge" :class="mem.role">
                      {{ mem.role === 'rei' ? '👑 REI' : (mem.role === 'lider' ? '⚔️ LÍDER' : '🛡️ ALDEÃO') }}
                    </span>
                  </div>
                  
                  <div v-if="mem.role !== 'rei'" class="actions-group">
                    <div class="dropdown">
                      <button class="btn-action-sm" data-bs-toggle="dropdown">
                        <i class="bi bi-pencil-square"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="#" @click.prevent="updateRole(mem.user_id, 'lider')">Promover a Líder</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="updateRole(mem.user_id, 'aldeao')">Tornar Aldeão</a></li>
                      </ul>
                    </div>
                    <button class="btn-action-sm delete" @click="removeMember(mem.user_id)">
                      <i class="bi bi-person-x-fill"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.premium-manage-clan {
  min-height: 100vh;
  background-color: #050810;
  font-family: 'Outfit', sans-serif;
}

.btn-back {
  width: 45px;
  height: 45px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  text-decoration: none;
}
.btn-back:hover { background: rgba(255, 255, 255, 0.15); transform: translateX(-3px); }

.clan-badge-mini {
  background: rgba(99, 102, 241, 0.1);
  padding: 8px 16px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #818cf8;
  border: 1px solid rgba(99, 102, 241, 0.2);
}
.clan-badge-mini img { width: 30px; height: 30px; border-radius: 8px; }

.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
}

.label-premium {
  display: block;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.input-glass-wrapper {
  background: rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 5px 15px;
}
.input-glass-wrapper input, .input-glass-wrapper textarea, .input-glass-wrapper select {
  background: transparent;
  border: none;
  color: white;
  width: 100%;
  padding: 10px 0;
}
.input-glass-wrapper input:focus, .input-glass-wrapper textarea:focus, .input-glass-wrapper select:focus {
  outline: none;
}

.btn-premium-action {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  border: none;
  padding: 14px;
  border-radius: 16px;
  font-weight: 700;
  transition: all 0.3s;
}
.btn-premium-action:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(79, 70, 229, 0.4); }

/* Members */
.member-admin-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 18px;
  transition: all 0.2s;
}
.member-admin-card:hover { background: rgba(255, 255, 255, 0.05); }

.member-avatar-md { width: 45px; height: 45px; border-radius: 14px; object-fit: cover; }

.role-badge {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
}
.role-badge.rei { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.role-badge.lider { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.role-badge.aldeao { background: rgba(148, 163, 184, 0.1); color: #94a3b8; }

.btn-action-sm {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.05);
  color: #94a3b8;
  width: 35px;
  height: 35px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-action-sm:hover { background: #6366f1; color: white; }
.btn-action-sm.delete:hover { background: #ef4444; }

.actions-group { display: flex; gap: 8px; }

.spinner-premium {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(99, 102, 241, 0.1);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 2rem auto;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
