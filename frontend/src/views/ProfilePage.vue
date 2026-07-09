<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Profil Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola informasi akun dan saldo dompet Anda</p>
      </div>

      <!-- Profile Banner -->
      <div class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-4 md:p-6 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
          <div class="relative w-20 h-20 rounded-full flex-shrink-0 ring-4 ring-emerald-50 group">
            <img
              v-if="authStore.user?.avatar_url"
              :src="authStore.user.avatar_url"
              :alt="authStore.user?.name"
              class="w-full h-full rounded-full object-cover"
            />
            <div v-else class="w-full h-full rounded-full bg-emerald-100 flex items-center justify-center">
              <i class="pi pi-user text-3xl text-emerald-700"></i>
            </div>
            <label
              class="absolute inset-0 rounded-full bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity"
            >
              <i class="pi pi-camera text-white text-lg"></i>
              <input
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleAvatarUpload"
              />
            </label>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
              <h2 class="text-2xl font-bold text-gray-900 truncate">{{ authStore.user?.name || 'User' }}</h2>
              <Badge
                :value="roleLabel"
                :severity="roleSeverity"
                class="!px-3 !py-1 !rounded-full !text-xs !font-semibold"
              />
            </div>
            <p class="text-gray-500 text-sm mt-1">{{ authStore.user?.email }}</p>
            <p class="text-xs text-gray-400 mt-1">
              <i class="pi pi-calendar mr-1"></i>Bergabung sejak {{ formatDate(authStore.user?.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Admin Profile Card -->
      <div v-if="isAdmin" class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-6">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center">
            <i class="pi pi-shield text-2xl text-emerald-700"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-800">Akun Admin</h3>
            <p class="text-xs text-gray-400">Anda login sebagai Administrator platform</p>
          </div>
        </div>
        <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-100">
          <p class="text-sm text-emerald-800 leading-relaxed">
            Akun admin tidak memiliki dompet digital dan tidak dapat membuat atau mendanai kampanye. 
            Gunakan Panel Admin untuk mengelola pengguna, kampanye, dan pengaturan platform.
          </p>
        </div>
        <router-link :to="{ name: 'AdminDashboard' }">
          <Button
            label="Buka Panel Admin"
            icon="pi pi-external-link"
            class="w-full mt-5 !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3.5 !rounded-xl shadow-sm"
          />
        </router-link>
      </div>

      <!-- Non-Admin content -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Wallet Card -->
        <div class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-4 md:p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-wallet text-lg text-emerald-700"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Dompet Digital</h3>
              <p class="text-xs text-gray-400">Kelola saldo donasi Anda</p>
            </div>
          </div>

          <div class="bg-emerald-700 rounded-[15px] p-6 text-white mb-6 shadow-sm">
            <p class="text-emerald-100 text-sm font-medium flex items-center gap-2">
              <i class="pi pi-credit-card"></i>Saldo Saat Ini
            </p>
            <p class="text-3xl font-bold mt-1 tracking-tight">{{ formatCurrency(currentBalance) }}</p>
          </div>

          <div class="space-y-4">
            <!-- Top Up Dropdown Header -->
            <button
              class="w-full flex items-center justify-between py-3 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-all duration-150 cursor-pointer"
              @click="showTopUp = !showTopUp"
            >
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-200 rounded-lg flex items-center justify-center">
                  <i class="pi pi-arrow-up text-sm text-emerald-700"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800">Top Up Saldo</span>
              </div>
              <i
                class="pi text-sm text-gray-500 transition-transform duration-200"
                :class="showTopUp ? 'pi-chevron-up' : 'pi-chevron-down'"
              ></i>
            </button>
            <!-- Top Up Content -->
            <div v-show="showTopUp" class="flex flex-col gap-1.5 px-1">
              <!-- Quick-select amount buttons -->
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="amt in topUpPresets"
                  :key="amt"
                  class="py-2.5 px-3 rounded-xl text-sm font-semibold border transition-all duration-150"
                  :class="topUpAmount === amt
                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                    : 'bg-white text-gray-700 border-gray-200 hover:border-emerald-300 hover:text-emerald-700'"
                  @click="topUpAmount = amt; showCustomTopUp = false"
                >
                  {{ formatCollected(amt) }}
                </button>
                <button
                  class="py-2.5 px-3 rounded-xl text-sm font-semibold border transition-all duration-150"
                  :class="showCustomTopUp
                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
                    : 'bg-white text-gray-500 border-dashed border-gray-300 hover:border-emerald-300 hover:text-emerald-600'"
                  @click="topUpAmount = null; showCustomTopUp = !showCustomTopUp"
                >
                  <i class="pi pi-pencil mr-1 text-xs"></i>Lainnya
                </button>
              </div>
              <!-- Custom amount input -->
              <div v-if="showCustomTopUp" class="flex flex-col sm:flex-row gap-2 mt-1">
                <InputNumber
                  v-model="topUpAmount"
                  :min="10000"
                  :step="50000"
                  prefix="Rp "
                  placeholder="Nominal custom"
                  class="flex-1"
                  :class="{ 'p-invalid': topUpError }"
                  fluid
                />
              </div>
              <!-- Submit button -->
              <Button
                label="Top Up via Xendit"
                icon="pi pi-arrow-right"
                class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-medium !rounded-xl !py-3 shadow-sm w-full"
                :loading="topUpLoading"
                :disabled="!topUpAmount || topUpAmount < 10000"
                @click="handleTopUp"
              />
              <small v-if="topUpError" class="text-red-500 text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle"></i>{{ topUpError }}
              </small>
              <small v-else class="text-gray-400 text-xs">Pilih nominal di atas, lalu bayar via Xendit</small>
            </div>
          </div>

          <!-- Withdraw Dropdown Header -->
          <button
            class="w-full flex items-center justify-between py-3 px-4 rounded-xl bg-orange-50 hover:bg-orange-100 border border-orange-200 transition-all duration-150 cursor-pointer mt-4"
            @click="showWithdraw = !showWithdraw"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-orange-200 rounded-lg flex items-center justify-center">
                <i class="pi pi-arrow-up-right text-sm text-orange-700"></i>
              </div>
              <span class="text-sm font-semibold text-gray-800">Withdraw / Tarik Dana</span>
            </div>
            <i
              class="pi text-sm text-gray-500 transition-transform duration-200"
              :class="showWithdraw ? 'pi-chevron-up' : 'pi-chevron-down'"
            ></i>
          </button>
          <!-- Withdraw Content -->
          <div v-show="showWithdraw" class="flex flex-col gap-1.5 px-1">
            <!-- Quick-select amount buttons -->
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="amt in withdrawPresets"
                :key="amt"
                class="py-2.5 px-3 rounded-xl text-sm font-semibold border transition-all duration-150"
                :class="withdrawAmount === amt
                  ? 'bg-orange-500 text-white border-orange-500 shadow-sm'
                  : 'bg-white text-gray-700 border-gray-200 hover:border-orange-300 hover:text-orange-600'"
                @click="withdrawAmount = amt; showCustomWithdraw = false"
                :disabled="amt > currentBalance"
              >
                {{ formatCollected(amt) }}
              </button>
              <button
                class="py-2.5 px-3 rounded-xl text-sm font-semibold border-dashed border transition-all duration-150"
                :class="showCustomWithdraw
                  ? 'bg-orange-500 text-white border-orange-500 shadow-sm'
                  : 'bg-white text-gray-500 border-gray-300 hover:border-orange-300 hover:text-orange-600'"
                @click="withdrawAmount = null; showCustomWithdraw = !showCustomWithdraw"
              >
                <i class="pi pi-pencil mr-1 text-xs"></i>Lainnya
              </button>
            </div>
            <!-- Custom amount input -->
            <div v-if="showCustomWithdraw" class="flex flex-col sm:flex-row gap-2">
              <InputNumber
                v-model="withdrawAmount"
                :min="10000"
                :step="50000"
                :max="currentBalance"
                prefix="Rp "
                placeholder="Nominal custom"
                class="flex-1"
                :class="{ 'p-invalid': withdrawError }"
                fluid
              />
            </div>
            <!-- Submit button -->
            <Button
              label="Tarik Dana via Xendit"
              icon="pi pi-arrow-up-right"
              class="!bg-orange-500 !border-none hover:!bg-orange-600 !text-white !font-medium !rounded-xl !py-3 shadow-sm w-full"
              :loading="withdrawLoading"
              :disabled="!withdrawAmount || withdrawAmount < 10000 || withdrawAmount > currentBalance"
              @click="handleWithdraw"
            />
            <small v-if="withdrawError" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ withdrawError }}
            </small>
            <small v-else class="text-gray-400 text-xs">Konfirmasi penarikan via halaman Xendit (staging)</small>
          </div>

          <Divider class="my-5" />

          <Button
            label="Riwayat Transaksi"
            icon="pi pi-history"
            class="p-button-text w-full !text-emerald-600 !justify-start !rounded-xl"
            @click="showHistory = !showHistory; if (showHistory && transactions.value.length === 0) loadTransactions()"
          />

          <div v-if="showHistory" class="mt-4 space-y-3 max-h-80 overflow-y-auto">
            <div v-if="transactions.length === 0" class="text-center py-6 text-gray-400 text-sm">
              <i class="pi pi-inbox text-2xl mb-2 block"></i>
              Belum ada transaksi
            </div>
            <div
              v-for="tx in transactions"
              :key="tx.id"
              class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0"
            >
              <div class="flex items-center gap-3 min-w-0 flex-1">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                  :class="tx.type === 'top_up' ? 'bg-emerald-100' : tx.type === 'disbursement' ? 'bg-purple-100' : 'bg-blue-100'"
                >
                  <i
                    class="pi text-xs"
                    :class="tx.type === 'top_up' ? 'pi-arrow-up text-emerald-600' : tx.type === 'disbursement' ? 'pi-wallet text-purple-600' : 'pi-arrow-right text-blue-600'"
                  ></i>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-gray-700">{{ formatTypeLabel(tx.type) }}</p>
                  <p v-if="txCampaignName(tx)" class="text-xs text-emerald-600 font-medium truncate max-w-[180px]">
                    Donasi ke: {{ txCampaignName(tx) }}
                  </p>
                  <p v-else-if="tx.description" class="text-xs text-gray-500 truncate max-w-[180px]">{{ tx.description.replace(/\s*[-–—]\s*Rp\s*[\d.,]+/, '') }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(tx.created_at) }}</p>
                </div>
              </div>
              <div class="text-right flex-shrink-0 ml-2">
                <span
                  class="text-sm font-semibold"
                  :class="tx.type === 'top_up' || tx.type === 'refund' ? 'text-emerald-600' : 'text-gray-800'"
                >
                  {{ tx.type === 'top_up' || tx.type === 'refund' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                </span>
                <p class="text-[10px] text-gray-400 capitalize">{{ tx.status }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Creator Status Card -->
        <div class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-4 md:p-6">
          <div class="flex items-center gap-3 mb-6">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center"
              :class="isCreator ? 'bg-emerald-100' : hasPendingRequest ? 'bg-yellow-100' : 'bg-purple-100'"
            >
              <i
                class="text-lg"
                :class="isCreator ? 'pi pi-check-circle text-emerald-700' : hasPendingRequest ? 'pi pi-clock text-yellow-700' : 'pi pi-shield text-purple-700'"
              ></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Status Kreator</h3>
              <p class="text-xs text-gray-400">Ajukan diri Anda menjadi kreator kampanye</p>
            </div>
          </div>

          <!-- State 1: Already a Creator -->
          <div v-if="isCreator" class="space-y-5">
            <div class="p-4 md:p-5 bg-emerald-50 border border-emerald-200 rounded-xl">
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-200 flex items-center justify-center flex-shrink-0">
                  <i class="pi pi-check-circle text-emerald-700 text-lg"></i>
                </div>
                <div>
                  <p class="text-base font-bold text-emerald-900">Anda adalah Kreator</p>
                  <p class="text-sm text-emerald-700 mt-1 leading-relaxed">
                    Anda dapat membuat kampanye penggalangan dana sendiri
                  </p>
                </div>
              </div>
            </div>
            <router-link :to="{ name: 'Dashboard' }" class="block">
              <Button
                label="Buat Kampanye Baru"
                icon="pi pi-plus-circle"
                class="w-full !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3.5 !rounded-xl shadow-sm"
              />
            </router-link>
          </div>

          <!-- State 2: Pending Verification -->
          <div v-else-if="hasPendingRequest" class="space-y-5">
            <div class="p-4 md:p-5 bg-amber-50 border border-amber-200 rounded-xl">
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-yellow-200 flex items-center justify-center flex-shrink-0">
                  <i class="pi pi-clock text-yellow-700 text-lg"></i>
                </div>
                <div>
                  <p class="text-base font-bold text-yellow-900">Menunggu Verifikasi Admin</p>
                  <p class="text-sm text-yellow-700 mt-1 leading-relaxed">
                    Permintaan upgrade role Anda sedang diproses oleh admin.
                  </p>
                  <Badge
                    value="Menunggu..."
                    severity="warn"
                    class="mt-3 !bg-yellow-200 !text-yellow-800 !rounded-full !text-xs !font-semibold !px-3 !py-1"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- State 3: Backer - Show Application Form -->
          <div v-else class="space-y-5">
            <div class="p-4 md:p-5 bg-purple-50 border border-purple-100 rounded-xl">
              <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-purple-200 flex items-center justify-center flex-shrink-0">
                  <i class="pi pi-info-circle text-purple-700 text-lg"></i>
                </div>
                <div>
                  <p class="text-base font-bold text-purple-900">Jadi Kreator</p>
                  <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                    Dapatkan akses membuat kampanye penggalangan dana sendiri
                  </p>
                  <ul class="mt-3 space-y-1.5">
                    <li class="flex items-center gap-2 text-xs text-gray-500">
                      <i class="pi pi-check-circle text-emerald-500 text-[10px]"></i>Buat kampanye sendiri
                    </li>
                    <li class="flex items-center gap-2 text-xs text-gray-500">
                      <i class="pi pi-check-circle text-emerald-500 text-[10px]"></i>Kelola donasi masuk
                    </li>
                    <li class="flex items-center gap-2 text-xs text-gray-500">
                      <i class="pi pi-check-circle text-emerald-500 text-[10px]"></i>Cairkan dana kampanye
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label for="reason" class="text-sm font-semibold text-gray-700">
                Alasan permohonan <span class="text-red-400 font-normal">(Wajib)</span>
              </label>
              <Textarea
                id="reason"
                v-model="upgradeReason"
                rows="4"
                placeholder="Ceritakan mengapa Anda ingin menjadi kreator..."
                class="w-full !rounded-xl !text-sm"
                :maxlength="500"
              />
              <small class="text-gray-400 text-xs text-right">{{ upgradeReason.length }}/500</small>
            </div>

            <Button
              label="Ajukan Jadi Kreator"
              icon="pi pi-shield"
              class="w-full !bg-purple-600 !border-none hover:!bg-purple-700 !text-white !font-semibold !py-3.5 !rounded-xl shadow-sm"
              :loading="upgradeLoading"
              :disabled="upgradeLoading"
              @click="handleUpgradeRequest"
            />
          </div>

          <Divider class="my-6" />

          <!-- Quick Stats -->
          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider flex items-center gap-2">
              <i class="pi pi-chart-bar text-xs"></i>Statistik
            </h4>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white rounded-[15px] border border-emerald-200 p-4 text-center hover:bg-emerald-50 transition-colors">
                <p class="text-2xl font-bold text-gray-800">{{ totalBackings }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Dukungan</p>
              </div>
              <div class="bg-white rounded-[15px] border border-emerald-200 px-0 py-2 text-center hover:bg-emerald-50 transition-colors">
                <p class="text-2xl font-bold text-emerald-700 hidden sm:block">{{ formatCurrency(totalDonated) }}</p>
                <p class="text-2xl font-bold text-emerald-700 block sm:hidden">{{ formatCollected(totalDonated) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Donasi</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Divider from 'primevue/divider'
import { useToast } from 'primevue/usetoast'
import walletService from '@/services/walletService'
import roleService from '@/services/roleService'
import api from '@/services/api'
import { formatCollected } from '@/utils/formatter'

const authStore = useAuthStore()
const toast = useToast()

const currentBalance = computed(() => authStore.user?.balance || 0)
const isCreator = computed(() => authStore.user?.role === 'creator')
const isAdmin = computed(() => authStore.user?.role === 'admin')
const totalBackings = computed(() => authStore.user?.total_backings || 0)
const totalDonated = computed(() => authStore.user?.total_donated || 0)

const roleLabel = computed(() => {
  const labels = { admin: 'Admin', creator: 'Creator', backer: 'Backer', guest: 'Tamu' }
  return labels[authStore.user?.role] || 'Backer'
})

const roleSeverity = computed(() => {
  const severities = { admin: 'danger', creator: 'success', backer: 'info', guest: 'warning' }
  return severities[authStore.user?.role] || 'info'
})

// Wallet — amount presets (in Rupiah)
const topUpPresets = [50000, 100000, 200000, 500000, 1000000, 2000000]
const withdrawPresets = [50000, 100000, 200000, 500000, 1000000, 2000000]

const showTopUp = ref(false)
const topUpAmount = ref(null)
const topUpLoading = ref(false)
const topUpError = ref('')
const showCustomTopUp = ref(false)

const showWithdraw = ref(false)
const withdrawAmount = ref(null)
const withdrawLoading = ref(false)
const withdrawError = ref('')
const showCustomWithdraw = ref(false)
const showHistory = ref(false)
const transactions = ref([])

async function handleTopUp() {
  topUpError.value = ''

  if (!topUpAmount.value || topUpAmount.value < 10000) {
    topUpError.value = 'Minimal top up Rp 10.000'
    return
  }

  topUpLoading.value = true
  // Open blank window BEFORE the async call to avoid pop-up blockers
  let xenditWindow = window.open('', '_blank')

  try {
    // Pass current page as redirect URLs so Xendit sends user back here
    const baseUrl = window.location.origin
    const res = await walletService.postTopUp(topUpAmount.value, {
      success: baseUrl + '/profile',
      failure: baseUrl + '/profile',
    })

    const invoiceUrl = res.data?.data?.invoice_url

    if (invoiceUrl && xenditWindow) {
      // Redirect the pre-opened window to Xendit checkout
      xenditWindow.location = invoiceUrl
      topUpAmount.value = null
      await refreshBalanceOnly()
      toast.add({
        severity: 'success',
        summary: 'Pembayaran Dibuka',
        detail: 'Halaman pembayaran Xendit telah dibuka di tab baru.',
        life: 5000,
      })
    } else if (res.data?.data?.balance !== undefined) {
      // Fallback: direct top-up (legacy/admin mode)
      authStore.user.balance = res.data.data.balance
      topUpAmount.value = null
      toast.add({ severity: 'success', summary: 'Top Up Berhasil', detail: 'Saldo berhasil ditambahkan.', life: 3000 })
    } else {
      topUpError.value = 'Gagal mendapatkan URL pembayaran'
      if (xenditWindow) xenditWindow.close()
    }
  } catch (error) {
    topUpError.value = error.response?.data?.message || 'Gagal membuat invoice pembayaran'
    toast.add({ severity: 'error', summary: 'Top Up Gagal', detail: topUpError.value, life: 5000 })
    if (xenditWindow) xenditWindow.close()
  } finally {
    topUpLoading.value = false
  }
}

// Simple balance refresh (used after top-up/withdraw)
async function refreshBalanceOnly() {
  try {
    const res = await walletService.getBalance()
    if (res.data?.data) {
      authStore.user.balance = res.data.data.balance
      transactions.value = res.data.data.transactions?.data || []
    }
  } catch (e) {
    // Ignore
  }
}

async function handleWithdraw() {
  withdrawError.value = ''

  if (!withdrawAmount.value || withdrawAmount.value < 10000) {
    withdrawError.value = 'Minimal withdraw Rp 10.000'
    return
  }
  if (withdrawAmount.value > currentBalance.value) {
    withdrawError.value = 'Saldo tidak mencukupi'
    return
  }

  withdrawLoading.value = true
  // Open blank window BEFORE the async call to avoid pop-up blockers
  let xenditWindow = window.open('', '_blank')

  try {
    const baseUrl = window.location.origin
    const res = await walletService.postWithdraw(withdrawAmount.value, {
      success: baseUrl + '/profile',
      failure: baseUrl + '/profile',
    })

    const invoiceUrl = res.data?.data?.invoice_url

    if (invoiceUrl && xenditWindow) {
      // Redirect the pre-opened window to Xendit checkout
      xenditWindow.location = invoiceUrl
      withdrawAmount.value = null
      showCustomWithdraw.value = false
      await refreshBalanceOnly()
      toast.add({
        severity: 'success',
        summary: 'Penarikan Dibuka',
        detail: 'Halaman konfirmasi penarihan Xendit telah dibuka di tab baru.',
        life: 5000,
      })
    } else {
      withdrawError.value = 'Gagal mendapatkan URL konfirmasi'
      if (xenditWindow) xenditWindow.close()
    }
  } catch (error) {
    withdrawError.value = error.response?.data?.message || 'Gagal memproses penarikan dana'
    toast.add({ severity: 'error', summary: 'Withdraw Gagal', detail: withdrawError.value, life: 5000 })
    if (xenditWindow) xenditWindow.close()
  } finally {
    withdrawLoading.value = false
  }
}

// Role upgrade state management
const upgradeReason = ref('')
const upgradeLoading = ref(false)
const hasPendingRequest = ref(false)

async function checkPendingRequest() {
  if (isCreator.value || isAdmin.value) return
  try {
    const res = await api.get('/my/role-requests')
    const requests = res.data?.data || []
    hasPendingRequest.value = requests.some(r => r.status === 'pending')
  } catch (e) {
    hasPendingRequest.value = false
  }
}

async function handleUpgradeRequest() {
  upgradeLoading.value = true
  try {
    await roleService.requestCreatorRole(upgradeReason.value)
    hasPendingRequest.value = true
    toast.add({ severity: 'success', summary: 'Permintaan Dikirim', detail: 'Permintaan upgrade role sedang diproses.', life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    upgradeLoading.value = false
  }
}

/**
 * Upload/set avatar image via the backend.
 * The backend returns the new avatar_url which we store back into the store.
 */
async function handleAvatarUpload(event) {
  const file = event.target.files?.[0]
  if (!file) return

  // Quick client-side validation
  const maxSize = 2 * 1024 * 1024 // 2 MB
  if (file.size > maxSize) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Ukuran maksimal 2 MB.', life: 3000 })
    return
  }
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Format gambar tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.', life: 3000 })
    return
  }

  const formData = new FormData()
  formData.append('avatar', file)

  try {
    const res = await api.post('/profile/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const avatarUrl = res.data?.data?.avatar_url
    if (avatarUrl && authStore.user) {
      authStore.user.avatar_url = avatarUrl
    }
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Foto profil berhasil diperbarui.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal mengunggah avatar.', life: 4000 })
  }
}

function txCampaignName(tx) {
  if (tx.type !== 'payment' && tx.type !== 'backing') return null
  const match = tx.description?.match(/"([^"]+)"/)
  return match ? match[1] : null
}

function formatTypeLabel(type) {
  const map = {
    top_up: 'Top Up',
    payment: 'Donasi',
    backing: 'Backing',
    disbursement: 'Pencairan',
    refund: 'Refund',
    platform_fee: 'Biaya Platform',
  }
  return map[type] || 'Umum'
}

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}

/**
 * Detect redirect from Xendit checkout after payment/withdraw.
 * Xendit redirects back with query params like ?status=PAID&external_id=COFUND-...
 */
async function checkXenditRedirect() {
  const params = new URLSearchParams(window.location.search)
  const status = params.get('status')
  const externalId = params.get('external_id')

  if (status && externalId) {
    // Clean up URL immediately (remove query params)
    window.history.replaceState({}, '', window.location.pathname)

    const isPaid = ['PAID', 'SETTLED'].includes(status.toUpperCase())
    const isTopUp = externalId.startsWith('COFUND-TOPUP-')
    const isWithdraw = externalId.startsWith('COFUND-WITHDRAW-')

    if (isPaid) {
      try {
        // Verify payment with Xendit API and update balance server-side
        const res = await walletService.verifyPayment(externalId)
        if (res.data?.success && res.data?.data?.balance !== undefined) {
          authStore.user.balance = res.data.data.balance
        }
        await refreshBalanceOnly()
      } catch (e) {
        // Fallback to just refreshing balance if verify fails
        await refreshBalanceOnly()
      }
      toast.add({
        severity: 'success',
        summary: isTopUp ? 'Top Up Berhasil' : 'Withdraw Berhasil',
        detail: isTopUp
          ? 'Saldo berhasil ditambahkan.'
          : 'Penarikan dana berhasil. Saldo Anda telah diperbarui.',
        life: 6000,
      })
    } else {
      toast.add({
        severity: 'error',
        summary: 'Pembayaran Gagal',
        detail: 'Status: ' + status + '. Silakan coba lagi.',
        life: 8000,
      })
    }
  }
}async function loadTransactions() {
  if (transactions.value.length > 0) return
  await refreshBalanceOnly()
}

onMounted(async () => {
  try {
    // Check if user was redirected back from Xendit checkout
    checkXenditRedirect()

    // Always load balance & transactions on mount
    if (!transactions.value.length) {
      await refreshBalanceOnly()
    }

    await checkPendingRequest()
  } catch (e) {
    // Ignore
  }
})
</script>

<style scoped>
@keyframes pulse-once {
  0% { opacity: 0.6; transform: scale(0.98); }
  50% { opacity: 1; transform: scale(1.01); }
  100% { opacity: 1; transform: scale(1); }
}
.animate-pulse-once {
  animation: pulse-once 0.4s ease-out;
}
</style>