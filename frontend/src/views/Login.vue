<template>
  <div class="login-container">
    <h2>Login CoFund</h2>
    <form @submit.prevent="handleLogin">
      <input v-model="form.email" type="email" placeholder="Email" required />
      <InputText />
      <input v-model="form.password" type="password" placeholder="Password" required />
      <button type="submit">Masuk</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import authService from '../services/authService';
import InputText from 'primevue/inputtext';

const form = ref({
    email: '',
    password: ''
});

const handleLogin = async () => {
    try {
        const response = await authService.login(form.value);
        // Simpan token ke localStorage sesuai standar token-based auth
        localStorage.setItem('token', response.data.token);
        alert('Login Berhasil!');
        // Pengalihan halaman bisa menggunakan vue-router jika sudah disetup
    } catch (error) {
        alert(error.response?.data?.message || 'Login Gagal');
    }
};
</script>