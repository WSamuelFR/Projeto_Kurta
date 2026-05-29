import { defineStore } from 'pinia'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    loading: false
  }),
  
  getters: {
    unreadCount: (state) => state.notifications.length
  },
  
  actions: {
    async fetchNotifications() {
      const token = localStorage.getItem('fellit_token')
      if (!token) return

      try {
        const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
        const res = await axios.get('/api/social/notifications', {
          params: { user_id: user.id }
        })
        if (res.data.status === 'success') {
          this.notifications = res.data.data
        }
      } catch (err) {
        console.error('Erro ao buscar notificações:', err)
      }
    },
    
    async respond(notif, action) {
      try {
        const user = JSON.parse(localStorage.getItem('fellit_user') || '{}')
        const res = await axios.post('/api/social/respond-friend', {
          friendship_id: notif.reference_id,
          user_id: user.id,
          action: action === 'accept' ? 'accepted' : 'rejected'
        })
        
        if (res.data.status === 'success') {
          this.notifications = this.notifications.filter(n => n.notif_id !== notif.notif_id)
          return true
        }
        return false
      } catch (err) {
        console.error('Erro ao responder convite:', err)
        return false
      }
    }
  }
})
