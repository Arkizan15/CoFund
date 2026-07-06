<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Profil Saya</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola informasi akun dan saldo dompet Anda</p>
      </div>

      <!-- Profile Banner -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
          <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 ring-4 ring-emerald-50">
            <i class="pi pi-user text-3xl text-emerald-700"></i>
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

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Wallet Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-wallet text-lg text-emerald-700"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Dompet Digital</h3>
              <p class="text-xs text-gray-400">Kelola saldo donasi Anda</p>
            </div>
          </div>

          <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white mb-6 shadow-sm">
            <p class="text-emerald-100 text-sm font-medium flex items-center gap-2">
              <i class="pi pi-credit-card"></i>Saldo Saat Ini
            </p>
            <p class="text-3xl font-bold mt-1 tracking-tight">{{ formatCurrency(currentBalance) }}</p>
          </div>

          <div class="space-y-4">
            <div class="flex flex-col gap-1.5">
              <label for="topup-amount" class="text-sm font-semibold text-gray-700">Top Up Saldo</label>
              <div class="flex gap-2">
                <InputNumber
                  id="topup-amount"
                  v-model="topUpAmount"
                  :min="10000"
                  :step="50000"
                  :max="999999999"
                  prefix="Rp "
                  placeholder="Masukkan nominal"
                  class="flex-1"
                  :class="{ 'p-invalid': topUpError }"
                  fluid
                />
                <Button
                  label="Top Up"
                  icon="pi pi-plus"
                  class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-medium !rounded-xl !px-6 shadow-sm"
                  :loading="topUpLoading"
                  :disabled="!topUpAmount || topUpAmount < 10000"
                  @click="handleTopUp"
                />
              </div>
              <small v-if="topUpError" class="text-red-500 text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle"></i>{{ topUpError }}
              </small>
              <small v-else class="text-gray-400 text-xs">Minimal top up Rp 10.000</small>
            </div>

            <div v-if="topUpSuccess" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2 animate-pulse-once">
              <i class="pi pi-check-circle"></i>
              <span>Top up berhasil!</span>
            </div>
          </div>

          <Divider class="my-5" />

          <Button
            label="Riwayat Transaksi"
            icon="pi pi-history"
            class="p-button-text w-full !text-emerald-600 !justify-start !rounded-xl"
            @click="showHistory = !showHistory"
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
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center"
                  :class="tx.type === 'top_up' ? 'bg-emerald-100' : tx.type === 'disbursement' ? 'bg-purple-100' : 'bg-blue-100'"
                >
                  <i
                    class="pi text-xs"
                    :class="tx.type === 'top_up' ? 'pi-arrow-up text-emerald-600' : tx.type === 'disbursement' ? 'pi-wallet text-purple-600' : 'pi-arrow-right text-blue-600'"
                  ></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700 capitalize">{{ formatTypeLabel(tx.type) }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(tx.created_at) }}</p>
                </div>
              </div>
              <div class="text-right">
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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
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
            <div class="p-5 bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-200 rounded-xl">
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
            <div class="p-5 bg-gradient-to-br from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl">
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
            <div class="p-5 bg-gradient-to-br from-purple-50 to-purple-100/30 border border-purple-100 rounded-xl">
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
                Alasan permohonan <span class="text-gray-400 font-normal">(Opsional)</span>
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
              <div class="bg-slate-50 rounded-xl p-4 text-center hover:bg-slate-100 transition-colors">
                <p class="text-2xl font-bold text-gray-800">{{ totalBackings }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Dukungan</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-4 text-center hover:bg-slate-100 transition-colors">
                <p class="text-2xl font-bold text-emerald-700">{{ formatCurrency(totalDonated) }}</p>
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

const authStore = useAuthStore()
const toast = useToast()

const currentBalance = computed(() => authStore.user?.balance || 0)
const isCreator = computed(() => authStore.user?.role === 'creator')
const isAdmin = computed(() => authStore.user?.role === 'admin')
const totalBackings = computed(() => authStore.user?.total_backings || 0)
const totalDonated = computed(() => authStore.user?.total_backings || 0)

const roleLabel = computed(() => {
  const labels = { admin: 'Admin', creator: 'Creator', backer: 'Backer', guest: 'Tamu' }
  return labels[authStore.user?.role] || 'Backer'
})

const roleSeverity = computed(() => {
  const severities = { admin: 'danger', creator: 'success', backer: 'info', guest: 'warning' }
  return severities[authStore.user?.role] || 'info'
})

// Wallet
const topUpAmount = ref(null)
const topUpLoading = ref(false)
const topUpError = ref('')
const topUpSuccess = ref(false)
const showHistory = ref(false)
const transactions = ref([])

async function handleTopUp() {
  topUpError.value = ''
  topUpSuccess.value = false

  if (!topUpAmount.value || topUpAmount.value < 10000) {
    topUpError.value = 'Minimal top up Rp 10.000'
    return
  }

  topUpLoading.value = true
  try {
    const res = await walletService.postTopUp(topUpAmount.value)
    authStore.user.balance = res.data.data.balance
    topUpSuccess.value = true
    topUpAmount.value = null
    toast.add({ severity: 'success', summary: 'Top Up Berhasil', detail: 'Saldo berhasil ditambahkan.', life: 3000 })
    const txRes = await walletService.getBalance()
    transactions.value = txRes.data?.data?.transactions?.data || []
  } catch (error) {
    topUpError.value = error.response?.data?.message || 'Gagal melakukan top up'
    toast.add({ severity: 'error', summary: 'Top Up Gagal', detail: topUpError.value, life: 3000 })
  } finally {
    topUpLoading.value = false
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

function formatTypeLabel(type) {
  const map = {
    top_up: 'Top Up',
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

onMounted(async () => {
  try {
    const [txRes] = await Promise.all([
      walletService.getBalance().catch(() => ({ data: { data: { transactions: { data: [] } } } })),
      checkPendingRequest(),
    ])
    transactions.value = txRes.data?.data?.transactions?.data || []
  } catch (e) {
    transactions.value = []
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