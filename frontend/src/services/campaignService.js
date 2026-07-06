import api from '@/services/api'

export async function getCampaigns() {
  const res = await api.get('/campaigns')
  return res.data
}

export async function getCampaignDetail(slug) {
  const res = await api.get(`/campaigns/${slug}`)
  return res.data
}

export async function getCategories() {
  const res = await api.get('/categories')
  return res.data
}

export async function createCampaign(payload) {
  const res = await api.post('/campaigns', payload)
  return res.data
}

export async function submitForReview(campaignId) {
  const res = await api.post(`/campaigns/${campaignId}/submit`)
  return res.data
}

export async function getMyCampaigns() {
  const res = await api.get('/my/campaigns')
  return res.data
}

export async function postUpdate(campaignId, payload) {
  const res = await api.post(`/campaigns/${campaignId}/updates`, payload)
  return res.data
}

export async function backCampaign(payload) {
  const res = await api.post('/backings', payload)
  return res.data
}

const campaignService = { getCampaigns, getCampaignDetail, getCategories, createCampaign, submitForReview, getMyCampaigns, postUpdate, backCampaign }
export default campaignService
