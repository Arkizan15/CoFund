import api from './api'

export const notificationService = {
  getUnreadNotifications: (params = {}) => api.get('/notifications', { params }),
  getUnreadCount: () => api.get('/notifications/unread-count'),
  patchMarkAsRead: (id) => api.patch(`/notifications/${id}/read`),
  markAllAsRead: () => api.post('/notifications/mark-all-read'),
  sendAnnouncement: (data) => api.post('/admin/announcements', data),
}

export default notificationService
