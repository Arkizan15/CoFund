<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Jelajahi Kampanye</h1>
          <p class="text-gray-500 text-sm mt-1">Temukan dan dukung kampanye crowdfunding terbaik</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-400">
          <i class="pi pi-megaphone text-xs"></i>
          <span>{{ totalCampaigns || campaigns.length }} kampanye ditemukan</span>
        </div>
      </div>

      <!-- Filter Panel -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 mb-8">
        <div class="flex flex-col gap-4">
          <!-- Row 1: Search -->
          <div class="relative">
            <i
              class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm transition-colors"
              :class="{ '!text-emerald-500': search }"
            ></i>
            <InputText
              v-model="searchInput"
              placeholder="Cari kampanye berdasarkan judul atau deskripsi..."
              class="w-full pl-10 pr-12 !border-gray-200 !rounded-xl !py-3 !text-sm focus:!border-emerald-400 focus:!shadow-sm focus:!shadow-emerald-100 transition-all"
              @input="onSearchInput"
            />
            <!-- Clear search button -->
            <button
              v-if="searchInput"
              class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
              @click="clearSearch"
            >
              <i class="pi pi-times text-[10px] text-gray-500"></i>
            </button>
          </div>

          <!-- Row 2: Filter dropdowns -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Category Filter -->
            <div class="relative">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                <i class="pi pi-tag mr-1 text-[10px]"></i>Kategori
              </label>
              <Dropdown
                v-model="selectedCategory"
                :options="categoryOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Semua Kategori"
                class="w-full !bg-white"
                :showClear="true"
                @change="onFilterChange"
              />
            </div>

            <!-- Status Filter -->
            <div class="relative">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                <i class="pi pi-flag mr-1 text-[10px]"></i>Status
              </label>
              <Dropdown
                v-model="selectedStatus"
                :options="statusOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Semua Status"
                class="w-full !bg-white"
                :showClear="true"
                @change="onFilterChange"
              />
            </div>

            <!-- Sort -->
            <div class="relative">
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                <i class="pi pi-sort-amount-down mr-1 text-[10px]"></i>Urutkan
              </label>
              <Dropdown
                v-model="selectedSort"
                :options="sortOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Urutkan"
                class="w-full !bg-white"
                @change="onFilterChange"
              />
            </div>
          </div>

          <!-- Active Filters Chips -->
          <div v-if="activeFilterCount > 0" class="flex items-center gap-2 flex-wrap pt-1">
            <span class="text-xs text-gray-400 font-medium">Filter aktif:</span>
            <span
              v-if="search"
              class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-full text-xs font-medium text-emerald-700"
            >
              <i class="pi pi-search text-[10px]"></i>
              Pencarian: "{{ truncateText(search, 20) }}"
              <button class="ml-0.5 hover:text-emerald-900" @click="clearSearch">
                <i class="pi pi-times text-[8px]"></i>
              </button>
            </span>
            <span
              v-if="selectedCategoryLabel"
              class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 border border-blue-200 rounded-full text-xs font-medium text-blue-700"
            >
              <i class="pi pi-tag text-[10px]"></i>
              {{ selectedCategoryLabel }}
              <button class="ml-0.5 hover:text-blue-900" @click="selectedCategory = null; onFilterChange()">
                <i class="pi pi-times text-[8px]"></i>
              </button>
            </span>
            <span
              v-if="selectedStatusLabel"
              class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 border border-purple-200 rounded-full text-xs font-medium text-purple-700"
            >
              <i class="pi pi-flag text-[10px]"></i>
              {{ selectedStatusLabel }}
              <button class="ml-0.5 hover:text-purple-900" @click="selectedStatus = null; onFilterChange()">
                <i class="pi pi-times text-[8px]"></i>
              </button>
            </span>
            <button
              class="text-xs text-red-500 hover:text-red-700 font-medium ml-1 transition-colors"
              @click="clearAllFilters"
            >
              Hapus semua
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State - Skeleton -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <CampaignCardSkeleton v-for="n in 6" :key="'skeleton-' + n" />
      </div>

      <!-- Empty State -->
      <div v-else-if="campaigns.length === 0" class="text-center py-20">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-inbox text-3xl text-gray-300"></i>
        </div>
        <p class="text-gray-500 font-medium">Tidak ada kampanye ditemukan</p>
        <p class="text-gray-400 text-sm mt-1">Coba ubah filter atau kata kunci pencarian Anda</p>
        <Button
          v-if="activeFilterCount > 0"
          label="Reset Filter"
          icon="pi pi-refresh"
          class="mt-4 !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !rounded-xl"
          @click="clearAllFilters"
        />
      </div>

      <!-- Campaign Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <CampaignCard
          v-for="campaign in campaigns"
          :key="campaign.id"
          :campaign="campaign"
          @backing="openBackingDialog"
        />
      </div>

      <!-- Pagination -->
      <div v-if="!loading && totalPages > 1" class="flex items-center justify-center gap-2 mt-10">
        <button
          class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200"
          :class="currentPage <= 1
            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'bg-white border border-gray-200 text-gray-700 hover:border-emerald-300 hover:text-emerald-700'"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
        >
          <i class="pi pi-chevron-left text-xs"></i>
          Sebelumnya
        </button>

        <div class="flex items-center gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            class="w-10 h-10 rounded-xl text-sm font-semibold transition-all duration-200"
            :class="page === currentPage
              ? 'bg-emerald-600 text-white shadow-sm'
              : 'bg-white border border-gray-200 text-gray-600 hover:border-emerald-300 hover:text-emerald-700'"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>

        <button
          class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200"
          :class="currentPage >= totalPages
            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
            : 'bg-white border border-gray-200 text-gray-700 hover:border-emerald-300 hover:text-emerald-700'"
          :disabled="currentPage >= totalPages"
          @click="goToPage(currentPage + 1)"
        >
          Selanjutnya
          <i class="pi pi-chevron-right text-xs"></i>
        </button>
      </div>
    </div>

    <!-- Backing Dialog Modal -->
    <Dialog
      v-model:visible="backingDialogVisible"
      :header="'Dukung Kampanye Ini'"
      :modal="true"
      :closable="true"
      class="app-dialog !rounded-[15px] !bg-white !border-2 !border-emerald-300 !shadow-lg !w-full !max-w-md"
      @show="onDialogShow"
      @hide="resetBackingForm"
    >
      <div v-if="backingCampaign" class="space-y-5 py-2">
        <!-- Campaign Title -->
        <div class="bg-emerald-50 rounded-xl p-3 text-center">
          <p class="text-xs text-gray-500 mb-1">Mendukung</p>
          <p class="text-sm font-bold text-gray-800">{{ backingCampaign.title }}</p>
        </div>

        <!-- Auth Check -->
        <div v-if="!authStore.isAuthenticated" class="text-center py-4">
          <i class="pi pi-lock text-3xl text-gray-300 mb-3 block"></i>
          <p class="text-gray-600 text-sm mb-4">Silakan masuk terlebih dahulu untuk mendukung kampanye.</p>
          <router-link :to="{ name: 'Login', query: { redirect: route.fullPath } }" @click="backingDialogVisible = false">
            <Button label="Masuk Sekarang" icon="pi pi-sign-in" class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-medium !rounded-xl" />
          </router-link>
        </div>

        <!-- Owner Check -->
        <div v-else-if="isOwner" class="text-center py-4">
          <i class="pi pi-info-circle text-3xl text-yellow-400 mb-3 block"></i>
          <p class="text-gray-600 text-sm">Anda tidak dapat mendanai kampanye milik sendiri.</p>
        </div>

        <!-- Campaign Not Active -->
        <div v-else-if="backingCampaign.status !== 'active'" class="text-center py-4">
          <i class="pi pi-lock text-3xl text-gray-300 mb-3 block"></i>
          <p class="text-gray-600 text-sm">Kampanye ini belum aktif untuk menerima dukungan dana.</p>
        </div>

        <!-- Backing Form -->
        <template v-else>
          <div class="p-3 bg-white rounded-xl border border-emerald-200 flex items-center justify-between">
            <span class="text-xs text-gray-500 font-medium">Saldo Anda</span>
            <span class="text-sm font-bold text-gray-800">{{ formatCurrency(userBalance) }}</span>
          </div>

          <!-- Preset Amounts -->
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Pilih Nominal</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="preset in presetAmounts"
                :key="preset"
                class="py-2.5 px-3 rounded-xl text-sm font-semibold border-2 transition-all duration-200"
                :class="backingAmount === preset
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                  : 'border-gray-200 bg-white text-gray-600 hover:border-emerald-200 hover:bg-emerald-50/50'"
                @click="selectPreset(preset)"
              >
                {{ formatCurrency(preset) }}
              </button>
            </div>
          </div>

          <!-- Custom Amount -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Atur Nominal Sendiri</label>
            <InputNumber
              v-model="backingAmount"
              :min="10000"
              :step="10000"
              prefix="Rp "
              placeholder="Masukkan nominal"
              class="w-full"
              :class="{ 'p-invalid': backingError }"
              fluid
            />
            <small v-if="backingError" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ backingError }}
            </small>
            <small v-else class="text-gray-400 text-xs">Minimal Rp 10.000</small>
          </div>

          <!-- Xendit Checkout Opened Message -->
          <div v-if="backingInvoiceOpened" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-emerald-200 flex items-center justify-center flex-shrink-0">
                <i class="pi pi-external-link text-emerald-700 text-sm"></i>
              </div>
              <div>
                <p class="text-sm font-semibold text-emerald-800">Halaman Pembayaran Dibuka</p>
                <p class="text-xs text-emerald-600 mt-0.5">
                  Pembayaran sebesar <strong>{{ formatCurrency(lastBackingAmount) }}</strong> telah dibuka di tab baru.
                  Setelah pembayaran selesai, data akan diperbarui.
                </p>
              </div>
            </div>
          </div>

          <Button
            v-if="!backingInvoiceOpened"
            label="Bayar via Xendit"
            icon="pi pi-credit-card"
            class="w-full !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !rounded-xl shadow-sm"
            :loading="backingLoading"
            :disabled="backingLoading || !backingAmount"
            @click="handleBackingDirect"
          />
        </template>
      </div>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useGenieEffect } from '@/composables/useGenieEffect'
import { useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import CampaignCard from '@/components/CampaignCard.vue'
import CampaignCardSkeleton from '@/components/CampaignCardSkeleton.vue'
import * as campaignService from '@/services/campaignService'

const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()
const { onDialogShow } = useGenieEffect()

// ── Filter State ──────────────────────────────────────────────
const search = ref('')
const searchInput = ref('')
const selectedCategory = ref(null)
const selectedStatus = ref(null)
const selectedSort = ref('newest')
const loading = ref(false)

// Real categories from API
const rawCategories = ref([])
const categoryOptions = computed(() => {
  return rawCategories.value.map(c => ({
    label: c.name,
    value: c.id,
  }))
})

const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Berhasil', value: 'success' },
  { label: 'Gagal', value: 'failed' },
]

const sortOptions = [
  { label: 'Terbaru', value: 'newest' },
  { label: 'Populer', value: 'popular' },
  { label: 'Segera Berakhir', value: 'ending_soon' },
]

// Active filter labels for chips
const selectedCategoryLabel = computed(() => {
  if (!selectedCategory.value) return null
  const found = categoryOptions.value.find(c => c.value === selectedCategory.value)
  return found?.label || null
})
const selectedStatusLabel = computed(() => {
  if (!selectedStatus.value) return null
  const found = statusOptions.find(s => s.value === selectedStatus.value)
  return found?.label || null
})

const activeFilterCount = computed(() => {
  let count = 0
  if (search.value) count++
  if (selectedCategory.value) count++
  if (selectedStatus.value) count++
  return count
})

// ── Campaign Data ─────────────────────────────────────────────
const campaigns = ref([])
const totalCampaigns = ref(0)

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const perPage = ref(12)

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const pages = []
  
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  
  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(total, start + 4)
    } else {
      start = Math.max(1, end - 4)
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchCampaigns()
}

// ── Backing Dialog State ──────────────────────────────────────
const backingDialogVisible = ref(false)
const backingCampaign = ref(null)
const backingAmount = ref(null)
const backingLoading = ref(false)
const backingError = ref('')
const backingInvoiceOpened = ref(false)
const lastBackingAmount = ref(0)

const presetAmounts = [50000, 100000, 250000, 500000]

const isOwner = computed(() => {
  return authStore.user?.id && backingCampaign.value?.user_id === authStore.user.id
})
const userBalance = computed(() => authStore.user?.balance || 0)

// ── Debounced Search ──────────────────────────────────────────
let searchTimeout = null

function onSearchInput() {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    search.value = searchInput.value
    fetchCampaigns()
  }, 400)
}

function clearSearch() {
  search.value = ''
  searchInput.value = ''
  if (searchTimeout) clearTimeout(searchTimeout)
  fetchCampaigns()
}

function onFilterChange() {
  fetchCampaigns()
}

function clearAllFilters() {
  search.value = ''
  searchInput.value = ''
  selectedCategory.value = null
  selectedStatus.value = null
  selectedSort.value = 'newest'
  if (searchTimeout) clearTimeout(searchTimeout)
  fetchCampaigns()
}

function truncateText(text, max) {
  return text?.length > max ? text.substring(0, max) + '...' : text
}

// ── API Calls ─────────────────────────────────────────────────
async function fetchCampaigns() {
  loading.value = true
  try {
    const params = {}
    if (selectedSort.value) params.sort = selectedSort.value
    if (search.value) params.search = search.value
    if (selectedCategory.value) params.category_id = selectedCategory.value
    if (selectedStatus.value) params.status = selectedStatus.value

    params.page = currentPage.value
    params.per_page = perPage.value

    const res = await campaignService.getCampaigns(params)
    // res = axios response → res.data = { success: true, data: paginatorObject }
    // paginatorObject = { data: [...], current_page, last_page, total, ... }
    const responseBody = res?.data || res || {}
    const paginated = responseBody.data

    if (paginated && Array.isArray(paginated.data)) {
      campaigns.value = paginated.data
      totalCampaigns.value = paginated.total || 0
      currentPage.value = paginated.current_page || 1
      totalPages.value = paginated.last_page || 1
    } else if (Array.isArray(paginated)) {
      campaigns.value = paginated
    } else if (Array.isArray(responseBody)) {
      campaigns.value = responseBody
    } else {
      campaigns.value = []
    }
  } catch (e) {
    campaigns.value = []
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const res = await campaignService.getCategories()
    rawCategories.value = res?.data || []
  } catch (e) {
    rawCategories.value = []
  }
}

// ── Backing Functions ─────────────────────────────────────────
function openBackingDialog(campaign) {
  backingCampaign.value = campaign
  backingDialogVisible.value = true
  resetBackingForm()
}

function selectPreset(amount) {
  backingAmount.value = amount
  backingError.value = ''
}

function resetBackingForm() {
  backingAmount.value = null
  backingError.value = ''
  backingInvoiceOpened.value = false
  lastBackingAmount.value = 0
}

function validateBacking() {
  backingError.value = ''
  if (!backingAmount.value || Number(backingAmount.value) < 10000) {
    backingError.value = 'Nominal minimal dukungan adalah Rp 10.000'
    return false
  }

  // Check if backing amount exceeds remaining campaign target
  const collected = Number(backingCampaign.value?.collected_amount || 0)
  const target = Number(backingCampaign.value?.target_amount || 0)
  const remaining = Math.max(0, target - collected)
  if (Number(backingAmount.value) > remaining) {
    backingError.value = 'Nominal donasi melebihi sisa dana yang dibutuhkan kampanye. Sisa dana yang diperlukan: ' + formatCurrency(remaining)
    return false
  }

  return true
}

async function handleBackingDirect() {
  if (!validateBacking()) return

  const backedAmount = Number(backingAmount.value)
  backingLoading.value = true

  // Open blank window BEFORE the async call to avoid pop-up blockers
  let xenditWindow = window.open('', '_blank')

  try {
    const payload = {
      campaign_id: backingCampaign.value.id,
      amount: backedAmount,
    }

    const baseUrl = window.location.origin + route.fullPath
    payload.success_redirect_url = baseUrl
    payload.failure_redirect_url = baseUrl

    const res = await campaignService.createBackingInvoice(payload)

    const invoiceUrl = res.data?.invoice_url

    if (invoiceUrl && xenditWindow) {
      xenditWindow.location = invoiceUrl
      lastBackingAmount.value = backedAmount
      backingInvoiceOpened.value = true
      backingAmount.value = null

      toast.add({
        severity: 'success',
        summary: 'Pembayaran Dibuka',
        detail: 'Halaman pembayaran Xendit telah dibuka di tab baru.',
        life: 5000,
      })
    } else {
      backingError.value = 'Gagal mendapatkan URL pembayaran'
      if (xenditWindow) xenditWindow.close()
    }
  } catch (error) {
    backingError.value = error.response?.data?.message || 'Gagal membuat invoice pembayaran'
    toast.add({ severity: 'error', summary: 'Gagal', detail: backingError.value, life: 4000 })
    if (xenditWindow) xenditWindow.close()
  } finally {
    backingLoading.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────
function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
}

// ── Eager fetch (runs at module level, before mount) ────────
;(async function preloadCampaigns() {
  await Promise.all([
    fetchCategories(),
    fetchCampaigns(),
  ])
})()

// ── (Fetching already done at top-level — no onMounted needed for data)
</script>
