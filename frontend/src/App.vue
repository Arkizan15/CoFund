<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    <Toast position="top-right" />
    <Navbar />
    <main class="flex-1">
      <router-view v-slot="{ Component, route }">
        <Transition name="page" mode="out-in">
          <component :is="Component" :key="route.path" />
        </Transition>
      </router-view>
    </main>
    <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-400">
      <div class="max-w-7xl mx-auto px-4">
        &copy; {{ new Date().getFullYear() }} CoFund. {{ $t('common.all') }} rights reserved.
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from '@/components/Navbar.vue'
import Toast from 'primevue/toast'
</script>
