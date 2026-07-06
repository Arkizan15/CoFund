<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <router-link :to="{ name: 'Home' }" class="inline-flex items-center gap-2 text-emerald-700 font-bold text-2xl no-underline mb-6">
          <i class="pi pi-wallet text-emerald-600"></i>
          <span>CoFund</span>
        </router-link>
      </div>

      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mb-4">
            <i class="pi pi-user-plus text-2xl"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">{{ $t('auth.registerTitle') }}</h2>
          <p class="text-sm text-gray-500 mt-1">{{ $t('auth.registerSubtitle') }}</p>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <div class="flex flex-col gap-1.5">
            <label for="name" class="text-sm font-semibold text-gray-700">{{ $t('auth.name') }}</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-user text-gray-400"></i>
              <InputText
                id="name"
                v-model="form.name"
                :placeholder="$t('auth.namePlaceholder')"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.name }"
                required
              />
            </span>
            <small v-if="errors.name" class="p-error text-xs">{{ errors.name[0] }}</small>
          </div>

          <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-semibold text-gray-700">{{ $t('auth.email') }}</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-envelope text-gray-400"></i>
              <InputText
                id="email"
                v-model="form.email"
                type="email"
                :placeholder="$t('auth.emailPlaceholder2')"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.email }"
                required
              />
            </span>
            <small v-if="errors.email" class="p-error text-xs">{{ errors.email[0] }}</small>
          </div>

          <div class="flex flex-col gap-1.5">
            <label for="password" class="text-sm font-semibold text-gray-700">{{ $t('auth.password') }}</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-lock text-gray-400"></i>
              <InputText
                id="password"
                v-model="form.password"
                type="password"
                :placeholder="$t('auth.passwordPlaceholder')"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.password }"
                minlength="6"
                required
              />
            </span>
            <small v-if="errors.password" class="p-error text-xs">{{ errors.password[0] }}</small>

            <div v-if="form.password" class="mt-1">
              <div class="flex gap-1 mb-1">
                <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 1 ? passwordStrength.color : 'bg-gray-200'"></div>
                <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 2 ? passwordStrength.color : 'bg-gray-200'"></div>
                <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 3 ? passwordStrength.color : 'bg-gray-200'"></div>
              </div>
              <p class="text-xs font-medium" :class="passwordStrength.textColor">{{ $t('auth.passwordStrength', { label: passwordStrength.label }) }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label for="password_confirmation" class="text-sm font-semibold text-gray-700">{{ $t('auth.passwordConfirmation') }}</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-lock text-gray-400"></i>
              <InputText
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                :placeholder="$t('auth.passwordConfirmPlaceholder')"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.password_confirmation }"
                minlength="6"
                required
              />
            </span>
            <small v-if="errors.password_confirmation" class="p-error text-xs">{{ errors.password_confirmation[0] }}</small>
          </div>

          <div v-if="globalError" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 flex items-start gap-3">
            <i class="pi pi-exclamation-circle mt-0.5"></i>
            <span>{{ globalError }}</span>
          </div>

          <div v-if="successMessage" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-start gap-3">
            <i class="pi pi-check-circle mt-0.5"></i>
            <span>{{ successMessage }}</span>
          </div>

          <Button
            type="submit"
            :label="loading ? $t('auth.processing') : $t('auth.registerNow')"
            :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-user-plus'"
            :disabled="loading"
            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm transition-all duration-200 mt-2"
          />
        </form>

        <div class="text-center mt-6 text-sm text-gray-500">
          {{ $t('auth.haveAccount') }}
          <router-link :to="{ name: 'Login' }" class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors ml-1">
            {{ $t('nav.login') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const successMessage = ref('')
const globalError = ref('')
const errors = reactive({})

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

const handleRegister = async () => {
  loading.value = true
  globalError.value = ''
  successMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    const response = await authStore.register(form)
    successMessage.value = response.data.message || 'Pendaftaran berhasil! Silakan cek email Anda.'
    setTimeout(() => {
      router.push({ name: 'Login' })
    }, 2000)
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors)
    } else {
      globalError.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>