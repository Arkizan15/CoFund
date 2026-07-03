import api from '@/services/api'

export async function getCampaigns() {
  const res = await api.get('/campaigns')
  return res.data
}

export async function getCampaignDetail(slug) {
  const res = await api.get(`/campaigns/${slug}`)
  return res.data
}

// Export default object for backward compatibility
const campaignService = { getCampaigns, getCampaignDetail }
export default campaignService


