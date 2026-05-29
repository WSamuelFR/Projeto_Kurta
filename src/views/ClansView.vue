<script setup>
import ClanList from '../components/ClanList.vue'
</script>

<template>
  <div class="clans-page min-vh-100 py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="glass-card p-5 animate__animated animate__fadeIn">
            <div class="header text-center mb-5">
              <div class="icon-box mb-3">
                <i class="bi bi-shield-shaded"></i>
              </div>
              <h1 class="text-white fw-bold">Clãs do fell.it</h1>
              <p class="text-white-50">Encontre a sua comunidade, compartilhe sentimentos e faça parte de um clã.</p>
            </div>
            
            <ClanList />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.clans-page {
  background-color: #050810;
}

.glass-card {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 32px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.icon-box {
  font-size: 3rem;
  color: #6366f1;
  filter: drop-shadow(0 0 15px rgba(99, 102, 241, 0.3));
}

.header h1 {
  letter-spacing: -1.5px;
  background: linear-gradient(to bottom, #fff, #94a3b8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
</style>
