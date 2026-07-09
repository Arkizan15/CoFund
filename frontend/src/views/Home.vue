<template>
  <div class="min-h-screen bg-slate-950 text-slate-900">
    <section class="relative isolate overflow-hidden bg-gradient-to-br from-emerald-800 via-emerald-700 to-green-500 text-white min-h-screen flex items-center">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.2),transparent_38%)]"></div>
        <div class="parallax-blur absolute top-8 left-8 w-64 h-64 rounded-full bg-white/20 blur-3xl" data-speed="0.16"></div>
        <div class="parallax-blur absolute bottom-8 right-8 w-80 h-80 rounded-full bg-emerald-200/20 blur-3xl" data-speed="0.28"></div>
        <div class="parallax-blur absolute top-1/3 right-1/4 w-56 h-56 rounded-full bg-emerald-300/15 blur-3xl" data-speed="0.34"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 md:py-10 w-full">
        <div class="grid grid-cols-1 md:grid-cols-[1.1fr_0.9fr] gap-12 items-center">
          <div class="space-y-7">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-medium text-emerald-50 backdrop-blur-sm">
              <i class="pi pi-bolt text-xs"></i>
              Platform Crowdfunding Lokal
            </div>
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-white">
              Mulai Perjalanan<br />
              <span class="text-emerald-100">Bersama CoFund</span>
            </h1>
            <p class="text-base sm:text-lg md:text-xl text-emerald-50/90 leading-relaxed max-w-lg sm:max-w-xl">
              Sistem crowdfunding dengan virtual escrow, tier backer, campaign lifecycle otomatis, dan refund
berbasis queue job. Dirancang sebagai proyek pelatihan magang dengan kompleksitas bisnis
yang realistis.
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
              <router-link
                :to="{ name: 'CampaignList' }"
                class="inline-flex items-center gap-2 bg-white text-emerald-700 font-semibold w-full sm:w-auto px-5 py-3 sm:px-8 sm:py-3.5 rounded-2xl shadow-lg shadow-emerald-950/20 hover:bg-emerald-50 transition-all duration-300 hover:-translate-y-0.5"
              >
                <i class="pi pi-search"></i>
                Jelajahi Campaign
              </router-link>
              <router-link
                :to="{ name: 'Register' }"
                class="inline-flex items-center gap-2 border border-white/40 text-white font-semibold w-full sm:w-auto px-5 py-3 sm:px-8 sm:py-3.5 rounded-2xl bg-white/10 backdrop-blur-sm hover:bg-white/15 transition-all duration-300"
              >
                <i class="pi pi-user-plus"></i>
                Mulai Kampanye
              </router-link>
            </div>
          </div>

          <div class="hidden md:block relative">
            <div class="absolute inset-0 rounded-[2rem] bg-gradient-to-tr from-emerald-200/30 to-transparent blur-2xl"></div>
            <img
              src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80"
              alt="Community crowdfunding"
              class="relative rounded-[2rem] shadow-2xl shadow-emerald-950/30 w-full h-[420px] object-cover border border-white/20"
            />
          </div>
        </div>
      </div>

      <button
        type="button"
        class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-white/80 hover:text-white transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60 rounded-full px-3 py-2 animate-hero-bounce"
        @click="scrollToHowItWorks"
      >
        <span class="text-[11px] tracking-[0.35em] uppercase font-semibold">Scroll</span>
        <div class="w-6 h-10 border border-white/40 rounded-full flex items-start justify-center p-1.5">
          <div class="w-1.5 h-1.5 bg-white rounded-full animate-scroll-dot"></div>
        </div>
      </button>
    </section>

    <!-- Platform Stats Section -->
    <section v-if="!statsLoading" class="py-12 md:py-16 bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-2 sm:gap-6">
          <div class="bg-emerald-50 rounded-xl sm:rounded-2xl p-2 sm:p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-8 h-8 sm:w-12 sm:h-12 bg-emerald-100 rounded-lg sm:rounded-xl flex items-center justify-center mx-auto mb-1 sm:mb-3">
              <i class="pi pi-flag text-sm sm:text-xl text-emerald-700"></i>
            </div>
            <p class="text-base sm:text-3xl font-bold text-gray-900">{{ platformStats.campaigns }}</p>
            <p class="text-[10px] sm:text-sm text-gray-500 mt-0 sm:mt-1">Total Kampanye</p>
          </div>
          <div class="bg-blue-50 rounded-xl sm:rounded-2xl p-2 sm:p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-8 h-8 sm:w-12 sm:h-12 bg-blue-100 rounded-lg sm:rounded-xl flex items-center justify-center mx-auto mb-1 sm:mb-3">
              <i class="pi pi-users text-sm sm:text-xl text-blue-700"></i>
            </div>
            <p class="text-base sm:text-3xl font-bold text-gray-900">{{ platformStats.backers }}</p>
            <p class="text-[10px] sm:text-sm text-gray-500 mt-0 sm:mt-1">Backer Terdaftar</p>
          </div>
          <div class="bg-purple-50 rounded-xl sm:rounded-2xl p-2 sm:p-6 text-center hover:shadow-md transition-shadow">
            <div class="w-8 h-8 sm:w-12 sm:h-12 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center mx-auto mb-1 sm:mb-3">
              <i class="pi pi-wallet text-sm sm:text-xl text-purple-700"></i>
            </div>
            <p class="text-base sm:text-3xl font-bold text-gray-900">{{ formatCollected(platformStats.collected) }}</p>
            <p class="text-[10px] sm:text-sm text-gray-500 mt-0 sm:mt-1">Dana Terkumpul</p>
          </div>
        </div>
      </div>
    </section>

    <section ref="howItWorksSection" class="relative py-16 md:py-24 lg:py-32 bg-white overflow-hidden">
      <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-emerald-50/80 to-transparent"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div ref="headingRef" class="text-center mb-10 md:mb-16 lg:mb-20">
          <div class="inline-block">
            <h2 class="text-2xl sm:text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4 transition-all duration-700 ease-out" :class="headingRevealed ? 'reveal-text-visible' : 'reveal-text'">
              Bagaimana Cara Kerjanya?
            </h2>
          </div>
          <div class="inline-block max-w-2xl mx-auto">
            <p class="text-slate-500 text-base md:text-lg leading-relaxed transition-all duration-700 ease-out" :class="headingRevealed ? 'reveal-text-visible' : 'reveal-text'" style="transition-delay: 150ms;">
              Platform crowdfunding terpercaya dengan sistem escrow otomatis, transparansi penuh, dan pengalaman dukungan yang terasa lebih aman.
            </p>
          </div>
        </div>

        <div class="perspective-container grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
          <div
            v-for="(card, index) in howItWorksCards"
            :key="index"
            class="card-3d group relative p-5 md:p-8 bg-slate-50/90 backdrop-blur rounded-[1.75rem] border border-slate-200/70 shadow-[0_25px_70px_-35px_rgba(15,23,42,0.35)] transition-all duration-700"
            :data-index="index"
            :style="{ transitionDelay: `${index * 140}ms` }"
            :class="{ 'card-visible': visibleCards[index] }"
          >
            <div class="card-3d-inner">
              <div class="w-12 h-12 md:w-14 md:h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors duration-300">
                <i :class="card.icon" class="text-xl md:text-2xl text-emerald-700"></i>
              </div>
              <h3 class="text-lg md:text-xl font-bold text-slate-800 mb-3">{{ card.title }}</h3>
              <p class="text-slate-500 text-sm leading-relaxed">{{ card.description }}</p>
              <div class="mt-6 flex items-center gap-2 text-emerald-600 text-sm font-medium opacity-0 group-hover:opacity-100 transition-all duration-300">
                <span>Pelajari lebih lanjut</span>
                <i class="pi pi-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 md:py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900">Kampanye Terbaru</h2>
            <p class="text-slate-500 mt-2">Jelajahi kampanye crowdfunding terbaru dari berbagai sektor</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>

        <!-- Loading State - Skeleton -->
        <div v-if="latestCampaignsLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
          <CampaignCardSkeleton v-for="n in 3" :key="'skeleton-' + n" />
        </div>

        <!-- Empty State -->
        <div v-else-if="latestCampaigns.length === 0" class="text-center py-16">
          <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <i class="pi pi-inbox text-2xl text-gray-300"></i>
          </div>
          <p class="text-gray-500 font-medium">Belum ada kampanye</p>
          <p class="text-gray-400 text-sm mt-1">Kampanye terbaru akan muncul di sini</p>
        </div>

        <!-- Campaign Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
          <CampaignCard
            v-for="campaign in latestCampaigns.slice(0, 3)"
            :key="campaign.id"
            :campaign="campaign"
            :showBackingButton="false"
          />
        </div>

        <div class="text-center mt-10 sm:hidden">
          <router-link :to="{ name: 'CampaignList' }" class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>
      </div>
    </section>

    <section v-if="!authStore.isAuthenticated" class="bg-gradient-to-r from-emerald-700 to-emerald-600 py-12 md:py-16">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-6">
          <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center">
            <i class="pi pi-wallet text-3xl text-white"></i>
          </div>
        </div>
        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-4">Mulai Kampanye</h3>
        <p class="text-emerald-100 mb-8 max-w-2xl mx-auto">Jelajahi kampanye UMKM terbaik dan berikan dukungan dana sesuai kemampuan Anda.</p>
        <router-link :to="{ name: 'Register' }" class="inline-flex items-center gap-2 bg-white text-emerald-700 font-bold px-6 py-3 sm:px-10 sm:py-4 rounded-2xl shadow-lg hover:bg-emerald-50 transition-all duration-300 hover:-translate-y-0.5 no-underline">
          <i class="pi pi-user-plus"></i>
          Daftar Sekarang
        </router-link>
      </div>
    </section>

    <section v-else class="py-12 md:py-16 bg-gradient-to-r from-emerald-700 to-emerald-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6 md:mb-10">
          <div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2">Jelajahi Kampanye</h3>
            <p class="text-emerald-100 text-sm">Temukan dan dukung kampanye yang sesuai dengan minat Anda</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-2.5 rounded-2xl transition-all duration-300 no-underline">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm"></i>
          </router-link>
        </div>

        <div v-if="liveCampaignsLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <CampaignCardSkeleton v-for="n in 3" :key="'live-skeleton-' + n" />
        </div>

        <div v-else-if="liveCampaigns.length === 0" class="text-center py-12 bg-white/10 rounded-2xl">
          <i class="pi pi-inbox text-4xl text-white/40 mb-3 block"></i>
          <p class="text-white/70 text-sm">Belum ada data</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <CampaignCard
            v-for="campaign in liveCampaigns.slice(0, 3)"
            :key="campaign.id"
            :campaign="campaign"
            @backing="goToCampaignDetail"
          />
        </div>

        <div class="text-center mt-8 sm:hidden">
          <router-link :to="{ name: 'CampaignList' }" class="inline-flex items-center gap-2 text-white font-semibold hover:text-emerald-100 transition-colors">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm"></i>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import CampaignCard from '@/components/CampaignCard.vue'
import CampaignCardSkeleton from '@/components/CampaignCardSkeleton.vue'
import api from '@/services/api'
import * as campaignService from '@/services/campaignService'
import { formatCollected } from '@/utils/formatter'

const router = useRouter()
const authStore = useAuthStore()

let headingObserver = null
let cardObserver = null

const howItWorksSection = ref(null)
const headingRef = ref(null)
const headingRevealed = ref(false)
const visibleCards = ref([false, false, false])

const howItWorksCards = [
  {
    icon: 'pi pi-shield',
    title: 'Daftar & Verifikasi',
    description: 'Buat akun dan lengkapi profil Anda untuk mulai berpartisipasi dalam crowdfunding.',
  },
  {
    icon: 'pi pi-chart-line',
    title: 'Pilih & Dukung',
    description: 'Jelajahi kampanye UMKM terbaik dan berikan dukungan dana sesuai kemampuan Anda.',
  },
  {
    icon: 'pi pi-globe',
    title: 'Nikmati Hasilnya',
    description: 'Pantau perkembangan kampanye dan dapatkan reward eksklusif dari kreator.',
  },
]

// ── Cache module-level variables ─────────────────────────────
// These persist across component mount/unmount cycles so
// navigating away and back can reuse cached data immediately.
let _statsCache = null
let _latestCache = null
let _liveCache = null
let _fetching = false

const platformStats = ref({ campaigns: 0, backers: 0, collected: 0 })
const statsLoading = ref(true)

const latestCampaigns = ref([])
const latestCampaignsLoading = ref(true)

const liveCampaigns = ref([])
const liveCampaignsLoading = ref(true)

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

function goToCampaignDetail(campaign) {
  router.push(`/campaigns/${campaign.slug}?dukung=1`)
}

function scrollToHowItWorks() {
  if (!howItWorksSection.value) return
  howItWorksSection.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

/**
 * Eager fetch — runs immediately when the module is loaded (before mount).
 * 1. Restore cached data instantly (if available) → skeleton disappears immediately.
 * 2. Always fetch fresh data in the background → updates UI when done.
 */
// Restore cached data instantly
if (_statsCache) {
  platformStats.value = _statsCache
  statsLoading.value = false
}
if (_latestCache) {
  latestCampaigns.value = _latestCache
  latestCampaignsLoading.value = false
}
if (_liveCache) {
  liveCampaigns.value = _liveCache
  liveCampaignsLoading.value = false
}

// Always fetch fresh data in background (skip if already fetching)
if (!_fetching) {
  _fetching = true
  ;(async () => {
    try {
      const [statsRes, latestRes] = await Promise.all([
        api.get('/platform/stats').catch(() => null),
        campaignService.getCampaigns({ sort: 'newest' }).catch(() => null),
      ])

      // Stats
      if (statsRes) {
        const d = statsRes.data?.data
        _statsCache = {
          campaigns: d?.total_campaigns || 0,
          backers: d?.total_backers || 0,
          collected: d?.total_collected || 0,
        }
        platformStats.value = _statsCache
      }
      statsLoading.value = false

      // Latest campaigns
      if (latestRes) {
        // latestRes = { success: true, data: paginator }
        // paginator = { data: [...campaigns], current_page, last_page, total, ... }
        const all = latestRes?.data?.data || latestRes?.data || []
        _latestCache = all
        latestCampaigns.value = Array.isArray(all) ? all : []
      }
      latestCampaignsLoading.value = false

      // Live campaigns (authenticated only)
      if (authStore.isAuthenticated) {
        const liveRes = await campaignService.getCampaigns().catch(() => null)
        if (liveRes) {
          const liveData = liveRes?.data?.data || liveRes?.data || []
          _liveCache = Array.isArray(liveData) ? liveData : []
          liveCampaigns.value = _liveCache
        }
        liveCampaignsLoading.value = false
      } else {
        liveCampaignsLoading.value = false
      }
    } catch (e) {
      statsLoading.value = false
      latestCampaignsLoading.value = false
      liveCampaignsLoading.value = false
    } finally {
      _fetching = false
    }
  })()
}

onMounted(() => {
  headingObserver = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        headingRevealed.value = true
        headingObserver?.disconnect()
      }
    },
    { threshold: 0.3 }
  )

  if (headingRef.value) {
    headingObserver.observe(headingRef.value)
  }

  const cardElements = Array.from(document.querySelectorAll('.card-3d'))
  cardObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return

        const index = Number(entry.target.dataset.index)
        if (!Number.isNaN(index) && !visibleCards.value[index]) {
          visibleCards.value[index] = true
        }

        cardObserver?.unobserve(entry.target)
      })
    },
    { threshold: 0.2 }
  )

  cardElements.forEach((el) => cardObserver.observe(el))
})

onUnmounted(() => {
  _fetching = false
  headingObserver?.disconnect()
  headingObserver = null

  cardObserver?.disconnect()
  cardObserver = null
})
</script>

<style scoped>
.perspective-container {
  perspective: 1200px;
  perspective-origin: center;
}

.card-3d {
  transform-style: preserve-3d;
  transform: translate3d(0, 28px, 0) rotateX(18deg) rotateY(-8deg) scale(0.96);
  opacity: 0;
  will-change: transform, opacity;
}

.card-3d.card-visible {
  opacity: 1;
  transform: translate3d(0, 0, 0) rotateX(0deg) rotateY(0deg) scale(1);
}

.card-3d:hover {
  transform: rotateX(-6deg) rotateY(8deg) translateZ(24px) scale(1.02);
  box-shadow: 0 30px 80px -30px rgba(16, 185, 129, 0.35);
  border-color: rgba(16, 185, 129, 0.35);
}

.card-3d-inner {
  transform: translateZ(18px);
}

.reveal-text {
  opacity: 0;
  transform: translateY(24px) scale(0.98);
  clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
}

.reveal-text-visible {
  opacity: 1;
  transform: translateY(0) scale(1);
  clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
}

.animate-hero-bounce {
  animation: hero-bounce 1.8s ease-in-out infinite;
}

.animate-scroll-dot {
  animation: scroll-dot 1.6s ease-in-out infinite;
}

.parallax-blur {
  will-change: transform;
  transform: translate3d(0, 0, 0);
}

@keyframes hero-bounce {
  0%,
  100% {
    transform: translate3d(-50%, 0, 0);
  }
  50% {
    transform: translate3d(-50%, 8px, 0);
  }
}

@keyframes scroll-dot {
  0%,
  100% {
    transform: translateY(0);
    opacity: 0.65;
  }
  50% {
    transform: translateY(10px);
    opacity: 1;
  }
}
</style>