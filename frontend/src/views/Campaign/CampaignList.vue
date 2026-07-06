<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Jelajahi Kampanye</h1>
          <p class="text-gray-500 text-sm mt-1">Temukan dan dukung kampanye crowdfunding terbaik</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <InputText
              v-model="search"
              placeholder="Cari kampanye..."
              class="w-full pl-10 !border-gray-200 !rounded-xl !py-3 !text-sm"
            />
          </div>
          <div class="w-full sm:w-56">
            <Dropdown
              v-model="selectedCategory"
              :options="categories"
              optionLabel="label"
              optionValue="value"
              placeholder="Semua Kategori"
              class="w-full"
            />
          </div>
        </div>
      </div>

      <div v-if="filteredCampaigns.length === 0" class="text-center py-20">
        <i class="pi pi-inbox text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">Tidak ada kampanye ditemukan</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
          <div class="relative h-48 overflow-hidden bg-gray-100">
            <img
              v-if="campaign.image_url || campaign.image"
              :src="campaign.image_url || campaign.image || 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80'"
              :alt="campaign.title"
              class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
              <i class="pi pi-image text-4xl"></i>
            </div>
            <div class="absolute top-3 left-3">
              <span class="bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                {{ campaign.category?.name || 'Umum' }}
              </span>
            </div>
          </div>

          <div class="p-5 flex flex-col flex-1">
            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ campaign.title }}</h3>

            <div class="space-y-3 mb-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Terkumpul</span>
                <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.collected_amount || 0) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Target</span>
                <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount || 0) }}</span>
              </div>
            </div>

            <div class="space-y-2">
              <ProgressBar :value="progressPercent(campaign)" class="!h-2.5 !rounded-full" />
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">{{ progressPercent(campaign) }}% terkumpul</span>
                <span class="flex items-center gap-1 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ remainingDays(campaign.deadline) }} hari lagi
                </span>
              </div>
            </div>

            <div class="mt-5 pt-4 border-t border-gray-100">
              <router-link :to="`/campaigns/${campaign.slug}`">
                <Button
                  label="Lihat Detail"
                  icon="pi pi-arrow-right"
                  iconPos="right"
                  class="w-full bg-emerald-600 border-none hover:bg-emerald-700 text-white font-medium !py-2.5 !rounded-xl shadow-sm"
                />
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import ProgressBar from 'primevue/progressbar'
import Button from 'primevue/button'
import * as campaignService from '@/services/campaignService'

const campaigns = ref([])
const search = ref('')
const selectedCategory = ref(null)
const categories = ref([])

onMounted(async () => {
  try {
    const data = await campaignService.getCampaigns()
    campaigns.value = data?.data || data || []
    const unique = {}
    campaigns.value.forEach(c => {
      const name = c.category?.name || 'Umum'
      if (!unique[name]) {
        unique[name] = true
        categories.value.push({ label: name, value: name })
      }
    })
  } catch (e) {
    campaigns.value = []
  }
})

const filteredCampaigns = computed(() => {
  return campaigns.value.filter(c => {
    const q = search.value.toLowerCase()
    const matchesQuery = !q ||
      (c.title && c.title.toLowerCase().includes(q)) ||
      (c.description && c.description.toLowerCase().includes(q))
    const matchesCategory = !selectedCategory.value || (c.category?.name === selectedCategory.value)
    return matchesQuery && matchesCategory
  })
})

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
}

function remainingDays(deadline) {
  if (!deadline) return '-'
  const d = new Date(deadline)
  const diff = Math.ceil((d - new Date()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
}

function progressPercent(c) {
  const col = Number(c.collected_amount || 0)
  const tar = Number(c.target_amount || 1)
  return Math.min(100, Math.round((col / tar) * 100))
}
</script>
