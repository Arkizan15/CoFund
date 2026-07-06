<template>
  <div class="min-h-screen bg-slate-950 text-slate-900">
    <section class="relative isolate overflow-hidden bg-gradient-to-br from-emerald-800 via-emerald-700 to-green-500 text-white min-h-screen flex items-center">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.2),transparent_38%)]"></div>
        <div class="parallax-blur absolute top-8 left-8 w-64 h-64 rounded-full bg-white/20 blur-3xl" data-speed="0.16"></div>
        <div class="parallax-blur absolute bottom-8 right-8 w-80 h-80 rounded-full bg-emerald-200/20 blur-3xl" data-speed="0.28"></div>
        <div class="parallax-blur absolute top-1/3 right-1/4 w-56 h-56 rounded-full bg-emerald-300/15 blur-3xl" data-speed="0.34"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-10 w-full">
        <div class="grid md:grid-cols-[1.1fr_0.9fr] gap-12 items-center">
          <div class="space-y-7">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-medium text-emerald-50 backdrop-blur-sm">
              <i class="pi pi-bolt text-xs"></i>
              Crowdfunding modern untuk UMKM Indonesia
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-white">
              Mulai Perjalanan<br />
              <span class="text-emerald-100">Bersama CoFund</span>
            </h1>
            <p class="text-lg md:text-xl text-emerald-50/90 leading-relaxed max-w-xl">
              CoFund menghubungkan para pengusaha UMKM dengan pendukung finansial. Wujudkan ide bisnis Anda melalui crowdfunding yang transparan, aman, dan terpercaya.
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
              <router-link
                :to="{ name: 'CampaignList' }"
                class="inline-flex items-center gap-2 bg-white text-emerald-700 font-semibold px-8 py-3.5 rounded-2xl shadow-lg shadow-emerald-950/20 hover:bg-emerald-50 transition-all duration-300 hover:-translate-y-0.5"
              >
                <i class="pi pi-search"></i>
                Jelajahi Campaign
              </router-link>
              <router-link
                :to="{ name: 'Register' }"
                class="inline-flex items-center gap-2 border border-white/40 text-white font-semibold px-8 py-3.5 rounded-2xl bg-white/10 backdrop-blur-sm hover:bg-white/15 transition-all duration-300"
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

    <section ref="howItWorksSection" class="relative py-24 md:py-32 bg-white overflow-hidden">
      <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-emerald-50/80 to-transparent"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div ref="headingRef" class="text-center mb-16 md:mb-20">
          <div class="inline-block">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-slate-900 mb-4 transition-all duration-700 ease-out" :class="headingRevealed ? 'reveal-text-visible' : 'reveal-text'">
              Bagaimana Cara Kerjanya?
            </h2>
          </div>
          <div class="inline-block max-w-2xl mx-auto">
            <p class="text-slate-500 text-base md:text-lg leading-relaxed transition-all duration-700 ease-out" :class="headingRevealed ? 'reveal-text-visible' : 'reveal-text'" style="transition-delay: 150ms;">
              Platform crowdfunding terpercaya dengan sistem escrow otomatis, transparansi penuh, dan pengalaman dukungan yang terasa lebih aman.
            </p>
          </div>
        </div>

        <div class="perspective-container grid md:grid-cols-3 gap-6 md:gap-8">
          <div
            v-for="(card, index) in howItWorksCards"
            :key="index"
            class="card-3d group relative p-8 bg-slate-50/90 backdrop-blur rounded-[1.75rem] border border-slate-200/70 shadow-[0_25px_70px_-35px_rgba(15,23,42,0.35)] transition-all duration-700"
            :data-index="index"
            :style="{ transitionDelay: `${index * 140}ms` }"
            :class="{ 'card-visible': visibleCards[index] }"
          >
            <div class="card-3d-inner">
              <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors duration-300">
                <i :class="card.icon" class="text-2xl text-emerald-700"></i>
              </div>
              <h3 class="text-xl font-bold text-slate-800 mb-3">{{ card.title }}</h3>
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

    <section class="py-20 bg-slate-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900">Kampanye Unggulan</h2>
            <p class="text-slate-500 mt-2">Temukan peluang investasi terbaik dari berbagai sektor</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="campaign in featuredCampaigns" :key="campaign.id" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
            <div class="relative overflow-hidden h-48">
              <img :src="campaign.image" :alt="campaign.title" class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" />
              <div class="absolute top-3 left-3">
                <span class="bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                  {{ campaign.category }}
                </span>
              </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <router-link :to="`/campaigns/${campaign.slug}`" class="text-lg font-bold text-slate-800 hover:text-emerald-600 transition-colors line-clamp-2 mb-3">
                {{ campaign.title }}
              </router-link>

              <div class="mt-auto space-y-3">
                <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full transition-all duration-500" :style="{ width: campaign.progress + '%' }"></div>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-500">
                    Terkumpul <strong class="text-slate-800">{{ campaign.progress }}%</strong>
                  </span>
                  <span class="flex items-center gap-1.5 text-orange-600 font-medium">
                    <i class="pi pi-clock text-xs"></i>
                    {{ campaign.daysLeft }} hari lagi
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-10 sm:hidden">
          <router-link :to="{ name: 'CampaignList' }" class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>
      </div>
    </section>

    <section v-if="!authStore.isAuthenticated" class="bg-gradient-to-r from-emerald-700 to-emerald-600 py-16">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-3 mb-6">
          <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center">
            <i class="pi pi-wallet text-3xl text-white"></i>
          </div>
        </div>
        <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Mulai Kampanye</h3>
        <p class="text-emerald-100 mb-8 max-w-2xl mx-auto">Jelajahi kampanye UMKM terbaik dan berikan dukungan dana sesuai kemampuan Anda.</p>
        <router-link :to="{ name: 'Register' }" class="inline-flex items-center gap-2 bg-white text-emerald-700 font-bold px-10 py-4 rounded-2xl shadow-lg hover:bg-emerald-50 transition-all duration-300 hover:-translate-y-0.5 no-underline">
          <i class="pi pi-user-plus"></i>
          Daftar Sekarang
        </router-link>
      </div>
    </section>

    <section v-else class="py-16 bg-gradient-to-r from-emerald-700 to-emerald-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
          <div>
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">Jelajahi Kampanye</h3>
            <p class="text-emerald-100 text-sm">Temukan dan dukung kampanye yang sesuai dengan minat Anda</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-2.5 rounded-2xl transition-all duration-300 no-underline">
            Lihat Semua
            <i class="pi pi-arrow-right text-sm"></i>
          </router-link>
        </div>

        <div v-if="liveCampaignsLoading" class="flex items-center justify-center py-12">
          <i class="pi pi-spin pi-spinner text-3xl text-white/70"></i>
        </div>

        <div v-else-if="liveCampaigns.length === 0" class="text-center py-12 bg-white/10 rounded-2xl">
          <i class="pi pi-inbox text-4xl text-white/40 mb-3 block"></i>
          <p class="text-white/70 text-sm">Belum ada data</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="campaign in liveCampaigns.slice(0, 3)"
            :key="campaign.id"
            class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col"
          >
            <div class="relative overflow-hidden h-44 bg-slate-100">
              <img
                src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=400&q=80"
                :alt="campaign.title"
                class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
              />
              <div class="absolute top-3 left-3">
                <span class="bg-emerald-600/90 text-white text-[10px] font-semibold px-2.5 py-1 rounded-full shadow-sm">
                  {{ campaign.category?.name || 'Umum' }}
                </span>
              </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <h4 class="font-bold text-slate-800 text-sm line-clamp-2 mb-3">{{ campaign.title }}</h4>
              <div class="mt-auto space-y-2.5">
                <ProgressBar :value="progressPercent(campaign)" class="!h-7 !rounded-full" />
                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-500">
                    {{ formatCurrency(campaign.collected_amount || 0) }}
                  </span>
                  <span class="flex items-center gap-1 text-orange-600 font-medium">
                    <i class="pi pi-clock text-[10px]"></i>
                    {{ remainingDays(campaign.deadline) }} hari lagi
                  </span>
                </div>
              </div>
              <router-link
                :to="`/campaigns/${campaign.slug}`"
                class="mt-4 block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm py-2.5 rounded-2xl transition-all duration-300 shadow-sm no-underline"
              >
                Dukung Sekarang
                <i class="pi pi-arrow-right ml-1.5 text-xs"></i>
              </router-link>
            </div>
          </div>
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
import { useAuthStore } from '@/stores/auth'
import ProgressBar from 'primevue/progressbar'
import * as campaignService from '@/services/campaignService'
import Lenis from 'lenis'

const authStore = useAuthStore()

let lenis = null
let rafId = null
let headingObserver = null
let cardObserver = null
let parallaxElements = []

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

const featuredCampaigns = ref([
  {
    id: 1,
    title: 'Ekspansi Cabang Kopi Skena Sektor 4',
    slug: 'ekspansi-cabang-kopi-skena',
    category: 'F&B',
    progress: 75,
    daysLeft: 14,
    image: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&q=80',
  },
  {
    id: 2,
    title: 'Pendanaan Bahan Baku Konveksi Taylor ID',
    slug: 'pendanaan-bahan-baku-konveksi',
    category: 'Fashion',
    progress: 40,
    daysLeft: 22,
    image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&q=80',
  },
  {
    id: 3,
    title: 'Digitalisasi Distribusi Sembako Murah Mandiri',
    slug: 'digitalisasi-distribusi-sembako',
    category: 'Ritel',
    progress: 90,
    daysLeft: 5,
    image: 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=600&q=80',
  },
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

function scrollToHowItWorks() {
  if (!howItWorksSection.value) return

  if (lenis) {
    lenis.scrollTo(howItWorksSection.value, {
      offset: -80,
      duration: 1.4,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    })
    return
  }

  howItWorksSection.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(() => {
  lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
    gestureOrientation: 'vertical',
    touchMultiplier: 1.2,
  })

  parallaxElements = Array.from(document.querySelectorAll('.parallax-blur'))

  const updateParallax = () => {
    if (!lenis) return

    const scrollY = lenis.scroll || 0
    parallaxElements.forEach((el) => {
      const speed = Number.parseFloat(el.dataset.speed || '0.3') || 0.3
      const offset = scrollY * speed
      el.style.transform = `translate3d(0, ${offset}px, 0)`
    })
  }

  const raf = (time) => {
    lenis.raf(time)
    updateParallax()
    rafId = window.requestAnimationFrame(raf)
  }

  rafId = window.requestAnimationFrame(raf)

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

  if (authStore.isAuthenticated) {
    ;(async () => {
      try {
        const data = await campaignService.getCampaigns()
        liveCampaigns.value = data?.data || data || []
      } catch (e) {
        liveCampaigns.value = []
      } finally {
        liveCampaignsLoading.value = false
      }
    })()
  } else {
    liveCampaignsLoading.value = false
  }
})

onUnmounted(() => {
  if (rafId) {
    window.cancelAnimationFrame(rafId)
    rafId = null
  }

  headingObserver?.disconnect()
  headingObserver = null

  cardObserver?.disconnect()
  cardObserver = null

  if (lenis) {
    lenis.destroy()
    lenis = null
  }

  parallaxElements = []
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