<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <div class="w-full max-w-md text-center">
      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Memproses Verifikasi...</h2>
        <p class="text-sm text-gray-500">Harap tunggu sebentar, kami sedang memverifikasi akun Anda.</p>
      </div>

      <!-- Success State -->
      <div v-else-if="success" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-check-circle text-3xl text-emerald-600"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">
          {{ alreadyVerified ? 'Email Sudah Diverifikasi' : 'Email Berhasil Diverifikasi!' }}
        </h2>
        <p class="text-sm text-gray-500 mb-6">
          {{ alreadyVerified
            ? 'Akun Anda sudah terverifikasi sebelumnya. Anda akan dialihkan ke Dashboard.'
            : 'Selamat! Email Anda telah berhasil diverifikasi. Anda akan dialihkan ke Dashboard.'
          }}
        </p>
        <i class="pi pi-spin pi-spinner text-xl text-emerald-600"></i>
      </div>

      <!-- Error State -->
      <div v-else class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-exclamation-circle text-3xl text-red-500"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Verifikasi Gagal</h2>
        <p class="text-sm text-gray-500 mb-6">{{ errorMessage }}</p>
        <router-link
          :to="{ name: 'Login' }"
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-200 no-underline shadow-sm"
        >
          <i class="pi pi-sign-in"></i>
          Masuk Manual
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const loading = ref(true)
const success = ref(false)
const alreadyVerified = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  const token = route.query.token
  const error = route.query.error
  const verified = route.query.verified

  // Handle error from backend redirect
  if (error) {
    loading.value = false
    errorMessage.value = error
    return
  }

  // No token — invalid URL
  if (!token) {
    loading.value = false
    errorMessage.value = 'Tautan verifikasi tidak valid. Token tidak ditemukan.'
    return
  }

  // Already verified flag
  if (verified === 'already') {
    alreadyVerified.value = true
  }

  // Save token to auth store
  authStore.token = token
  localStorage.setItem('token', token)

  // Fetch user data to complete login
  try {
    await authStore.fetchUser()
    success.value = true
    loading.value = false

    // Redirect to dashboard after brief pause
    setTimeout(() => {
      // Clear verification params from URL
      window.history.replaceState({}, document.title, '/dashboard')
      router.push({ name: 'Dashboard' })
    }, 1500)
  } catch (e) {
    loading.value = false
    errorMessage.value = 'Gagal memuat data pengguna. Silakan coba login manual.'
    // Clean up invalid token
    authStore.token = null
    authStore.user = null
    localStorage.removeItem('token')
  }
})
</script>
