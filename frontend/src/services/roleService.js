import api from './api'

export const roleService = {
  requestCreatorRole: (reason) => api.post('/role-requests', { reason }),
  getPendingRequestsAdmin: () => api.get('/role-requests'),
  updateRequestStatusAdmin: (id, status, rejectionReason) =>
    api.put(`/role-requests/${id}`, { status, rejection_reason: rejectionReason }),
}

export default roleService
