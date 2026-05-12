import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ProfileView from '../views/ProfileView.vue'
import ClanDetailView from '../views/ClanDetailView.vue'
import ManageClanView from '../views/ManageClanView.vue'
import CreateClanView from '../views/CreateClanView.vue'
import HomeView from '../views/HomeView.vue'
import SearchView from '../views/SearchView.vue'
import VisitorProfileView from '../views/VisitorProfileView.vue'
import ClansView from '../views/ClansView.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/home',
    name: 'home',
    component: HomeView,
    meta: { requiresAuth: true }
  },
  {
    path: '/clans',
    name: 'clans',
    component: ClansView,
    meta: { requiresAuth: true }
  },
  {
    path: '/search',
    name: 'search',
    component: SearchView,
    meta: { requiresAuth: true }
  },
  {
    path: '/user/:id',
    name: 'user-profile',
    component: VisitorProfileView,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true }
  },
  {
    path: '/clan/create',
    name: 'clan-create',
    component: CreateClanView,
    meta: { requiresAuth: true }
  },
  {
    path: '/clan/:id',
    name: 'clan-detail',
    component: ClanDetailView,
    meta: { requiresAuth: true }
  },
  {
    path: '/clan/:id/manage',
    name: 'clan-manage',
    component: ManageClanView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('fellit_token')
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    next('/home')
  } else {
    next()
  }
})

export default router
