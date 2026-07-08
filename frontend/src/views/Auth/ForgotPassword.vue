<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <router-link :to="{ name: 'Home' }" class="inline-flex items-center gap-2 text-emerald-700 font-bold text-2xl no-underline mb-6">
          <i class="pi pi-wallet text-emerald-600"></i>
          <span>CoFund</span>
        </router-link>
      </div>

      <div class="bg-white rounded-[15px] shadow-lg border border-emerald-200 p-8">
        <!-- Initial State -->
        <template v-if="!emailSent">
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mb-4">
              <i class="pi pi-lock-open text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Lupa Password</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan email Anda, kami akan kirim link reset password</p>
          </div>

          <form @submit.prevent="handleForgotPassword" class="space-y-5">
            <div class="flex flex-col gap-1.5">
              <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
              <div class="relative">
                <i class="pi pi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm z-10"></i>
                <InputText
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="nama@email.com"
                  class="w-full !rounded-xl !py-3 !pl-10 !text-sm !bg-white"
                  :class="{ 'p-invalid': errors.email }"
                  required
                />
              </div>
              <small v-if="errors.email" class="p-error text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle text-[10px]"></i>{{ errors.email[0] }}
              </small>
            </div>

            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-[15px] text-sm text-red-600 flex items-start gap-3">
              <i class="pi pi-exclamation-circle mt-0.5"></i>
              <span>{{ errorMessage }}</span>
            </div>

            <Button
              type="submit"
              :label="loading ? 'Mengirim...' : 'Kirim Link Reset'"
              :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-send'"
              :disabled="loading"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm transition-all duration-200"
            />
          </form>
        </template>

        <!-- Success State -->
        <template v-else>
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mb-4 animate-success-pop">
              <i class="pi pi-check-circle text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Cek Email Anda</h2>
            <p class="text-sm text-gray-500 mt-3 leading-relaxed">
              Link reset password telah dikirim ke <strong class="text-gray-700">{{ form.email }}</strong>.
            </p>
            <p class="text-xs text-gray-400 mt-2">
              Tidak menerima email? Periksa folder spam atau
              <button
                type="button"
                class="text-emerald-600 hover:text-emerald-700 font-semibold underline decoration-emerald-300 hover:decoration-emerald-500 transition-colors bg-transparent border-none cursor-pointer"
                @click="emailSent = false; loading = false; errorMessage = ''"
              >
                kirim ulang
              </button>
            </p>
          </div>

          <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-[15px] text-sm text-emerald-700 flex items-start gap-3">
            <i class="pi pi-info-circle mt-0.5 flex-shrink-0"></i>
            <span>Link reset password akan kedaluwarsa dalam 60 menit. Jika Anda tidak meminta reset password, abaikan email ini.</span>
          </div>
        </template>

        <div class="text-center mt-6 text-sm text-gray-500">
          <router-link :to="{ name: 'Login' }" class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors inline-flex items-center gap-1.5">
            <i class="pi pi-arrow-left text-xs"></i>
            Kembali ke Login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import authService from '@/services/authService'

const form = reactive({
  email: '',
})

const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({})
const emailSent = ref(false)

const handleForgotPassword = async () => {
  loading.value = true
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    await authService.forgotPassword({ email: form.email })
    emailSent.value = true
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors)
    } else {
      errorMessage.value = error.response?.data?.message || 'Gagal mengirim email reset password. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@keyframes success-pop {
  0% { transform: scale(0.5); opacity: 0; }
  60% { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}
.animate-success-pop {
  animation: success-pop 0.5s ease-out;
}
</style>
