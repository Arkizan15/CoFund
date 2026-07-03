<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50 px-4">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md border border-gray-100">
      
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 mb-3">
          <i class="pi pi-wallet text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Login</h2>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-5">
        
        <div class="flex flex-col gap-2">
          <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
          <span class="p-input-icon-left w-full">
            <i class="pi pi-envelope text-gray-400"></i>
            <InputText id="email" v-model="form.email" type="email" placeholder="nama@email.com" class="w-full p-inputtext-sm border-gray-300 focus:border-green-500 focus:ring-green-500" required />
          </span>
        </div>

        <div class="flex flex-col gap-2">
          <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
          <span class="p-input-icon-left w-full">
            <i class="pi pi-lock text-gray-400"></i>
            <InputText id="password" v-model="form.password" type="password"  class="w-full p-inputtext-sm border-gray-300 focus:border-green-500 focus:ring-green-500" required />
          </span>
        </div>

        <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 flex items-center gap-2 animate-fadein">
          <i class="pi pi-exclamation-circle"></i>
          <span>{{ errorMessage }}</span>
        </div>

        <Button type="submit" :label="loading ? 'Loading...' : 'Login'" icon="pi pi-sign-in" :disabled="loading" class="w-full p-button-sm bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg transition-colors duration-200 border-none" />
      </form>

      <div class="text-center mt-6 text-sm text-gray-600">
        Belum punya akun CoFund? 
        <router-link to="/register" class="font-semibold text-green-600 hover:text-green-700 hover:underline transition-colors ml-1">Buat Akun Baru</router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useAuthStore } from '../stores/auth'; // 1. Import Pinia Store
import InputText from 'primevue/inputtext';
import Button from 'primevue/button'; // Import Button PrimeVue

const authStore = useAuthStore(); // 2. Inisialisasi Store

const form = reactive({
  email: '',
  password: ''
});

const loading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';
  
  try {
    // 3. Komunikasi via Store (Picu Actions dari Pinia store, bukan authService langsung)
    await authStore.login(form);
    
    // Jika sukses, arahkan user ke halaman utama/dashboard (misal menggunakan router jika ada)
    alert('Login Berhasil!');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Login gagal, periksa kembali akun Anda.';
  } finally {
    loading.value = false;
  }
};
</script>