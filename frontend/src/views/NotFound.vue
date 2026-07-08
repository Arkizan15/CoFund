<template>
  <div class="min-h-screen bg-white flex items-center justify-center py-16 px-4">
    <div class="max-w-md w-full text-center">
      <!-- Animated 404 Illustration -->
      <div class="relative mb-8 flex justify-center">
        <div class="relative">
          <div class="text-[120px] md:text-[160px] font-extrabold text-emerald-100 leading-none select-none">
            404
          </div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-24 h-24 md:w-28 md:h-28 bg-emerald-100 rounded-full flex items-center justify-center animate-float">
              <i class="pi pi-question-circle text-5xl md:text-6xl text-emerald-500"></i>
            </div>
          </div>
        </div>
      </div>

      <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">
        Halaman Tidak Ditemukan
      </h1>
      <p class="text-gray-500 mb-8 leading-relaxed">
        Halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau tidak pernah ada.
        Periksa kembali URL atau kembali ke beranda.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <router-link
          :to="{ name: 'Home' }"
          class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-2xl transition-all duration-300 hover:-translate-y-0.5 shadow-sm no-underline"
        >
          <i class="pi pi-home"></i>
          Kembali ke Beranda
        </router-link>
        <router-link
          :to="{ name: 'CampaignList' }"
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-emerald-300 hover:text-emerald-700 text-gray-700 font-semibold px-6 py-3 rounded-2xl transition-all duration-300 no-underline"
        >
          <i class="pi pi-search"></i>
          Jelajahi Kampanye
        </router-link>
      </div>

      <!-- Suggestive links -->
      <div class="mt-12 pt-8 border-t border-gray-100">
        <p class="text-sm text-gray-400 mb-4">Coba halaman berikut:</p>
        <div class="flex flex-wrap justify-center gap-3">
          <router-link
            :to="{ name: 'Login' }"
            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1.5"
          >
            <i class="pi pi-sign-in text-xs"></i>
            Masuk
          </router-link>
          <router-link
            :to="{ name: 'Register' }"
            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1.5"
          >
            <i class="pi pi-user-plus text-xs"></i>
            Daftar
          </router-link>
          <router-link
            :to="{ name: 'Dashboard' }"
            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1.5"
          >
            <i class="pi pi-chart-bar text-xs"></i>
            Dashboard
          </router-link>
          <router-link
            :to="{ name: 'Profile' }"
            class="text-sm text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1.5"
          >
            <i class="pi pi-user text-xs"></i>
            Profil
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// 404 Page — No special logic needed
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-16px); }
}
.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>
