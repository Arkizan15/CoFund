import api from './api'

export const notificationService = {
  getUnreadNotifications: () => api.get('/notifications'),
  patchMarkAsRead: (id) => api.patch(`/notifications/${id}/read`),
  markAllAsRead: () => api.post('/notifications/mark-all-read'),
}

export default notificationService
