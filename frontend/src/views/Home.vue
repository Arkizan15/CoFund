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
              Bangun Masa Depan<br />
              <span class="text-emerald-200">Bersama Komunitas</span>
            </h1>
            <p class="text-lg md:text-xl text-emerald-50 leading-relaxed max-w-lg">
              CoFund menghubungkan para pengusaha UMKM dengan pendukung finansial. Wujudkan ide bisnis Anda melalui crowdfunding yang transparan, aman, dan terpercaya.
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
              <router-link
                :to="{ name: 'CampaignList' }"
                class="inline-flex items-center gap-2 bg-white text-emerald-700 font-semibold px-8 py-3.5 rounded-xl shadow-lg hover:bg-emerald-50 transition-all duration-200 hover:-translate-y-0.5"
              >
                <i class="pi pi-search"></i>
                Jelajahi Kampanye
              </router-link>
              <router-link
                :to="{ name: 'Register' }"
                class="inline-flex items-center gap-2 border-2 border-white/40 text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-white/10 transition-all duration-200"
              >
                <i class="pi pi-user-plus"></i>
                Mulai Sekarang
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
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Mengapa CoFund?</h2>
          <p class="text-gray-500 max-w-2xl mx-auto">Platform crowdfunding terpercaya dengan sistem escrow otomatis dan transparansi penuh</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-shield text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Perlindungan Escrow</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              Dana donasi diamankan dalam rekening escrow terverifikasi. Pencairan hanya dilakukan saat kampanye mencapai target yang ditentukan.
            </p>
          </div>

          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-chart-line text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Transparansi Penuh</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              Pantau perkembangan kampanye secara real-time. Setiap transaksi tercatat dan dapat diaudit oleh seluruh pihak.
            </p>
          </div>

          <div class="group p-8 bg-slate-50 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-emerald-200 transition-colors">
              <i class="pi pi-globe text-2xl text-emerald-700"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-3">Dampak Nyata</h3>
            <p class="text-gray-500 text-sm leading-relaxed">
              Setiap kontribusi Anda berdampak langsung pada pertumbuhan UMKM dan perekonomian masyarakat lokal.
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
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Kampanye Populer</h2>
            <p class="text-gray-500 mt-2">Temukan peluang investasi terbaik dari berbagai sektor</p>
          </div>
          <router-link :to="{ name: 'CampaignList' }" class="hidden sm:inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition-colors group">
            Lihat Semua
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
                    Terkumpul <strong class="text-gray-800">{{ campaign.progress }}%</strong>
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
            Lihat Semua Kampanye
            <i class="pi pi-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-emerald-700 to-emerald-600 py-16">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Siap Mengembangkan Portofolio Investasi Anda?</h3>
        <p class="text-emerald-100 mb-8 max-w-2xl mx-auto">Bergabunglah bersama CoFund dan temukan berbagai peluang investasi sektor riil terbaik untuk masa depan Anda.</p>
        <router-link :to="{ name: 'Register' }" class="inline-flex items-center gap-2 bg-white text-emerald-700 font-bold px-10 py-4 rounded-xl shadow-lg hover:bg-emerald-50 transition-all duration-200 hover:-translate-y-0.5">
          <i class="pi pi-user-plus"></i>
          Daftar Sekarang
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'

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
</script>
