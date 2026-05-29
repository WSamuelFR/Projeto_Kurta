<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from './components/NavBar.vue'
import ToastProvider from './components/ToastProvider.vue'

const route = useRoute()
const showNavBar = computed(() => {
  const hasToken = localStorage.getItem('fellit_token')
  return !['login', 'register'].includes(route.name) && hasToken
})
</script>

<template>
  <ToastProvider />
  <NavBar v-if="showNavBar" />
  <router-view></router-view>
</template>

<style>
/* Estilos Globais */
body {
  margin: 0;
  padding: 0;
  background-color: #050810;
  color: white;
}

@media (max-width: 768px) {
  body {
    padding-bottom: 75px;
  }
}
</style>
