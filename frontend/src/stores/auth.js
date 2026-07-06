import { defineStore } from 'pinia';
import authService from '@/services/authService';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    getUserRole: (state) => state.user?.role || 'guest',
  },

  actions: {
    async login(credentials) {
      try {
        const response = await authService.login(credentials);
        
        // Simpan ke state
        this.token = response.data.token;
        this.user = response.data.user;

        // Simpan ke localStorage agar tidak hilang saat refresh
        localStorage.setItem('token', this.token);
        localStorage.setItem('user', JSON.stringify(this.user));

        return response;
      } catch (error) {
        throw error;
      }
    },

    async register(userData) {
      try {
        // Registrasi biasanya tidak langsung login (menunggu verifikasi email)
        return await authService.register(userData);
      } catch (error) {
        throw error;
      }
    },

    async logout() {
      try {
        await authService.logout();
      } catch (error) {
        console.error('Logout server error:', error);
      } finally {
        // Hapus data di state dan storage apapun yang terjadi di server
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      }
    },

    async fetchUser() {
      if (!this.token) return;
      try {
        const response = await authService.getProfile();
        // Backend wraps user data in { success: true, data: { ... } }
        this.user = response.data.data || response.data;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (error) {
        if (error.response?.status === 401) {
          this.logout(); // Token tidak valid, paksa logout
        }
      }
    }
  }
});