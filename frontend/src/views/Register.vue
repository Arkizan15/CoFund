<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50 px-4 py-8">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md border border-gray-100">
      
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-600 mb-3">
          <i class="pi pi-user-plus text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Register</h2>
        <p class="text-sm text-gray-500 mt-2">Create your account to get started</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        
        <!-- Name Field -->
        <div class="flex flex-col gap-1.5">
          <label for="name" class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
          <span class="p-input-icon-left w-full">
            <i class="pi pi-user text-gray-400"></i>
            <InputText 
              id="name" 
              v-model="form.name" 
              placeholder="Nama Lengkap Anda" 
              class="w-full p-inputtext-sm" 
              :class="{ 'p-invalid': errors.name }"
              required 
            />
          </span>
          <small v-if="errors.name" class="p-error text-xs">{{ errors.name[0] }}</small>
        </div>

        <!-- Email Field -->
        <div class="flex flex-col gap-1.5">
          <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
          <span class="p-input-icon-left w-full">
            <i class="pi pi-envelope text-gray-400"></i>
            <InputText 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="nama@email.com" 
              class="w-full p-inputtext-sm" 
              :class="{ 'p-invalid': errors.email }"
              required 
            />
          </span>
          <small v-if="errors.email" class="p-error text-xs">{{ errors.email[0] }}</small>
        </div>

        <!-- Password Field (Using PrimeVue Password component for native eye icon) -->
        <div class="flex flex-col gap-1.5">
          <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
          <Password 
            id="password" 
            v-model="form.password" 
            toggleMask 
            :feedback="false" 
            placeholder="Minimal 6 karakter" 
            class="w-full" 
            inputClass="w-full p-inputtext-sm" 
            :class="{ 'p-invalid': errors.password }"
            required 
          />
          <small v-if="errors.password" class="p-error text-xs">{{ errors.password[0] }}</small>
          
          <!-- Password Strength Indicator -->
          <div v-if="form.password" class="mt-1">
            <div class="flex gap-1 mb-1">
              <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 1 ? passwordStrength.color : 'bg-gray-200'"></div>
              <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 2 ? passwordStrength.color : 'bg-gray-200'"></div>
              <div class="h-1.5 flex-1 rounded-full transition-colors duration-300" :class="passwordStrength.score >= 3 ? passwordStrength.color : 'bg-gray-200'"></div>
            </div>
            <p class="text-xs font-medium" :class="passwordStrength.textColor">Kekuatan Password: {{ passwordStrength.label }}</p>
          </div>
        </div>

        <!-- Confirm Password Field (Using PrimeVue Password component) -->
        <div class="flex flex-col gap-1.5">
          <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Konfirmasi Password</label>
          <Password 
            id="password_confirmation" 
            v-model="form.password_confirmation" 
            toggleMask 
            :feedback="false" 
            class="w-full" 
            inputClass="w-full p-inputtext-sm" 
            :class="{ 'p-invalid': errors.password_confirmation }"
            required 
          />
          <small v-if="errors.password_confirmation" class="p-error text-xs">{{ errors.password_confirmation[0] }}</small>
        </div>

        <!-- Global Error Message -->
        <div v-if="globalError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 flex items-center gap-2">
          <i class="pi pi-exclamation-circle"></i>
          <span>{{ globalError }}</span>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 flex items-center gap-2">
          <i class="pi pi-check-circle"></i>
          <span>{{ successMessage }}</span>
        </div>

        <!-- Submit Button -->
        <Button 
          type="submit" 
          :label="loading ? 'Mendaftar...' : 'Register'" 
          :icon="loading ? 'pi pi-spin pi-spinner' : 'pi pi-user-plus'" 
          :disabled="loading" 
          class="w-full p-button-sm bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg transition-colors duration-200 border-none mt-2" 
        />
      </form>

      <div class="text-center mt-6 text-sm text-gray-600">
        Sudah memiliki akun? 
        <router-link to="/login" class="font-semibold text-green-600 hover:text-green-700 hover:underline transition-colors ml-1">Login di Sini</router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password'; // Import PrimeVue Password component
import Button from 'primevue/button';

const router = useRouter();

// Form Data
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// UI & State Management
const loading = ref(false);
const successMessage = ref('');
const globalError = ref('');
const errors = reactive({}); // Stores Laravel field-specific validation errors

// Password Strength Indicator Logic
const passwordStrength = computed(() => {
  const password = form.password;
  if (!password) return { score: 0, label: '', color: '', textColor: '' };

  let score = 0;
  if (password.length >= 8) score++;
  if (password.length >= 12) score++;
  if (/[A-Z]/.test(password)) score++; // Has uppercase
  if (/[0-9]/.test(password)) score++; // Has number
  if (/[^A-Za-z0-9]/.test(password)) score++; // Has special character

  if (score <= 2) return { score: 1, label: 'Lemah', color: 'bg-red-500', textColor: 'text-red-600' };
  if (score <= 4) return { score: 2, label: 'Sedang', color: 'bg-yellow-500', textColor: 'text-yellow-600' };
  return { score: 3, label: 'Kuat', color: 'bg-green-500', textColor: 'text-green-600' };
});

const handleRegister = async () => {
  // Reset states before submission
  loading.value = true;
  globalError.value = '';
  successMessage.value = '';
  Object.keys(errors).forEach(key => delete errors[key]);

  try {
    const response = await api.post('/register', form);
    successMessage.value = response.data.message || 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi.';
    
    // Auto-redirect to login page after 2 seconds
    setTimeout(() => {
      router.push('/login');
    }, 2000);
    
  } catch (error) {
    if (error.response?.status === 422) {
      // Handle Laravel Validation Errors
      Object.assign(errors, error.response.data.errors);
    } else {
      // Handle Global/Server Errors
      globalError.value = error.response?.data?.message || 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
    }
  } finally {
    loading.value = false;
  }
};
</script>