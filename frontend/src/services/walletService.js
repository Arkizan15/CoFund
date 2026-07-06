import api from './api'

export const walletService = {
  postTopUp: (amount) => api.post('/wallet/top-up', { amount }),
  getBalance: () => api.get('/wallet/balance'),
}

export default walletService
