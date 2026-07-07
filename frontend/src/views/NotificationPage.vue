<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Notifikasi</h1>
        <p class="text-gray-500 text-sm mt-1">Pesan dan pemberitahuan untuk akun Anda</p>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <i class="pi pi-spin pi-spinner text-4xl text-emerald-600"></i>
      </div>

      <div v-else-if="notifications.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
        <i class="pi pi-inbox text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-500 text-lg">Belum ada notifikasi</p>
        <p class="text-gray-400 text-sm mt-1">Notifikasi akan muncul di sini saat ada aktivitas baru</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="(notification, idx) in notifications"
          :key="notification.id"
          class="animate-fade-in transition-all duration-200"
          :style="{ animationDelay: idx * 50 + 'ms' }"
        >
          <div
            class="bg-white rounded-2xl shadow-sm border p-4 md:p-5"
            :class="!notification.read_at ? 'border-l-4 border-l-emerald-500 bg-emerald-50/30' : 'border-gray-100'"
          >
            <div class="flex items-start gap-4">
              <!-- ChatBubble-style avatar -->
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm ring-2 ring-white"
                :class="getIconBg(notification.type)"
              >
                <i :class="getIcon(notification.type)" class="text-sm"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-semibold text-gray-800">{{ notification.title || 'Notifikasi' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ notification.body || 'Tidak ada pesan.' }}</p>
                  </div>
                  <Badge v-if="!notification.read_at" value="Baru" severity="success" class="!bg-emerald-100 !text-emerald-700 !text-xs !px-2 !py-0.5 !rounded-full flex-shrink-0" />
                </div>
                <div class="flex items-center justify-between mt-3">
                  <span class="text-xs text-gray-400">{{ formatDate(notification.created_at) }}</span>
                  <Button
                    v-if="!notification.read_at"
                    label="Tandai Dibaca"
                    icon="pi pi-check"
                    class="p-button-text p-button-sm !text-emerald-600 !p-1 !text-xs touch-target"
                    @click="markAsRead(notification)"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center pt-4">
          <Button
            v-if="hasUnread"
            label="Tandai Semua Dibaca"
            icon="pi pi-check-circle"
            class="p-button-text text-emerald-600 touch-target"
            @click="markAllAsRead"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import { notificationService } from '@/services/notificationService'

const notifications = ref([])
const loading = ref(true)

const hasUnread = computed(() => notifications.value.some(n => !n.read_at))

onMounted(async () => {
  try {
    const res = await notificationService.getUnreadNotifications()
    notifications.value = res.data?.data || res.data || []
  } catch (e) {
    notifications.value = []
  } finally {
    loading.value = false
  }
})

function getIcon(type) {
  const icons = {
    backing: 'pi pi-heart',
    campaign: 'pi pi-flag',
    approval: 'pi pi-check-circle',
    rejection: 'pi pi-times-circle',
    refund: 'pi pi-refund',
    disbursement: 'pi pi-wallet',
    deadline: 'pi pi-clock',
    system: 'pi pi-info-circle',
  }
  return icons[type] || 'pi pi-bell'
}

function getIconBg(type) {
  const bg = {
    backing: 'bg-pink-100 text-pink-600',
    campaign: 'bg-blue-100 text-blue-600',
    approval: 'bg-emerald-100 text-emerald-600',
    rejection: 'bg-red-100 text-red-600',
    refund: 'bg-orange-100 text-orange-600',
    disbursement: 'bg-purple-100 text-purple-600',
    deadline: 'bg-yellow-100 text-yellow-600',
    system: 'bg-gray-100 text-gray-600',
  }
  return bg[type] || 'bg-emerald-100 text-emerald-600'
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function markAsRead(notification) {
  try {
    await notificationService.patchMarkAsRead(notification.id)
    notification.read_at = new Date().toISOString()
  } catch (e) {
    // Silently fail
  }
}

async function markAllAsRead() {
  try {
    await notificationService.markAllAsRead()
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
  } catch (e) {
    // Silently fail
  }
}
</script>
