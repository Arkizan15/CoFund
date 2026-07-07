import api from './api'

export const dashboardService = {
  getCreatorStats: () => api.get('/creator/stats'),
  getFundingChart: () => api.get('/creator/funding-chart'),
  getBackerStats: () => api.get('/backer/stats'),
}

export default dashboardService
