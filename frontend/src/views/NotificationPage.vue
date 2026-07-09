<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Kotak Masuk</h1>
          <p class="text-gray-500 text-sm mt-1">Semua pemberitahuan dan pesan untuk akun Anda</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Admin: Send Announcement -->
          <Button
            v-if="isAdmin"
            label="Buat Pengumuman"
            icon="pi pi-megaphone"
            class="!bg-purple-600 !border-none hover:!bg-purple-700 !text-white !rounded-xl !text-sm !px-4"
            @click="announcementDialogVisible = true"
          />
          <Button
            v-if="hasUnread"
            label="Tandai Semua Dibaca"
            icon="pi pi-check-circle"
            class="p-button-text !text-emerald-600 !rounded-xl !text-sm"
            @click="markAllAsRead"
          />
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div
          v-for="tab in tabs"
          :key="tab.key"
          class="bg-white rounded-xl border p-3 sm:p-4 cursor-pointer transition-all duration-200"
          :class="activeTab === tab.key
            ? 'border-emerald-400 shadow-sm shadow-emerald-100 ring-1 ring-emerald-200'
            : 'border-gray-100 hover:border-emerald-200 hover:shadow-sm'"
          @click="switchTab(tab.key)"
        >
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 rounded-lg flex items-center justify-center"
              :class="tab.bgClass"
            >
              <i :class="tab.icon" class="text-xs" :style="{ color: tab.color }"></i>
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-gray-700 truncate">{{ tab.label }}</p>
              <p class="text-lg font-bold" :style="{ color: tab.color }">{{ tabCounts[tab.key] }}</p>
            </div>
          </div>
        </div>
      </div>

    
      

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24">
        <i class="pi pi-spin pi-spinner text-3xl text-emerald-500 mb-4"></i>
        <p class="text-gray-400 text-sm">Memuat notifikasi...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
          <i :class="emptyIcon" class="text-3xl text-gray-300"></i>
        </div>
        <p class="text-gray-500 text-lg font-medium">{{ emptyTitle }}</p>
        <p class="text-gray-400 text-sm mt-1">{{ emptyDesc }}</p>
      </div>

      <!-- Notification List -->
      <div v-else class="space-y-3">
        <div
          v-for="(notification, idx) in filteredNotifications"
          :key="notification.id"
          class="transition-all duration-200"
          :style="{ animationDelay: idx * 30 + 'ms' }"
        >
          <div
            class="bg-white rounded-2xl shadow-sm border p-4 md:p-5 hover:shadow-md transition-all duration-200"
            :class="[
              !notification.read_at
                ? 'border-l-4 border-l-emerald-500 bg-gradient-to-r from-emerald-50/50 to-white'
                : 'border-gray-100',
            ]"
          >
            <div class="flex items-start gap-3.5">
              <!-- Icon Avatar -->
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm ring-2 ring-white mt-0.5"
                :class="getIconBg(notification.type)"
              >
                <i :class="getIcon(notification.type)" class="text-sm"></i>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                      <p class="text-sm font-semibold text-gray-800 truncate">{{ notification.title }}</p>
                      <span
                        class="text-[10px] font-medium px-2 py-0.5 rounded-full flex-shrink-0"
                        :class="getTypeBadgeClass(notification.type)"
                      >
                        {{ getTypeLabel(notification.type) }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ notification.body }}</p>

                    <!-- Notification Data Links -->
                    <div v-if="notification.data?.campaign_slug" class="mt-2">
                      <router-link
                        :to="`/campaigns/${notification.data.campaign_slug}`"
                        class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-medium no-underline hover:underline"
                      >
                        <i class="pi pi-external-link text-[10px]"></i>
                        Lihat Kampanye
                      </router-link>
                    </div>
                  </div>

                  <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <span v-if="!notification.read_at" class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></span>
                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ formatTimeAgo(notification.created_at) }}</span>
                  </div>
                </div>

                <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-50">
                  <span class="text-xs text-gray-400">{{ formatDate(notification.created_at) }}</span>
                  <Button
                    v-if="!notification.read_at"
                    label="Tandai Dibaca"
                    icon="pi pi-check"
                    class="p-button-text !p-1 !text-xs !text-emerald-600 !rounded-lg"
                    @click="markAsRead(notification)"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More -->
        <div v-if="hasMore" class="text-center pt-4">
          <Button
            label="Muat Lebih Banyak"
            icon="pi pi-chevron-down"
            class="p-button-text !text-emerald-600 !rounded-xl"
            :loading="loadingMore"
            @click="loadMore"
          />
        </div>
      </div>
    </div>

    <!-- Admin: Send Announcement Dialog -->
    <Dialog
      v-model:visible="announcementDialogVisible"
      header="Buat Pengumuman"
      :modal="true"
      class="app-dialog !rounded-[15px] !bg-white !border-2 !border-purple-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto"
      :style="{ maxWidth: '550px' }"
      @show="onDialogShow"
    >
      <div class="space-y-4 py-2">
        <div class="flex items-start gap-3 p-3 bg-purple-50 rounded-xl border border-purple-200">
          <i class="pi pi-info-circle text-purple-500 text-lg mt-0.5"></i>
          <div>
            <p class="text-sm font-semibold text-purple-800">Pengumuman akan dikirim ke semua pengguna</p>
            <p class="text-xs text-purple-600 mt-0.5">Pastikan pesan yang Anda tulis informatif dan jelas.</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Judul Pengumuman <span class="text-red-400">*</span></label>
          <InputText
            v-model="announcementForm.title"
            placeholder="Contoh: Pembaruan Platform"
            class="w-full !rounded-xl !text-sm"
            :maxlength="200"
          />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Isi Pengumuman <span class="text-red-400">*</span></label>
          <Textarea
            v-model="announcementForm.body"
            rows="4"
            placeholder="Tulis pesan pengumuman di sini..."
            class="w-full !rounded-xl !text-sm"
            :maxlength="2000"
          />
          <small class="text-gray-400 text-xs text-right">{{ announcementForm.body.length }}/2000</small>
        </div>
        <div v-if="announcementError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-start gap-2">
          <i class="pi pi-exclamation-circle mt-0.5"></i>
          <span>{{ announcementError }}</span>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text !text-gray-500 !rounded-xl" @click="announcementDialogVisible = false" />
          <Button
            label="Kirim Pengumuman"
            icon="pi pi-send"
            class="!bg-purple-600 !border-none hover:!bg-purple-700 !text-white !rounded-xl !px-6"
            :loading="announcementLoading"
            @click="sendAnnouncement"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useGenieEffect } from '@/composables/useGenieEffect'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import { notificationService } from '@/services/notificationService'

const authStore = useAuthStore()
const toast = useToast()
const { onDialogShow } = useGenieEffect()

const isAdmin = computed(() => authStore.getUserRole === 'admin')

// ── Tabs ──────────────────────────────────────────────────────
const activeTab = ref('all')
const tabs = computed(() => {
  if (isAdmin.value) {
    return [
      { key: 'all', label: 'Semua', icon: 'pi pi-inbox', bgClass: 'bg-emerald-100', color: '#059669' },
      { key: 'campaign', label: 'Kampanye', icon: 'pi pi-flag', bgClass: 'bg-blue-100', color: '#2563eb' },
      { key: 'deadline', label: 'Deadline', icon: 'pi pi-clock', bgClass: 'bg-amber-100', color: '#d97706' },
      { key: 'account', label: 'Akun', icon: 'pi pi-users', bgClass: 'bg-red-100', color: '#dc2626' },
    ]
  }
  return [
    { key: 'all', label: 'Semua', icon: 'pi pi-inbox', bgClass: 'bg-emerald-100', color: '#059669' },
    { key: 'backing', label: 'Pendanaan', icon: 'pi pi-heart', bgClass: 'bg-pink-100', color: '#e11d48' },
    { key: 'campaign', label: 'Kampanye', icon: 'pi pi-flag', bgClass: 'bg-blue-100', color: '#2563eb' },
    { key: 'deadline', label: 'Deadline', icon: 'pi pi-clock', bgClass: 'bg-amber-100', color: '#d97706' },
    { key: 'announcement', label: 'Pengumuman', icon: 'pi pi-megaphone', bgClass: 'bg-purple-100', color: '#7c3aed' },
    { key: 'other', label: 'Lainnya', icon: 'pi pi-info-circle', bgClass: 'bg-gray-100', color: '#6b7280' },
  ]
})

const tabTypeMapping = computed(() => {
  if (isAdmin.value) {
    return {
      all: null,
      campaign: ['campaign_approved', 'campaign_rejected'],
      deadline: ['deadline', 'deadline_approaching'],
      account: ['creator_approved', 'creator_rejected', 'user_banned', 'user_unbanned'],
    }
  }
  return {
    all: null,
    backing: ['backing', 'backing_success', 'backing_received', 'escrow_received'],
    campaign: ['campaign', 'campaign_created', 'campaign_approved', 'campaign_rejected', 'campaign_update', 'campaign_disbursed', 'disbursement', 'settlement'],
    deadline: ['deadline', 'deadline_approaching'],
    announcement: ['announcement'],
    other: ['refund', 'system', 'approval', 'rejection', 'withdraw', 'top_up', 'creator_approved', 'creator_rejected', 'user_banned', 'user_unbanned'],
  }
})

// ── Type Filter ───────────────────────────────────────────────
const selectedType = ref(null)

const typeFilters = [
  { label: 'Semua', value: null, icon: 'pi pi-th-large' },
  { label: 'Pendanaan', value: 'backing', icon: 'pi pi-heart' },
  { label: 'Kampanye', value: 'campaign', icon: 'pi pi-flag' },
  { label: 'Deadline', value: 'deadline', icon: 'pi pi-clock' },
  { label: 'Pengumuman', value: 'announcement', icon: 'pi pi-megaphone' },
  { label: 'Sistem', value: 'system', icon: 'pi pi-cog' },
]

function toggleTypeFilter(value) {
  selectedType.value = selectedType.value === value ? null : value
}

// ── Notifications Data ────────────────────────────────────────
const notifications = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)

// Count per tab
const tabCounts = computed(() => {
  const counts = {}
  tabs.value.forEach(t => { counts[t.key] = 0 })
  notifications.value.forEach(n => {
    counts.all = (counts.all || 0) + 1
    for (const [tab, types] of Object.entries(tabTypeMapping.value)) {
      if (types && types.includes(n.type)) {
        counts[tab] = (counts[tab] || 0) + 1
        break
      }
    }
  })
  return counts
})

const filteredNotifications = computed(() => {
  let result = notifications.value

  // Apply tab filter
  const tabTypes = tabTypeMapping.value[activeTab.value]
  if (tabTypes) {
    result = result.filter(n => tabTypes.includes(n.type))
  }

  // Apply type filter
  if (selectedType.value) {
    result = result.filter(n => n.type === selectedType.value)
  }

  return result
})

const hasUnread = computed(() => notifications.value.some(n => !n.read_at))
const hasMore = computed(() => currentPage.value < lastPage.value)

const emptyIcon = computed(() => {
  const icons = {
    all: 'pi pi-inbox',
    backing: 'pi pi-heart',
    campaign: 'pi pi-flag',
    deadline: 'pi pi-clock',
    announcement: 'pi pi-megaphone',
    other: 'pi pi-info-circle',
  }
  return icons[activeTab.value] || 'pi pi-inbox'
})

const emptyTitle = computed(() => {
  const titles = {
    all: 'Belum ada notifikasi',
    backing: 'Belum ada aktivitas pendanaan',
    campaign: 'Belum ada notifikasi kampanye',
    deadline: 'Tidak ada deadline mendekat',
    announcement: 'Belum ada pengumuman',
    other: 'Tidak ada notifikasi lain',
  }
  return titles[activeTab.value] || 'Belum ada notifikasi'
})

const emptyDesc = computed(() => {
  const descs = {
    all: 'Notifikasi akan muncul di sini saat ada aktivitas baru',
    backing: 'Notifikasi terkait pendanaan kampanye akan muncul di sini',
    campaign: 'Notifikasi tentang kampanye Anda akan muncul di sini',
    deadline: 'Pengingat deadline kampanye akan muncul di sini',
    announcement: 'Pengumuman dari admin akan muncul di sini',
    other: 'Notifikasi sistem lainnya akan muncul di sini',
  }
  return descs[activeTab.value] || ''
})

// ── Tab Navigation ────────────────────────────────────────────
function switchTab(tabKey) {
  activeTab.value = tabKey
  selectedType.value = null
}

// ── API Calls ─────────────────────────────────────────────────
async function fetchNotifications(page = 1) {
  try {
    const res = await notificationService.getUnreadNotifications({ page })
    const data = res.data?.data || res.data || {}
    const items = data.data || data || []
    if (page === 1) {
      notifications.value = items
    } else {
      notifications.value = [...notifications.value, ...items]
    }
    currentPage.value = data.current_page || data.currentPage || 1
    lastPage.value = data.last_page || data.lastPage || 1
  } catch (e) {
    notifications.value = []
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

async function loadMore() {
  loadingMore.value = true
  await fetchNotifications(currentPage.value + 1)
}

// ── Mark as Read ──────────────────────────────────────────────
async function markAsRead(notification) {
  try {
    await notificationService.patchMarkAsRead(notification.id)
    notification.read_at = new Date().toISOString()
  } catch (e) {
    // Silent fail
  }
}

async function markAllAsRead() {
  try {
    await notificationService.markAllAsRead()
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Semua notifikasi ditandai dibaca.', life: 2000 })
  } catch (e) {
    // Silent fail
  }
}

// ── Announcement (Admin) ──────────────────────────────────────
const announcementDialogVisible = ref(false)
const announcementLoading = ref(false)
const announcementError = ref('')
const announcementForm = ref({ title: '', body: '' })

async function sendAnnouncement() {
  announcementError.value = ''
  if (!announcementForm.value.title.trim() || !announcementForm.value.body.trim()) {
    announcementError.value = 'Judul dan isi pengumuman wajib diisi.'
    return
  }

  announcementLoading.value = true
  try {
    const res = await notificationService.sendAnnouncement({
      title: announcementForm.value.title.trim(),
      body: announcementForm.value.body.trim(),
    })
    toast.add({
      severity: 'success',
      summary: 'Pengumuman Terkirim',
      detail: res.data?.message || 'Pengumuman berhasil dikirim ke semua pengguna.',
      life: 5000,
    })
    announcementDialogVisible.value = false
    announcementForm.value = { title: '', body: '' }
  } catch (error) {
    announcementError.value = error.response?.data?.message || 'Gagal mengirim pengumuman.'
  } finally {
    announcementLoading.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────
function getIcon(type) {
  const icons = {
    backing: 'pi pi-heart',
    backing_success: 'pi pi-heart',
    backing_received: 'pi pi-arrow-down',
    escrow_received: 'pi pi-shield',
    campaign: 'pi pi-flag',
    campaign_created: 'pi pi-plus-circle',
    campaign_approved: 'pi pi-check-circle',
    campaign_rejected: 'pi pi-times-circle',
    campaign_update: 'pi pi-megaphone',
    campaign_disbursed: 'pi pi-wallet',
    disbursement: 'pi pi-money-bill',
    settlement: 'pi pi-balance-scale',
    deadline: 'pi pi-clock',
    deadline_approaching: 'pi pi-alarm',
    announcement: 'pi pi-megaphone',
    refund: 'pi pi-refund',
    system: 'pi pi-info-circle',
    approval: 'pi pi-check-circle',
    rejection: 'pi pi-times-circle',
    withdraw: 'pi pi-arrow-up',
    top_up: 'pi pi-plus',
    creator_approved: 'pi pi-shield',
    creator_rejected: 'pi pi-times-circle',
    user_banned: 'pi pi-ban',
    user_unbanned: 'pi pi-check-circle',
  }
  return icons[type] || 'pi pi-bell'
}

function getIconBg(type) {
  const bg = {
    backing: 'bg-pink-100 text-pink-600',
    backing_success: 'bg-pink-100 text-pink-600',
    backing_received: 'bg-emerald-100 text-emerald-600',
    escrow_received: 'bg-emerald-100 text-emerald-600',
    campaign: 'bg-blue-100 text-blue-600',
    campaign_created: 'bg-blue-100 text-blue-600',
    campaign_approved: 'bg-emerald-100 text-emerald-600',
    campaign_rejected: 'bg-red-100 text-red-600',
    campaign_update: 'bg-violet-100 text-violet-600',
    campaign_disbursed: 'bg-purple-100 text-purple-600',
    disbursement: 'bg-purple-100 text-purple-600',
    settlement: 'bg-cyan-100 text-cyan-600',
    deadline: 'bg-amber-100 text-amber-600',
    deadline_approaching: 'bg-orange-100 text-orange-600',
    announcement: 'bg-purple-100 text-purple-600',
    refund: 'bg-orange-100 text-orange-600',
    system: 'bg-gray-100 text-gray-600',
    approval: 'bg-emerald-100 text-emerald-600',
    rejection: 'bg-red-100 text-red-600',
    withdraw: 'bg-rose-100 text-rose-600',
    top_up: 'bg-emerald-100 text-emerald-600',
    creator_approved: 'bg-emerald-100 text-emerald-600',
    creator_rejected: 'bg-red-100 text-red-600',
    user_banned: 'bg-red-100 text-red-600',
    user_unbanned: 'bg-emerald-100 text-emerald-600',
  }
  return bg[type] || 'bg-emerald-100 text-emerald-600'
}

function getTypeLabel(type) {
  const labels = {
    backing: 'Pendanaan',
    backing_success: 'Pendanaan',
    backing_received: 'Dana Masuk',
    escrow_received: 'Escrow',
    campaign: 'Kampanye',
    campaign_created: 'Kampanye Dibuat',
    campaign_approved: 'Disetujui',
    campaign_rejected: 'Ditolak',
    campaign_update: 'Pembaruan',
    campaign_disbursed: 'Pencairan',
    disbursement: 'Pencairan',
    settlement: 'Settlement',
    deadline: 'Deadline',
    deadline_approaching: 'Deadline Mendekat',
    announcement: 'Pengumuman',
    refund: 'Refund',
    system: 'Sistem',
    approval: 'Persetujuan',
    rejection: 'Penolakan',
    withdraw: 'Penarikan',
    top_up: 'Top Up',
    creator_approved: 'Kreator Disetujui',
    creator_rejected: 'Kreator Ditolak',
    user_banned: 'Akun Dinonaktifkan',
    user_unbanned: 'Akun Diaktifkan',
  }
  return labels[type] || type || '-'
}

function getTypeBadgeClass(type) {
  const classes = {
    backing: 'bg-pink-50 text-pink-700 border border-pink-200',
    backing_success: 'bg-pink-50 text-pink-700 border border-pink-200',
    escrow_received: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    campaign: 'bg-blue-50 text-blue-700 border border-blue-200',
    campaign_created: 'bg-blue-50 text-blue-700 border border-blue-200',
    campaign_approved: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    campaign_rejected: 'bg-red-50 text-red-700 border border-red-200',
    campaign_update: 'bg-violet-50 text-violet-700 border border-violet-200',
    campaign_disbursed: 'bg-purple-50 text-purple-700 border border-purple-200',
    disbursement: 'bg-purple-50 text-purple-700 border border-purple-200',
    deadline: 'bg-amber-50 text-amber-700 border border-amber-200',
    deadline_approaching: 'bg-orange-50 text-orange-700 border border-orange-200',
    announcement: 'bg-purple-50 text-purple-700 border border-purple-200',
    refund: 'bg-orange-50 text-orange-700 border border-orange-200',
    system: 'bg-gray-50 text-gray-600 border border-gray-200',
    creator_approved: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    creator_rejected: 'bg-red-50 text-red-700 border border-red-200',
    user_banned: 'bg-red-50 text-red-700 border border-red-200',
    user_unbanned: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
  }
  return classes[type] || 'bg-gray-50 text-gray-600 border border-gray-200'
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

function formatTimeAgo(dateStr) {
  if (!dateStr) return ''
  const now = new Date()
  const date = new Date(dateStr)
  const diffMs = now - date
  const diffMin = Math.floor(diffMs / 60000)
  const diffHour = Math.floor(diffMs / 3600000)
  const diffDay = Math.floor(diffMs / 86400000)

  if (diffMin < 1) return 'Baru saja'
  if (diffMin < 60) return `${diffMin}m yang lalu`
  if (diffHour < 24) return `${diffHour}j yang lalu`
  if (diffDay < 7) return `${diffDay}h yang lalu`
  return formatDate(dateStr)
}

// ── Lifecycle ─────────────────────────────────────────────────
onMounted(async () => {
  await fetchNotifications(1)
})
</script>
