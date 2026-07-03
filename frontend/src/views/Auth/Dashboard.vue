<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Selamat datang kembali, <strong class="text-emerald-700">{{ authStore.user?.name || 'User' }}</strong></p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-wallet text-xl text-emerald-700"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500">Saldo Tersedia</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(userBalance) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-heart text-xl text-blue-700"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Donasi</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalDonated) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-flag text-xl text-purple-700"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500">Kampanye Diikuti</p>
              <p class="text-2xl font-bold text-gray-900">{{ supportedCampaigns.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">Kampanye yang Didukung</h3>
          <div v-if="supportedCampaigns.length === 0" class="text-center py-10">
            <i class="pi pi-inbox text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 text-sm">Belum ada kampanye yang didukung</p>
            <router-link :to="{ name: 'CampaignList' }" class="inline-block mt-3 text-emerald-600 font-semibold text-sm hover:text-emerald-700 transition-colors">
              Jelajahi Kampanye
            </router-link>
          </div>
          <div v-else class="space-y-4">
            <div v-for="(item, idx) in supportedCampaigns" :key="idx" class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl">
              <div class="w-14 h-14 rounded-xl bg-gray-200 overflow-hidden flex-shrink-0">
                <img :src="item.image || 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=100&q=80'" alt="" class="w-full h-full object-cover" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ item.title }}</p>
                <p class="text-xs text-gray-400">{{ item.date }}</p>
              </div>
              <span class="text-sm font-semibold text-emerald-700">{{ formatCurrency(item.amount) }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">Profil Saya</h3>
          <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
              <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="pi pi-user text-2xl text-emerald-700"></i>
              </div>
              <div>
                <p class="text-lg font-semibold text-gray-800">{{ authStore.user?.name || 'User' }}</p>
                <p class="text-sm text-gray-500">{{ authStore.user?.email || '' }}</p>
              </div>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Role</span>
              <span class="font-medium text-gray-800 capitalize">{{ authStore.user?.role || 'Backer' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">Status Akun</span>
              <Badge value="Aktif" severity="success" />
            </div>
            <hr class="border-gray-100" />
            <Button
              label="Logout"
              icon="pi pi-sign-out"
              class="p-button-text w-full text-red-600 hover:bg-red-50 !justify-start !rounded-xl"
              @click="handleLogout"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const userBalance = computed(() => authStore.user?.balance || 12500000)
const totalDonated = ref(750000)
const supportedCampaigns = ref([
  { title: 'Ekspansi Cabang Kopi Skena', amount: 150000, date: '2 hari lalu', image: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=100&q=80' },
  { title: 'Digitalisasi Distribusi Sembako', amount: 500000, date: '1 minggu lalu', image: 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=100&q=80' }
])

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    toast.add({ severity: 'success', summary: 'Logout Berhasil', detail: 'Anda telah keluar.', life: 3000 })
    router.push({ name: 'Home' })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal Logout', detail: 'Terjadi kesalahan.', life: 3000 })
  }
}
</script>
