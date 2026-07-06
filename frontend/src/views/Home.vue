<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-green-500 text-white">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-300 rounded-full blur-3xl"></div>
      </div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
              {{ $t('home.heroTitle') }}<br />
              <span class="text-emerald-200">{{ $t('home.heroTitleHighlight') }}</span>
            </h1>
            <p class="text-lg md:text-xl text-emerald-50 leading-relaxed max-w-lg">
              {{ $t('home.heroSubtitle') }}
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
              <router-link
                :to="{ name: 'CampaignList' }"
                class="inline-flex items-center gap-2 bg-white text-emerald-700 font-semibold px-8 py-3.5 rounded-xl shadow-lg hover:bg-emerald-50 transition-all duration-200 hover:-translate-y-0.5"
              >
                <i class="pi pi-search"></i>
                {{ $t('home.exploreCampaigns') }}
              </router-link>
              <router-link
                :to="{ name: 'Register' }"
                class="inline-flex items-center gap-2 border-2 border-white/40 text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-white/10 transition-all duration-200"
              >
                <i class="pi pi-user-plus"></i>
                {{ $t('home.startCampaign') }}
              </router-link>
            </div>
          </div>
          <div class="hidden md:block relative">
            <img
              src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80"
              alt="Community crowdfunding"
              class="rounded-2xl shadow-2xl w-full h-[400px] object-cover"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- About / Platform Values -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $t('home.howItWorks') }}</h2>
          <p class="text-gray-500 max-w-2xl mx-auto">{{ $t('home.howItWorksSubtitle') }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-shield text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $t('home.step1Title') }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              {{ $t('home.step1Desc') }}
            </p>
          </div>

          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-chart-line text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $t('home.step2Title') }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              {{ $t('home.step2Desc') }}
            </p>
          </div>

          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-globe text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $t('home.step3Title') }}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              {{ $t('home.step3Desc') }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Campaign Preview Section -->
    <section class="py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">{{ $t('home.featuredCampaigns') }}</h2>
            <p class="text-gray-500 mt-2">{{ $t('home.featuredSubtitle') }}</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            {{ $t('common.viewAll') }}
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="campaign in featuredCampaigns" :key="campaign.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
            <div class="relative overflow-hidden h-48">
              <img :src="campaign.image" :alt="campaign.title" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" />
              <div class="absolute top-3 left-3">
                <span class="bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                  {{ campaign.category }}
                </span>
              </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <router-link :to="`/campaigns/${campaign.slug}`" class="text-lg font-bold text-gray-800 hover:text-emerald-600 transition-colors line-clamp-2 mb-3">
                {{ campaign.title }}
              </router-link>

              <div class="mt-auto space-y-3">
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full transition-all duration-500" :style="{ width: campaign.progress + '%' }"></div>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">
                    {{ $t('home.collected') }} <strong class="text-gray-800">{{ campaign.progress }}%</strong>
                  </span>
                  <span class="flex items-center gap-1.5 text-orange-600 font-medium">
                    <i class="pi pi-clock text-xs"></i>
                    {{ $t('common.daysLeft', { days: campaign.daysLeft }) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-10 sm:hidden">
          <router-link :to="{ name: 'CampaignList' }" class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            {{ $t('common.viewAll') }}
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Guest CTA -->
    <section v-if="!authStore.isAuthenticated" class="bg-gradient-to-r from-emerald-700 to-emerald-600 py-16">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-6">
          <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center">
            <i class="pi pi-wallet text-3xl text-white"></i>
          </div>
        </div>          <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">{{ $t('home.ctaTitle') }}</h3>
          <p class="text-emerald-100 mb-8 max-w-2xl mx-auto">{{ $t('home.ctaSubtitle') }}</p>
          <router-link :to="{ name: 'Register' }" class="inline-flex items-center gap-2 bg-white text-emerald-700 font-bold px-10 py-4 rounded-xl shadow-lg hover:bg-emerald-50 transition-all duration-200 hover:-translate-y-0.5 no-underline">
            <i class="pi pi-user-plus"></i>
            {{ $t('home.ctaRegister') }}
          </router-link>
      </div>
    </section>

    <!-- Logged-in Campaign Cards -->
    <section v-else class="py-16 bg-gradient-to-r from-emerald-700 to-emerald-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ $t('home.exploreLoggedTitle') }}</h3>
            <p class="text-emerald-100 text-sm">{{ $t('home.exploreLoggedSubtitle') }}</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 no-underline">
            {{ $t('common.viewAll') }}
            <i class="pi pi-arrow-right text-sm"></i>
          </router-link>
        </div>

        <div v-if="liveCampaignsLoading" class="flex items-center justify-center py-12">
          <i class="pi pi-spin pi-spinner text-3xl text-white/70"></i>
        </div>

        <div v-else-if="liveCampaigns.length === 0" class="text-center py-12 bg-white/10 rounded-2xl">
          <i class="pi pi-inbox text-4xl text-white/40 mb-3 block"></i>
          <p class="text-white/70 text-sm">{{ $t('common.noData') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="campaign in liveCampaigns.slice(0, 3)"
            :key="campaign.id"
            class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col"
          >
            <div class="relative overflow-hidden h-44 bg-gray-100">
              <img
                src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=400&q=80"
                :alt="campaign.title"
                class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
              />
              <div class="absolute top-3 left-3">
                <span class="bg-emerald-600/90 text-white text-[10px] font-semibold px-2.5 py-1 rounded-full shadow-sm">
                  {{ $t('categories.' + (campaign.category?.slug || 'general')) }}
                </span>
              </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <h4 class="font-bold text-gray-800 text-sm line-clamp-2 mb-3">{{ campaign.title }}</h4>
              <div class="mt-auto space-y-2.5">
                <ProgressBar :value="progressPercent(campaign)" class="!h-7 !rounded-full" />
                <div class="flex items-center justify-between text-xs">
                  <span class="text-gray-500">
                    {{ formatCurrency(campaign.collected_amount || 0) }}
                  </span>
                  <span class="flex items-center gap-1 text-orange-600 font-medium">
                    <i class="pi pi-clock text-[10px]"></i>
                    {{ $t('common.daysLeft', { days: remainingDays(campaign.deadline) }) }}
                  </span>
                </div>
              </div>
              <router-link
                :to="`/campaigns/${campaign.slug}`"
                class="mt-4 block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-all duration-200 shadow-sm no-underline"
              >
                {{ $t('home.supportNow') }}
                <i class="pi pi-arrow-right ml-1.5 text-xs"></i>
              </router-link>
            </div>
          </div>
        </div>

        <div class="text-center mt-8 sm:hidden">
          <router-link :to="{ name: 'CampaignList' }" class="inline-flex items-center gap-2 text-white font-semibold hover:text-emerald-100 transition-colors">
            {{ $t('common.viewAll') }}
            <i class="pi pi-arrow-right text-sm"></i>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import ProgressBar from 'primevue/progressbar'
import * as campaignService from '@/services/campaignService'

const authStore = useAuthStore()

const featuredCampaigns = ref([
  {
    id: 1,
    title: 'Ekspansi Cabang Kopi Skena Sektor 4',
    slug: 'ekspansi-cabang-kopi-skena',
    category: 'F&B',
    progress: 75,
    daysLeft: 14,
    image: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80'
  },
  {
    id: 2,
    title: 'Pendanaan Bahan Baku Konveksi Taylor ID',
    slug: 'pendanaan-bahan-baku-konveksi',
    category: 'Fashion',
    progress: 40,
    daysLeft: 22,
    image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&q=80'
  },
  {
    id: 3,
    title: 'Digitalisasi Distribusi Sembako Murah Mandiri',
    slug: 'digitalisasi-distribusi-sembako',
    category: 'Ritel',
    progress: 90,
    daysLeft: 5,
    image: 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=600&q=80'
  }
])

const liveCampaigns = ref([])
const liveCampaignsLoading = ref(true)

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
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

onMounted(async () => {
  if (authStore.isAuthenticated) {
    try {
      const data = await campaignService.getCampaigns()
      liveCampaigns.value = data?.data || data || []
    } catch (e) {
      liveCampaigns.value = []
    } finally {
      liveCampaignsLoading.value = false
    }
  } else {
    liveCampaignsLoading.value = false
  }
})
</script>