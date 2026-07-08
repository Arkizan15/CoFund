<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    <Toast position="top-right" />
    <Navbar :isDarkMode="isDarkMode" @toggleDarkMode="toggleDarkMode" />
    <main class="flex-1">
      <router-view v-slot="{ Component, route }">
        <Transition name="page" mode="out-in">
          <component :is="Component" :key="route.path" />
        </Transition>
      </router-view>
    </main>
    <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
      <div class="max-w-7xl mx-auto px-4">
        &copy; {{ new Date().getFullYear() }} CoFund. Hak cipta dilindungi.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'
import Toast from 'primevue/toast'

const authStore = useAuthStore()

// Dark mode
const isDarkMode = ref(localStorage.getItem('dark-mode') === 'true')
function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('dark-mode', isDarkMode.value)
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

// Expose for Navbar/other components
if (isDarkMode.value) {
  document.documentElement.classList.add('dark')
}

onMounted(() => {
  if (authStore.token) {
    authStore.fetchUser()
  }
})
</script>
