<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $t('campaign.listTitle') }}</h1>
          <p class="text-gray-500 text-sm mt-1">{{ $t('campaign.listSubtitle') }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <i class="pi pi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <InputText
              v-model="search"
              :placeholder="$t('campaign.searchPlaceholder')"
              class="w-full pl-10 !border-gray-200 !rounded-xl !py-3 !text-sm"
            />
          </div>
          <div class="w-full sm:w-56">
            <Dropdown
              v-model="selectedCategory"
              :options="categories"
              optionLabel="label"
              optionValue="value"
              :placeholder="$t('campaign.allCategories')"
              class="w-full"
            />
          </div>
        </div>
      </div>

      <div v-if="filteredCampaigns.length === 0" class="text-center py-20">
        <i class="pi pi-inbox text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-500">{{ $t('campaign.noCampaigns') }}</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="campaign in filteredCampaigns" :key="campaign.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
          <div class="relative h-48 overflow-hidden bg-gray-100">
            <img
              v-if="campaign.image_url || campaign.image"
              :src="campaign.image_url || campaign.image || 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=600&q=80'"
              :alt="campaign.title"
              class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
              <i class="pi pi-image text-4xl"></i>
            </div>
            <div class="absolute top-3 left-3">
              <span class="bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
                {{ campaign.category?.name || $t('common.general') }}
              </span>
            </div>
          </div>

          <div class="p-5 flex flex-col flex-1">
            <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ campaign.title }}</h3>

            <div class="space-y-3 mb-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ $t('campaign.collected') }}</span>
                <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.collected_amount || 0) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ $t('campaign.target') }}</span>
                <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount || 0) }}</span>
              </div>
            </div>

            <div class="space-y-2">
              <ProgressBar :value="progressPercent(campaign)" class="!h-2.5 !rounded-full" />
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">{{ $t('campaign.percentCollected', { percent: progressPercent(campaign) }) }}</span>
                <span class="flex items-center gap-1 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ $t('common.daysLeft', { days: remainingDays(campaign.deadline) }) }}
                </span>
              </div>
            </div>

            <div class="mt-5 pt-4 border-t border-gray-100 flex gap-2">
              <router-link :to="`/campaigns/${campaign.slug}`" class="flex-1">
                <Button
                  :label="$t('common.seeDetail')"
                  icon="pi pi-arrow-right"
                  iconPos="right"
                  class="w-full bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 font-medium !py-2.5 !rounded-xl shadow-sm"
                />
              </router-link>
              <Button
                v-if="campaign.status === 'active'"
                :label="$t('common.support')"
                icon="pi pi-heart"
                class="flex-1 bg-emerald-600 border-none hover:bg-emerald-700 text-white font-medium !py-2.5 !rounded-xl shadow-sm"
                @click="openBackingDialog(campaign)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
      <!-- Backing Dialog Modal -->
      <Dialog
        v-model:visible="backingDialogVisible"            :header="$t('campaign.backThisCampaign') + ': ' + (backingCampaign?.title || '')"
        :modal="true"
        :closable="true"
        class="!rounded-2xl !w-full !max-w-md"
        @hide="resetBackingForm"
      >
        <div v-if="backingCampaign" class="space-y-5 py-2">
          <!-- Auth Check -->
          <div v-if="!authStore.isAuthenticated" class="text-center py-4">
            <i class="pi pi-lock text-3xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-600 text-sm mb-4">{{ $t('campaign.loginToBack') }}</p>
            <router-link :to="{ name: 'Login', query: { redirect: route.fullPath } }" @click="backingDialogVisible = false">
              <Button
                :label="$t('campaign.loginNow')"
                icon="pi pi-sign-in"
                class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-medium !rounded-xl"
              />
            </router-link>
          </div>

          <!-- Owner Check -->
          <div v-else-if="isOwner" class="text-center py-4">
            <i class="pi pi-info-circle text-3xl text-yellow-400 mb-3 block"></i>
            <p class="text-gray-600 text-sm">{{ $t('campaign.cantBackOwn') }}</p>
          </div>

          <!-- Campaign Not Active -->
          <div v-else-if="backingCampaign.status !== 'active'" class="text-center py-4">
            <i class="pi pi-lock text-3xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-600 text-sm">{{ $t('campaign.campaignNotActive') }}</p>
          </div>

          <!-- Backing Form -->
          <template v-else>
            <!-- Balance Info -->
            <div class="p-3 bg-slate-50 rounded-xl flex items-center justify-between">
              <span class="text-xs text-gray-500 font-medium">{{ $t('campaign.yourBalance') }}</span>
              <span class="text-sm font-bold text-gray-800">{{ formatCurrency(userBalance) }}</span>
            </div>

            <!-- Preset Amounts -->
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">{{ $t('campaign.presetAmount') }}</label>
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
              <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('campaign.customAmount') }}</label>
              <InputNumber
                v-model="backingAmount"
                :min="10000"
                :step="10000"
                prefix="Rp "
                :placeholder="$t('campaign.amountPlaceholder')"
                class="w-full"
                :class="{ 'p-invalid': backingError }"
                fluid
              />
              <small v-if="backingError" class="text-red-500 text-xs flex items-center gap-1">
                <i class="pi pi-exclamation-circle"></i>{{ backingError }}
              </small>
              <small v-else class="text-gray-400 text-xs">{{ $t('common.minAmount') }}</small>
            </div>

            <!-- Success Message -->
            <div v-if="backingSuccess" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-200 flex items-center justify-center flex-shrink-0">
                  <i class="pi pi-check-circle text-emerald-700"></i>
                </div>
                <div>
                  <p class="text-sm font-semibold text-emerald-800">{{ $t('campaign.backingSuccess') }}</p>
                  <p class="text-xs text-emerald-600 mt-0.5">{{ $t('campaign.backingSuccessDetail', { amount: formatCurrency(lastBackingAmount) }) }}</p>
                </div>
              </div>
            </div>

            <!-- Submit Button (opens confirmation) -->
            <Button
              v-if="!backingSuccess"
              :label="$t('campaign.backThisCampaign')"
              icon="pi pi-heart"
              class="w-full !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !rounded-xl shadow-sm"
              :loading="backingLoading"
              :disabled="backingLoading || !backingAmount"
              @click="openConfirmDialog"
            />
          </template>
        </div>
      </Dialog>

      <!-- Confirmation Dialog -->
      <Dialog
        v-model:visible="confirmDialogVisible"
        :header="$t('campaign.confirmDonation')"
        :modal="true"
        :closable="true"
        class="!rounded-2xl !w-full !max-w-sm"
        @hide="confirmAmount = 0"
      >
        <div class="space-y-5 py-2">
          <div class="bg-emerald-50 rounded-xl p-4 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
              <i class="pi pi-heart text-xl text-emerald-600"></i>
            </div>
            <p class="text-sm text-gray-500">{{ $t('campaign.youWillSupport') }}</p>
            <p class="text-base font-bold text-gray-800 mt-1 line-clamp-2">{{ backingCampaign?.title }}</p>
          </div>

          <div class="bg-slate-50 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">{{ $t('campaign.donationAmount') }}</span>
              <span class="text-lg font-bold text-emerald-700">{{ formatCurrency(confirmAmount) }}</span>
            </div>
            <hr class="border-gray-200" />
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">{{ $t('campaign.availableBalance') }}</span>
              <span class="font-semibold text-gray-800">{{ formatCurrency(userBalance) }}</span>
            </div>
            <hr class="border-gray-200" />
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-500">{{ $t('campaign.remainingBalance') }}</span>
              <span class="font-semibold" :class="userBalance - confirmAmount >= 0 ? 'text-emerald-700' : 'text-red-600'">
                {{ formatCurrency(Math.max(0, userBalance - confirmAmount)) }}
              </span>
            </div>
          </div>

          <div class="flex gap-2">
            <Button
              :label="$t('common.cancel')"
              icon="pi pi-arrow-left"
              class="flex-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium !py-3 !rounded-xl"
              @click="confirmDialogVisible = false"
            />
            <Button
              :label="$t('campaign.confirmDonationAction')"
              icon="pi pi-check-circle"
              class="flex-1 !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !rounded-xl shadow-sm"
              :loading="backingLoading"
              :disabled="backingLoading"
              @click="confirmBacking"
            />
          </div>
        </div>
      </Dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import ProgressBar from 'primevue/progressbar'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'primevue/usetoast'
import * as campaignService from '@/services/campaignService'

const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()

const campaigns = ref([])
const search = ref('')
const selectedCategory = ref(null)
const categories = ref([])

// Backing Dialog State
const backingDialogVisible = ref(false)
const backingCampaign = ref(null)
const backingAmount = ref(null)
const backingLoading = ref(false)
const backingError = ref('')
const backingSuccess = ref(false)
const lastBackingAmount = ref(0)

// Confirmation Dialog State
const confirmDialogVisible = ref(false)
const confirmAmount = ref(0)

const presetAmounts = [50000, 100000, 250000, 500000]

const isOwner = computed(() => {
  return authStore.user?.id && backingCampaign.value?.user_id === authStore.user.id
})

const userBalance = computed(() => authStore.user?.balance || 0)

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
  backingSuccess.value = false
  lastBackingAmount.value = 0
  confirmDialogVisible.value = false
  confirmAmount.value = 0
}

function validateBacking() {
  backingError.value = ''
  if (!backingAmount.value || Number(backingAmount.value) < 10000) {
    backingError.value = 'Nominal minimal dukungan adalah Rp 10.000'
    return false
  }
  if (Number(backingAmount.value) > userBalance.value) {
    backingError.value = 'Saldo tidak mencukupi. Silakan top up terlebih dahulu.'
    return false
  }
  return true
}

function openConfirmDialog() {
  if (!validateBacking()) return
  confirmAmount.value = Number(backingAmount.value)
  confirmDialogVisible.value = true
}

async function confirmBacking() {
  confirmDialogVisible.value = false
  backingLoading.value = true
  backingSuccess.value = false
  try {
    const payload = {
      campaign_id: backingCampaign.value.id,
      amount: confirmAmount.value,
    }
    const res = await campaignService.backCampaign(payload)
    // Update collected amount locally
    const idx = campaigns.value.findIndex(c => c.id === backingCampaign.value.id)
    if (idx !== -1) {
      campaigns.value[idx].collected_amount = res.data.collected_amount
    }
    // Update user balance
    authStore.user.balance = res.data.balance
    lastBackingAmount.value = confirmAmount.value
    backingSuccess.value = true
    toast.add({ severity: 'success', summary: 'Dukungan Berhasil!', detail: res.message || 'Dana telah masuk ke escrow.', life: 5000 })
  } catch (error) {
    backingError.value = error.response?.data?.message || 'Gagal melakukan backing. Silakan coba lagi.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: backingError.value, life: 4000 })
  } finally {
    backingLoading.value = false
  }
}

onMounted(async () => {
  try {
    const data = await campaignService.getCampaigns()
    campaigns.value = data?.data || data || []
    const unique = {}
    campaigns.value.forEach(c => {
      const name = c.category?.name || 'Umum'
      if (!unique[name]) {
        unique[name] = true
        categories.value.push({ label: name, value: name })
      }
    })
  } catch (e) {
    campaigns.value = []
  }
})

const filteredCampaigns = computed(() => {
  return campaigns.value.filter(c => {
    const q = search.value.toLowerCase()
    const matchesQuery = !q ||
      (c.title && c.title.toLowerCase().includes(q)) ||
      (c.description && c.description.toLowerCase().includes(q))
    const matchesCategory = !selectedCategory.value || (c.category?.name === selectedCategory.value)
    return matchesQuery && matchesCategory
  })
})

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val)
}

function remainingDays(deadline) {
  if (!deadline) return '-'
  const d = new Date(deadline)
  const diff = Math.ceil((d - new Date()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
}

function progressPercent(c) {
  const col = Number(c.collected_amount || 0)
  const tar = Number(c.target_amount || 1)
  return Math.min(100, Math.round((col / tar) * 100))
}
</script>
