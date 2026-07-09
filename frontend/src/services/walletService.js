import api from './api'

export const walletService = {
  postTopUp: (amount, redirectUrls = {}) => api.post('/wallet/top-up', {
    amount,
    ...(redirectUrls.success && { success_redirect_url: redirectUrls.success }),
    ...(redirectUrls.failure && { failure_redirect_url: redirectUrls.failure }),
  }),
  getBalance: (params) => api.get('/wallet/balance', { params }),
  postWithdraw: (amount, redirectUrls = {}) => api.post('/wallet/withdraw', {
    amount,
    ...(redirectUrls.success && { success_redirect_url: redirectUrls.success }),
    ...(redirectUrls.failure && { failure_redirect_url: redirectUrls.failure }),
  }),
  verifyPayment: (externalId) => api.post('/wallet/verify-payment', { external_id: externalId }),
}

export default walletService
