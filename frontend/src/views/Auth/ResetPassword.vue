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
        <!-- Invalid/Expired Token State -->
        <template v-if="invalidToken">
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-500 mb-4">
              <i class="pi pi-exclamation-triangle text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Link Tidak Valid</h2>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
              Link reset password tidak valid atau telah kedaluwarsa. Silakan minta link reset baru.
            </p>
          </div>
          <router-link :to="{ name: 'ForgotPassword' }" class="block">
            <Button
              label="Minta Link Baru"
              icon="pi pi-refresh"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm"
            />
          </router-link>
        </template>

        <!-- Success State -->
        <template v-else-if="resetSuccess">
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mb-4 animate-success-pop">
              <i class="pi pi-check-circle text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Password Berhasil Diubah</h2>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">
              Password akun Anda telah berhasil diperbarui. Silakan masuk dengan password baru.
            </p>
          </div>
          <router-link :to="{ name: 'Login' }" class="block">
            <Button
              label="Masuk Sekarang"
              icon="pi pi-sign-in"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm"
            />
          </router-link>
        </template>

        <!-- Reset Form -->
        <template v-else>
          <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mb-4">
              <i class="pi pi-lock-open text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Buat Password Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Password minimal 8 karakter untuk keamanan akun Anda</p>
          </div>

          <form @submit.prevent="handleResetPassword" class="space-y-5">
            <div class="flex flex-col gap-1.5">
              <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
              <div class="relative">
                <i class="pi pi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm z-10"></i>
                <InputText
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="nama@email.com"
                  class="w-full !rounded-xl !py-3 !pl-10 !text-sm !bg-white !cursor-not-allowed"
                  :class="{ 'p-invalid': errors.email }"
                  disabled
                  required
                />
              </div>
              <small v-if="errors.email" class="p-error text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle text-[10px]"></i>{{ errors.email[0] }}
              </small>
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="password" class="text-sm font-semibold text-gray-700">Password Baru</label>
              <div class="relative">
                <i class="pi pi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm z-10"></i>
                <InputText
                  id="password"
                  v-model="form.password"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  class="w-full !rounded-xl !py-3 !pl-10 !text-sm !bg-white"
                  :class="{ 'p-invalid': errors.password }"
                  minlength="8"
                  required
                />
              </div>
              <small v-if="errors.password" class="p-error text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle text-[10px]"></i>{{ errors.password[0] }}
              </small>

              <div v-if="form.password" class="mt-1">
                <div class="flex gap-1 mb-1">
                  <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 1 ? passwordStrength.color : 'bg-gray-200'"></div>
                  <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 2 ? passwordStrength.color : 'bg-gray-200'"></div>
                  <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 3 ? passwordStrength.color : 'bg-gray-200'"></div>
                </div>
                <p class="text-xs font-medium" :class="passwordStrength.textColor">Kekuatan Sandi: {{ passwordStrength.label }}</p>
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Konfirmasi Password Baru</label>
              <div class="relative">
                <i class="pi pi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm z-10"></i>
                <InputText
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Ulangi password baru"
                  class="w-full !rounded-xl !py-3 !pl-10 !text-sm !bg-white"
                  :class="{ 'p-invalid': errors.password_confirmation }"
                  minlength="8"
                  required
                />
              </div>
              <small v-if="errors.password_confirmation" class="p-error text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle text-[10px]"></i>{{ errors.password_confirmation[0] }}
              </small>
            </div>

            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-[15px] text-sm text-red-600 flex items-start gap-3">
              <i class="pi pi-exclamation-circle mt-0.5"></i>
              <span>{{ errorMessage }}</span>
            </div>

            <Button
              type="submit"
              :label="loading ? 'Memproses...' : 'Simpan Password Baru'"
              :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-save'"
              :disabled="loading"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm transition-all duration-200"
            />
          </form>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import authService from '@/services/authService'

const route = useRoute()

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({})
const resetSuccess = ref(false)
const invalidToken = ref(false)

// Read token & email from URL query params
const token = ref('')
const emailFromUrl = ref('')

onMounted(() => {
  token.value = route.query.token || ''
  emailFromUrl.value = route.query.email || ''
  form.email = emailFromUrl.value

  if (!token.value) {
    invalidToken.value = true
  }
})

const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return { score: 0, label: '', color: '', textColor: '' }

  let score = 0
  if (password.length >= 8) score++
  if (password.length >= 12) score++
  if (/[A-Z]/.test(password)) score++
  if (/[0-9]/.test(password)) score++
  if (/[^A-Za-z0-9]/.test(password)) score++

  if (score <= 2) return { score: 1, label: 'Lemah', color: 'bg-red-500', textColor: 'text-red-600' }
  if (score <= 4) return { score: 2, label: 'Sedang', color: 'bg-yellow-500', textColor: 'text-yellow-600' }
  return { score: 3, label: 'Kuat', color: 'bg-green-500', textColor: 'text-green-600' }
})

const handleResetPassword = async () => {
  loading.value = true
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    await authService.resetPassword({
      token: token.value,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    resetSuccess.value = true
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors)
    } else {
      errorMessage.value = error.response?.data?.message || 'Gagal mereset password. Link mungkin sudah kedaluwarsa.'
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
