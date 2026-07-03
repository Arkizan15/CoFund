<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-sm text-gray-400 mb-6">
        <router-link :to="{ name: 'Home' }" class="hover:text-emerald-600 transition-colors">Home</router-link>
        <span class="mx-2">/</span>
        <router-link :to="{ name: 'CampaignList' }" class="hover:text-emerald-600 transition-colors">Kampanye</router-link>
        <span class="mx-2">/</span>
        <span class="text-gray-600">{{ campaign.title || 'Detail' }}</span>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <i class="pi pi-spin pi-spinner text-4xl text-emerald-600"></i>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
          <div class="relative rounded-2xl overflow-hidden h-64 md:h-80 bg-gray-100 shadow-md">
            <img
              :src="campaign.image_url || 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&q=80'"
              :alt="campaign.title"
              class="w-full h-full object-cover"
            />
            <div class="absolute top-4 left-4">
              <Badge :value="campaign.status || 'active'" severity="success" class="!bg-white/90 !text-emerald-700 !text-xs !px-3 !py-1 !rounded-full !font-semibold shadow-sm" />
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ campaign.title }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <i class="pi pi-user text-emerald-600"></i>
                <span>Oleh <strong class="text-gray-700">{{ campaign.creator?.name || campaign.user?.name || 'N/A' }}</strong></span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-tag text-emerald-600"></i>
                <span>{{ campaign.category?.name || 'Umum' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-calendar text-emerald-600"></i>
                <span v-if="campaign.deadline">Tenggat: {{ formatDate(campaign.deadline) }}</span>
              </div>
            </div>

            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed" v-html="campaign.description || 'Belum ada deskripsi.'"></div>

            <div v-if="campaign.video_url" class="mt-6 bg-gray-100 rounded-xl h-56 flex items-center justify-center text-gray-400">
              <div class="text-center">
                <i class="pi pi-video text-4xl mb-2 block"></i>
                <span class="text-sm">Video tersedia</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Riwayat Donasi</h3>
            <p class="text-sm text-gray-400 mb-4">Para pendukung kampanye ini</p>
            <DataTable :value="backers" class="!text-sm" stripedRows responsiveLayout="scroll">
              <Column field="name" header="Nama" />
              <Column field="amount" header="Jumlah" :body="formatAmountCol" />
              <Column field="date" header="Tanggal" />
            </DataTable>
          </div>
        </div>

        <aside class="space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Ringkasan Pendanaan</h4>

            <div class="space-y-4">
              <div>
                <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(campaign.collected_amount || 0) }}</div>
                <div class="text-sm text-gray-500 mt-1">terkumpul</div>
              </div>

              <div>
                <div class="text-sm text-gray-500">Target</div>
                <div class="text-lg font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount || 0) }}</div>
              </div>

              <ProgressBar :value="progressPercent(campaign)" class="!h-3 !rounded-full" />

              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ progressPercent(campaign) }}% tercapai</span>
                <span class="flex items-center gap-1.5 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ remainingDays(campaign.deadline) }} hari lagi
                </span>
              </div>

              <hr class="border-gray-100" />
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Total Pendukung</span>
                <span class="font-semibold text-gray-800">{{ backers.length }}</span>
              </div>
              <hr class="border-gray-100" />
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Status</span>
                <Badge :value="campaign.status || 'active'" severity="success" />
              </div>
            </div>

            <div class="mt-6 space-y-3">
              <Button
                label="Dukung Kampanye Ini"
                icon="pi pi-heart"
                class="w-full bg-emerald-600 border-none hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl shadow-sm"
              />
            </div>
          </div>

          <div v-if="campaign.tiers?.length || mockTiers.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Tier Reward</h4>
            <div class="space-y-3">
              <div v-for="(tier, idx) in campaign.tiers || mockTiers" :key="idx" class="border border-emerald-100 rounded-xl p-4 bg-emerald-50/50 hover:bg-emerald-50 hover:border-emerald-200 transition-colors cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                  <h5 class="font-semibold text-gray-800 text-sm">{{ tier.name }}</h5>
                  <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded-full">Sisa {{ tier.remaining ?? '-' }}</span>
                </div>
                <div class="text-sm text-gray-600">Min. {{ formatCurrency(tier.min_amount) }}</div>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Badge from 'primevue/badge'
import ProgressBar from 'primevue/progressbar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import * as campaignService from '@/services/campaignService'

const route = useRoute()
const campaign = ref({})
const backers = ref([])
const loading = ref(true)

const mockTiers = [
  { name: 'Supporter', min_amount: 50000, remaining: 50 },
  { name: 'Partner', min_amount: 250000, remaining: 20 },
  { name: 'Sponsor', min_amount: 1000000, remaining: 5 }
]

onMounted(async () => {
  try {
    const data = await campaignService.getCampaignDetail(route.params.slug)
    campaign.value = data?.data || data || {}
    backers.value = data?.backers || [
      { name: 'Adi Pratama', amount: 100000, date: '2026-07-01' },
      { name: 'Sari Dewi', amount: 250000, date: '2026-07-02' },
      { name: 'Budi Hartono', amount: 500000, date: '2026-07-03' },
      { name: 'Rina Wijaya', amount: 150000, date: '2026-07-04' }
    ]
  } catch (e) {
    campaign.value = { title: 'Kampanye Tidak Ditemukan' }
    backers.value = []
  } finally {
    loading.value = false
  }
})

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
}

function formatAmountCol(row) {
  return formatCurrency(row.amount)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
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
