<template>
  <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Left: Logo & Desktop Navigation -->
        <div class="flex items-center gap-8">
          <router-link
            :to="{ name: 'Home' }"
            class="flex items-center gap-2 text-emerald-700 font-bold text-xl no-underline"
          >
            <i class="pi pi-wallet text-emerald-600 text-2xl"></i>
            <span class="hidden sm:inline">CoFund</span>
          </router-link>

          <div class="hidden md:flex items-center gap-6">
            <router-link
              :to="{ name: 'Home' }"
              class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors duration-200 no-underline"
              active-class="text-emerald-600 font-semibold"
            >
              <i class="pi pi-home mr-1.5 text-xs"></i>Home
            </router-link>
            <router-link
              :to="{ name: 'CampaignList' }"
              class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors duration-200 no-underline"
              active-class="text-emerald-600 font-semibold"
            >
              <i class="pi pi-th-large mr-1.5 text-xs"></i>Campaign
            </router-link>
          </div>
        </div>

        <!-- Right: Auth Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
          <!-- Guest Mode -->
          <template v-if="!authStore.isAuthenticated">
            <router-link :to="{ name: 'Login' }">
              <Button
                label="Masuk"
                icon="pi pi-sign-in"
                severity="success"
                class="p-button-text p-button-sm !text-emerald-600 !font-medium !p-2 !px-3"
              />
            </router-link>
            <router-link :to="{ name: 'Register' }">
              <Button
                label="Daftar"
                icon="pi pi-user-plus"
                class="p-button-sm !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-medium !p-2 !px-3 shadow-sm"
              />
            </router-link>
          </template>

          <!-- Authenticated Mode -->
          <template v-else>
            <!-- Notification Bell -->
            <div class="relative">
              <router-link
                :to="{ name: 'Notifications' }"
                class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-emerald-50 transition-colors"
              >
                <i class="pi pi-bell text-lg text-gray-600 hover:text-emerald-600 transition-colors"></i>
                <span
                  v-if="unreadCount > 0"
                  class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-red-500 border-2 border-white rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow-sm"
                >
                  {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
              </router-link>
            </div>

            <!-- Dashboard Quick Link (desktop only) -->
            <router-link
              :to="{ name: 'Dashboard' }"
              class="hidden md:flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-emerald-600 transition-colors no-underline px-2 py-1.5 rounded-lg hover:bg-emerald-50"
            >
              <i class="pi pi-chart-bar text-xs"></i>
              Dashboard
            </router-link>

            <!-- Profile Dropdown -->
            <div class="relative" ref="dropdownRef">
              <button
                @click="dropdownOpen = !dropdownOpen"
                class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 rounded-full p-0.5 transition-all hover:bg-emerald-50"
              >
                <div class="w-9 h-9 rounded-full bg-emerald-100 border-2 border-emerald-400 flex items-center justify-center overflow-hidden">
                  <i class="pi pi-user text-emerald-600 text-sm"></i>
                </div>
                <span class="hidden sm:inline text-sm font-medium text-gray-700 max-w-[100px] truncate">{{ authStore.user?.name || 'User' }}</span>
                <i class="pi pi-chevron-down text-xs text-gray-400 transition-transform" :class="{ 'rotate-180': dropdownOpen }"></i>
              </button>

              <Transition name="fade">
                <div
                  v-if="dropdownOpen"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                >
                  <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ authStore.user?.name || 'User' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ authStore.user?.email || '' }}</p>
                  </div>

                  <router-link
                    :to="{ name: 'Dashboard' }"
                    @click="dropdownOpen = false"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors no-underline"
                  >
                    <i class="pi pi-chart-bar text-xs"></i> Dashboard
                  </router-link>
                  <router-link
                    :to="{ name: 'Profile' }"
                    @click="dropdownOpen = false"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors no-underline"
                  >
                    <i class="pi pi-user text-xs"></i> Profil Saya
                  </router-link>
                  <router-link
                    :to="{ name: 'Notifications' }"
                    @click="dropdownOpen = false"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors no-underline"
                  >
                    <i class="pi pi-bell text-xs"></i> Notifikasi
                    <span v-if="unreadCount > 0" class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ unreadCount }}</span>
                  </router-link>
                  <router-link
                    v-if="isAdmin"
                    :to="{ name: 'AdminDashboard' }"
                    @click="dropdownOpen = false"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors no-underline"
                  >
                    <i class="pi pi-shield text-xs"></i> Panel Admin
                  </router-link>
                  <hr class="my-1 border-gray-100" />
                  <button
                    @click="handleLogout"
                    class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <i class="pi pi-sign-out text-xs"></i> Logout
                  </button>
                </div>
              </Transition>
            </div>

            <!-- Mobile Hamburger -->
            <button
              @click="mobileOpen = !mobileOpen"
              class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <i class="pi" :class="mobileOpen ? 'pi-times' : 'pi-bars'"></i>
            </button>
          </template>
        </div>
      </div>

      <!-- Mobile Menu -->
      <Transition name="fade">
        <div v-if="mobileOpen" class="md:hidden border-t border-gray-100 py-4 space-y-2">
          <router-link
            :to="{ name: 'Home' }"
            @click="mobileOpen = false"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
            active-class="text-emerald-600 bg-emerald-50 font-semibold"
          >
            <i class="pi pi-home text-xs"></i> Home
          </router-link>
          <router-link
            :to="{ name: 'CampaignList' }"
            @click="mobileOpen = false"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
            active-class="text-emerald-600 bg-emerald-50 font-semibold"
          >
            <i class="pi pi-th-large text-xs"></i> Campaign
          </router-link>
          <template v-if="authStore.isAuthenticated">
            <hr class="border-gray-100" />
            <router-link
              :to="{ name: 'Dashboard' }"
              @click="mobileOpen = false"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
              active-class="text-emerald-600 bg-emerald-50 font-semibold"
            >
              <i class="pi pi-chart-bar text-xs"></i> Dashboard
            </router-link>
            <router-link
              :to="{ name: 'Profile' }"
              @click="mobileOpen = false"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
              active-class="text-emerald-600 bg-emerald-50 font-semibold"
            >
              <i class="pi pi-user text-xs"></i> Profil Saya
            </router-link>
            <router-link
              :to="{ name: 'Notifications' }"
              @click="mobileOpen = false"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
              active-class="text-emerald-600 bg-emerald-50 font-semibold"
            >
              <i class="pi pi-bell text-xs"></i> Notifikasi
              <span v-if="unreadCount > 0" class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ unreadCount }}</span>
            </router-link>
            <router-link
              v-if="isAdmin"
              :to="{ name: 'AdminDashboard' }"
              @click="mobileOpen = false"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors no-underline"
              active-class="text-emerald-600 bg-emerald-50 font-semibold"
            >
              <i class="pi pi-shield text-xs"></i> Panel Admin
            </router-link>
            <hr class="border-gray-100" />
            <button
              @click="handleLogout"
              class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              <i class="pi pi-sign-out text-xs"></i> Logout
            </button>
          </template>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'
import { notificationService } from '@/services/notificationService'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const dropdownOpen = ref(false)
const mobileOpen = ref(false)
const dropdownRef = ref(null)
const unreadCount = ref(0)

const isAdmin = computed(() => authStore.getUserRole === 'admin')

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchUnreadCount()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function fetchUnreadCount() {
  if (!authStore.isAuthenticated) return
  try {
    const res = await notificationService.getUnreadNotifications()
    const notifications = res.data?.data || res.data || []
    unreadCount.value = notifications.filter(n => !n.read_at).length
  } catch (e) {
    // Silently fail
  }
}

const handleLogout = async () => {
  dropdownOpen.value = false
  mobileOpen.value = false
  try {
    await authStore.logout()
    toast.add({ severity: 'success', summary: 'Logout Berhasil', detail: 'Anda telah keluar.', life: 3000 })
    router.push({ name: 'Home' })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal Logout', detail: 'Terjadi kesalahan.', life: 3000 })
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
