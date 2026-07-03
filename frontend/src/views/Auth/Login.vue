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
            <i class="pi pi-wallet text-2xl"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Masuk</h2>
          <p class="text-sm text-gray-500 mt-1">Selamat datang kembali di CoFund</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-envelope text-gray-400"></i>
              <InputText
                id="email"
                v-model="form.email"
                type="email"
                placeholder="nama@email.com"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.email }"
                required
              />
            </span>
            <small v-if="errors.email" class="p-error text-xs">{{ errors.email[0] }}</small>
          </div>

          <div class="flex flex-col gap-1.5">
            <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
            <span class="p-input-icon-left w-full">
              <i class="pi pi-lock text-gray-400"></i>
              <InputText
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Masukkan password"
                class="w-full !rounded-xl !py-3 !text-sm"
                :class="{ 'p-invalid': errors.password }"
                required
              />
            </span>
            <small v-if="errors.password" class="p-error text-xs">{{ errors.password[0] }}</small>
          </div>

          <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 flex items-start gap-3">
            <i class="pi pi-exclamation-circle mt-0.5"></i>
            <span>{{ errorMessage }}</span>
          </div>

          <Button
            type="submit"
            :label="loading ? 'Memproses...' : 'Masuk'"
            :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-sign-in'"
            :disabled="loading"
            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold !py-3 !rounded-xl border-none shadow-sm transition-all duration-200"
          />
        </form>

        <div class="text-center mt-6 text-sm text-gray-500">
          Belum punya akun CoFund?
          <router-link :to="{ name: 'Register' }" class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors ml-1">
            Buat Akun Baru
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    await authStore.login(form)
    const redirect = route.query.redirect || { name: 'Dashboard' }
    router.push(typeof redirect === 'string' ? redirect : redirect)
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(errors, error.response.data.errors)
    } else {
      errorMessage.value = error.response?.data?.message || 'Login gagal, periksa kembali email dan password Anda.'
    }
  } finally {
    loading.value = false
  }
}
</script>
