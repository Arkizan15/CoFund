import api from '@/services/api'

export async function getCampaigns(params = {}) {
  const query = {}
  if (params.sort) query.sort = params.sort
  if (params.search) query.search = params.search
  if (params.category_id) query.category_id = params.category_id
  if (params.status) query.status = params.status
  const res = await api.get('/campaigns', { params: query })
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

export async function updateCampaign(campaignId, payload) {
  const res = await api.put(`/campaigns/${campaignId}`, payload)
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

export async function createBackingInvoice(payload) {
  const res = await api.post('/backings/invoice', payload)
  return res.data
}

export async function uploadCampaignImage(campaignId, formData) {
  const res = await api.post(`/campaigns/${campaignId}/images`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return res.data
}

export async function deleteCampaignImage(campaignId) {
  const res = await api.delete(`/campaigns/${campaignId}/images`)
  return res.data
}

export async function setPrimaryImage(campaignId, imageId) {
  const res = await api.post(`/campaigns/${campaignId}/images/${imageId}/primary`)
  return res.data
}

export async function deleteSpecificImage(campaignId, imageId) {
  const res = await api.delete(`/campaigns/${campaignId}/images/${imageId}`)
  return res.data
}

export async function simulatePayment(backingId) {
  const res = await api.post(`/backings/${backingId}/pay`)
  return res.data
}

export async function getMyBackings() {
  const res = await api.get('/my/backings')
  return res.data
}

export async function deleteCampaign(id) {
  const res = await api.delete(`/campaigns/${id}`)
  return res.data
}

const campaignService = { getCampaigns, getCampaignDetail, getCategories, createCampaign, updateCampaign, submitForReview, getMyCampaigns, postUpdate, backCampaign, createBackingInvoice, uploadCampaignImage, deleteCampaignImage, setPrimaryImage, deleteSpecificImage, simulatePayment, getMyBackings, deleteCampaign }
export default campaignService
