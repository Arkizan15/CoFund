import api from './api'

export const walletService = {
  postTopUp: (amount) => api.post('/wallet/top-up', { amount }),
  getBalance: (params) => api.get('/wallet/balance', { params }),
  postWithdraw: (amount) => api.post('/wallet/withdraw', { amount }),
}

export default walletService
