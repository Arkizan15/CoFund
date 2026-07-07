<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <div class="text-sm text-gray-400 mb-6">
        <router-link :to="{ name: 'Home' }" class="hover:text-emerald-600 transition-colors">Home</router-link>
        <span class="mx-2">/</span>
        <router-link :to="{ name: 'CampaignList' }" class="hover:text-emerald-600 transition-colors">Kampanye</router-link>
        <span class="mx-2">/</span>
        <span class="text-gray-600">{{ campaign.title || 'Detail Kampanye' }}</span>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-20">
        <i class="pi pi-spin pi-spinner text-4xl text-emerald-600"></i>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Hero Image Gallery -->
          <div class="relative rounded-2xl overflow-hidden bg-gray-100 shadow-md">
            <!-- Main Image -->
            <div class="relative h-64 md:h-80">
              <img
                :src="activeImageUrl"
                :alt="campaign.title"
                class="w-full h-full object-cover transition-opacity duration-300"
                :key="activeImageIndex"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
              <div class="absolute top-4 left-4">
                <Badge
                  :value="statusLabel(campaign.status)"
                  :severity="statusSeverity(campaign.status)"
                  class="!bg-white/90 !text-xs !px-3 !py-1 !rounded-full !font-semibold shadow-sm"
                />
              </div>

              <!-- Image Counter -->
              <div v-if="galleryImages.length > 1" class="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-3 py-1.5 rounded-full">
                {{ activeImageIndex + 1 }} / {{ galleryImages.length }}
              </div>
            </div>

            <!-- Thumbnail Strip -->
            <div v-if="galleryImages.length > 1" class="flex gap-2 p-3 bg-gray-50 overflow-x-auto">
              <button
                v-for="(img, idx) in galleryImages"
                :key="img.id || idx"
                class="flex-shrink-0 w-20 h-16 rounded-lg overflow-hidden border-2 transition-all duration-200"
                :class="idx === activeImageIndex
                  ? 'border-emerald-500 ring-2 ring-emerald-200 opacity-100'
                  : 'border-transparent opacity-60 hover:opacity-100'"
                @click="activeImageIndex = idx"
              >
                <img
                  :src="img.image_url"
                  :alt="'Gambar ' + (idx + 1)"
                  class="w-full h-full object-cover"
                />
              </button>
            </div>
          </div>

          <!-- Campaign Details -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ campaign.title }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <i class="pi pi-user text-emerald-600"></i>
                <span>Oleh {{ campaign.user?.name || 'N/A' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-tag text-emerald-600"></i>
                <span>{{ campaign.category?.name || 'Umum' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <i class="pi pi-calendar text-emerald-600"></i>
                <span v-if="campaign.deadline">Tenggat: {{ formatDate(campaign.deadline) }}</span>
              </div>
            </div>

            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed" v-html="campaign.description || 'Belum ada deskripsi.'"></div>

            <div v-if="campaign.video_url" class="mt-6 rounded-xl overflow-hidden shadow-sm">
              <div class="relative w-full" style="padding-bottom: 56.25%">
                <iframe
                  :src="embedVideoUrl"
                  class="absolute inset-0 w-full h-full"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                ></iframe>
              </div>
            </div>
          </div>

          <!-- Backers Table -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Riwayat Donasi</h3>
            <p class="text-sm text-gray-400 mb-4">Para pendukung kampanye ini</p>
            <div v-if="backers.length === 0" class="text-center py-6">
              <i class="pi pi-inbox text-2xl text-gray-300 mb-2 block"></i>
              <p class="text-gray-400 text-sm">Belum ada pendukung. Jadilah yang pertama!</p>
            </div>
            <DataTable v-else :value="backers" class="!text-sm" stripedRows responsiveLayout="scroll">
              <Column field="user.name" header="Nama">
                <template #body="{ data }">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                      <i class="pi pi-user text-[8px] text-emerald-600"></i>
                    </div>
                    <span>{{ data.user?.name || 'Anonim' }}</span>
                  </div>
                </template>
              </Column>
              <Column field="amount" header="Jumlah">
                <template #body="{ data }">
                  <span class="font-medium text-gray-800">{{ formatCurrency(data.amount) }}</span>
                </template>
              </Column>
              <Column field="created_at" header="Tanggal">
                <template #body="{ data }">
                  <span class="text-gray-400 text-xs">{{ formatShortDate(data.created_at) }}</span>
                </template>
              </Column>
            </DataTable>
          </div>

          <!-- Campaign Updates Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <div class="flex items-center justify-between mb-2">
              <div>
                <h3 class="text-lg font-bold text-gray-800">Pembaruan Kampanye</h3>
                <p class="text-sm text-gray-400">Informasi terbaru dari kreator</p>
              </div>
              <Badge
                v-if="campaign.updates?.length"
                :value="campaign.updates.length + ' update'"
                severity="info"
                class="!bg-emerald-50 !text-emerald-700 !rounded-full !text-xs !font-medium !px-3"
              />
            </div>

            <!-- Post Update Form (visible only to campaign owner) -->
            <div
              v-if="isOwner && (campaign.status === 'active' || campaign.status === 'review')"
              class="mt-4 mb-6 p-5 bg-slate-50 border border-gray-200 rounded-xl"
            >
              <div class="flex items-center gap-2 mb-4">
                <i class="pi pi-pencil text-emerald-600 text-sm"></i>
                <span class="text-sm font-semibold text-gray-700">Tulis Pembaruan Baru</span>
              </div>
              <div class="space-y-3">
                <InputText
                  v-model="updateForm.title"
                  placeholder="Judul pembaruan..."
                  class="w-full !rounded-xl !text-sm"
                  :class="{ 'p-invalid': updateErrors.title }"
                  :maxlength="200"
                />
                <small v-if="updateErrors.title" class="text-red-500 text-xs flex items-center gap-1">
                  <i class="pi pi-exclamation-circle"></i>{{ updateErrors.title }}
                </small>
                <Textarea
                  v-model="updateForm.content"
                  rows="4"
                  placeholder="Tulis konten pembaruan di sini..."
                  class="w-full !rounded-xl !text-sm"
                  :class="{ 'p-invalid': updateErrors.content }"
                />
                <small v-if="updateErrors.content" class="text-red-500 text-xs flex items-center gap-1">
                  <i class="pi pi-exclamation-circle"></i>{{ updateErrors.content }}
                </small>
              </div>
              <div v-if="updateError" class="mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                <i class="pi pi-exclamation-circle mt-0.5"></i>
                <span>{{ updateError }}</span>
              </div>
              <div class="mt-3 flex justify-end">
                <Button
                  label="Publikasikan Update"
                  icon="pi pi-send"
                  class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !rounded-xl !text-sm !px-5"
                  :loading="updateLoading"
                  :disabled="updateLoading"
                  @click="handlePostUpdate"
                />
              </div>
            </div>

            <!-- Updates Timeline -->
            <div v-if="!campaign.updates?.length && !isOwner" class="text-center py-10">
              <div class="w-14 h-14 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
                <i class="pi pi-history text-2xl text-gray-300"></i>
              </div>
              <p class="text-gray-500 text-sm font-medium">Belum ada pembaruan</p>
              <p class="text-gray-400 text-xs mt-1">Kreator akan memposting update di sini</p>
            </div>

            <div v-else-if="campaign.updates?.length" class="mt-4 space-y-0">
              <div
                v-for="(update, idx) in sortedUpdates"
                :key="update.id"
                class="relative pl-8 pb-6 last:pb-0"
              >
                <div
                  class="absolute left-3 top-2 bottom-0 w-px bg-gray-200"
                  :class="{ '!hidden': idx === sortedUpdates.length - 1 }"
                ></div>
                <div class="absolute left-0 top-1.5 w-6 h-6 rounded-full bg-emerald-100 border-2 border-white flex items-center justify-center">
                  <i class="pi pi-check text-[10px] text-emerald-600"></i>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 hover:bg-slate-100/70 transition-colors">
                  <div class="flex items-start justify-between gap-3 mb-1">
                    <h4 class="text-sm font-bold text-gray-800">{{ update.title }}</h4>
                    <span class="text-[10px] text-gray-400 whitespace-nowrap flex-shrink-0">{{ formatDate(update.created_at) }}</span>
                  </div>
                  <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ update.content }}</p>
                </div>
              </div>
            </div>

            <div v-else-if="isOwner && campaign.status !== 'active' && campaign.status !== 'review'" class="text-center py-6">
              <p class="text-gray-400 text-sm">Update hanya bisa diposting untuk kampanye aktif</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
          <!-- Funding Summary -->
          <div class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Ringkasan Pendanaan</h4>

            <div class="space-y-4">
              <div>
                <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(campaign.collected_amount || 0) }}</div>
                <div class="text-sm text-gray-500 mt-1">Terkumpul</div>
              </div>

              <div>
                <div class="text-sm text-gray-500">Target</div>
                <div class="text-lg font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount || 0) }}</div>
              </div>

              <ProgressBar :value="progressPercent(campaign)" class="!h-6 !rounded-full" />

              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">{{ progressPercent(campaign) }}% terkumpul</span>
                <span class="flex items-center gap-1.5 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ remainingDays(campaign.deadline) }} hari lagi
                </span>
              </div>

              <hr class="border-gray-100" />
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Total Pendukung</span>
                <span class="font-semibold text-gray-800">{{ campaign.backings?.length || 0 }}</span>
              </div>
              <hr class="border-gray-100" />
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Status</span>
                <Badge
                  :value="statusLabel(campaign.status)"
                  :severity="statusSeverity(campaign.status)"
                  class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5"
                />
              </div>
            </div>

            <!-- Backing Flow -->
            <div class="mt-6 pt-6 border-t border-gray-100">
              <!-- User Balance Info -->
              <div v-if="authStore.isAuthenticated && !isOwner" class="mb-4 p-3 bg-white rounded-[15px] border border-emerald-200 flex items-center justify-between">
                <span class="text-xs text-gray-500 font-medium">Saldo Anda</span>
                <span class="text-sm font-bold text-gray-800">{{ formatCurrency(userBalance) }}</span>
              </div>

              <!-- Not Authenticated -->
              <div v-if="!authStore.isAuthenticated">
                <router-link :to="{ name: 'Login', query: { redirect: route.fullPath } }">
                  <Button
                    label="Masuk untuk Mendukung"
                    icon="pi pi-sign-in"
                    class="w-full !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !rounded-xl shadow-sm"
                  />
                </router-link>
              </div>

              <!-- Campaign Owner (can't back own) -->
              <div v-else-if="isOwner">
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-xl text-xs text-yellow-700 flex items-start gap-2">
                  <i class="pi pi-info-circle mt-0.5 flex-shrink-0"></i>
                  <span>Anda tidak dapat mendanai kampanye milik sendiri.</span>
                </div>
              </div>

              <!-- Campaign Not Active -->
              <div v-else-if="campaign.status !== 'active'">
                <div class="p-3 bg-slate-50 border border-gray-200 rounded-xl text-xs text-gray-500 flex items-start gap-2">
                  <i class="pi pi-lock mt-0.5 flex-shrink-0"></i>
                  <span>Kampanye ini belum aktif untuk menerima dukungan dana.</span>
                </div>
              </div>

              <!-- Active Backing Form -->
              <div v-else class="space-y-4">
                <!-- Quick Amount Selectors -->
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
                  <label for="backing-amount" class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Atur Nominal Sendiri</label>
                  <InputNumber
                    id="backing-amount"
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

                <!-- Selected Tier Display -->
                <div v-if="selectedTier" class="p-3 bg-white border border-emerald-200 rounded-[15px]">
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-xs font-semibold text-emerald-800">{{ selectedTier.name }}</p>
                      <p class="text-[10px] text-emerald-600 mt-0.5">Min. {{ formatCurrency(selectedTier.min_amount) }}</p>
                    </div>
                    <button
                      class="text-emerald-400 hover:text-emerald-600 transition-colors"
                      @click="selectedTier = null"
                    >
                      <i class="pi pi-times text-xs"></i>
                    </button>
                  </div>
                </div>

                <!-- Backing Error -->
                <div v-if="backingError" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 flex items-start gap-2">
                  <i class="pi pi-exclamation-circle mt-0.5 flex-shrink-0"></i>
                  <span>{{ backingError }}</span>
                </div>

                <!-- Success Message with Animation -->
                <div v-if="backingSuccess" class="relative overflow-hidden">
                  <!-- Confetti decorations -->
                  <div class="confetti-piece"></div>
                  <div class="confetti-piece"></div>
                  <div class="confetti-piece"></div>

                  <div class="animate-success-slide p-5 bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl shadow-lg text-white">
                    <div class="flex items-start gap-4">
                      <div class="animate-success-pop w-12 h-12 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0 backdrop-blur-sm">
                        <i class="pi pi-check-circle text-2xl text-white"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-base font-bold text-white">Dukungan Berhasil! 🎉</p>
                        <p class="text-sm text-emerald-100 mt-1">
                          Dana sebesar <strong>{{ formatCurrency(lastBackingAmount) }}</strong> telah berhasil masuk ke escrow.
                        </p>
                        <div class="mt-3 flex items-center gap-3 text-xs text-emerald-100/80">
                          <span class="flex items-center gap-1">
                            <i class="pi pi-shield text-[10px]"></i> Escrow aman
                          </span>
                          <span class="flex items-center gap-1">
                            <i class="pi pi-clock text-[10px]"></i> Selesai dicairkan
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <Button
                  v-if="!backingSuccess"
                  label="Dukung Kampanye Ini"
                  icon="pi pi-heart"
                  class="w-full !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3.5 !rounded-xl shadow-sm"
                  :loading="backingLoading"
                  :disabled="backingLoading || !backingAmount"
                  @click="handleBacking"
                />
              </div>
            </div>
          </div>

          <!-- Tier Rewards -->
          <div v-if="campaign.tiers?.length" class="bg-white rounded-[15px] shadow-sm border border-emerald-200 p-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Tier Reward</h4>
            <div class="space-y-3">
              <div
                v-for="tier in campaign.tiers"
                :key="tier.id"
                class="border-2 rounded-[15px] p-4 transition-all duration-200 cursor-pointer"
                :class="selectedTier?.id === tier.id
                  ? 'border-emerald-500 bg-emerald-50 shadow-sm'
                  : tier.remaining_quota === 0
                    ? 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed'
                    : 'border-gray-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/50'"
                @click="selectTier(tier)"
              >
                <div class="flex items-center justify-between mb-2">
                  <h5 class="font-semibold text-gray-800 text-sm">{{ tier.name }}</h5>
                  <span
                    class="text-xs font-medium px-2 py-1 rounded-full"
                    :class="tier.remaining_quota === 0
                      ? 'bg-red-100 text-red-600'
                      : 'bg-white text-gray-500 border border-gray-200'"
                  >
                    {{ tier.remaining_quota === 0 ? 'Habis' : 'Sisa ' + tier.remaining_quota }}
                  </span>
                </div>
                <div class="text-sm font-bold text-emerald-700 mb-1">Min. {{ formatCurrency(tier.min_amount) }}</div>
                <p v-if="tier.reward_description" class="text-xs text-gray-500 mt-1 leading-relaxed">{{ tier.reward_description }}</p>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Badge from 'primevue/badge'
import ProgressBar from 'primevue/progressbar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import * as campaignService from '@/services/campaignService'

const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()

const campaign = ref({})
const backers = ref([])
const loading = ref(true)

// Gallery
const activeImageIndex = ref(0)
const galleryImages = computed(() => {
  if (!campaign.value.images) return []
  // Show uploaded images first, then video thumbnail
  const uploaded = campaign.value.images.filter(img => img.title !== 'Video Thumbnail')
  const videoThumb = campaign.value.images.filter(img => img.title === 'Video Thumbnail')
  // Sort: primary first
  const sorted = [...uploaded].sort((a, b) => (b.is_primary ? 1 : 0) - (a.is_primary ? 1 : 0))
  return [...sorted, ...videoThumb]
})
const activeImageUrl = computed(() => {
  if (galleryImages.value.length > 0) {
    return galleryImages.value[activeImageIndex.value]?.image_url
  }
  return 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&q=80'
})

const embedVideoUrl = computed(() => {
  const url = campaign.value.video_url
  if (!url) return null
  const patterns = [
    /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/,
    /youtu\.be\/([a-zA-Z0-9_-]{11})/,
    /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
    /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
  ]
  for (const pattern of patterns) {
    const match = url.match(pattern)
    if (match) {
      return `https://www.youtube.com/embed/${match[1]}`
    }
  }
  return null
})

const isOwner = computed(() => {
  return authStore.user?.id && campaign.value.user_id === authStore.user.id
})

const userBalance = computed(() => authStore.user?.balance || 0)

const sortedUpdates = computed(() => {
  const updates = campaign.value.updates || []
  return [...updates].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

// Backing State
const backingAmount = ref(null)
const selectedTier = ref(null)
const backingLoading = ref(false)
const backingError = ref('')
const backingSuccess = ref(false)
const lastBackingAmount = ref(0)

const presetAmounts = [50000, 100000, 250000, 500000]

function selectPreset(amount) {
  backingAmount.value = amount
  backingError.value = ''
}

function selectTier(tier) {
  if (tier.remaining_quota === 0) return
  if (selectedTier.value?.id === tier.id) {
    selectedTier.value = null
    return
  }
  selectedTier.value = tier
  backingAmount.value = tier.min_amount
  backingError.value = ''
}

// Update form
const updateForm = reactive({
  title: '',
  content: '',
})
const updateErrors = reactive({})
const updateError = ref('')
const updateLoading = ref(false)


function statusLabel(status) {
  const labels = { draft: 'Draft', review: 'Review', active: 'Aktif', success: 'Sukses', failed: 'Gagal' }
  return labels[status] || status || '-'
}

function statusSeverity(status) {
  const severities = { draft: 'info', review: 'warn', active: 'success', success: 'success', failed: 'danger' }
  return severities[status] || 'info'
}

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatShortDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
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

function validateBacking() {
  backingError.value = ''

  if (!backingAmount.value || Number(backingAmount.value) < 10000) {
    backingError.value = 'Nominal minimal dukungan adalah Rp 10.000'
    return false
  }

  if (selectedTier.value && Number(backingAmount.value) < Number(selectedTier.value.min_amount)) {
    backingError.value = `Nominal minimal untuk tier "${selectedTier.value.name}" adalah ${formatCurrency(selectedTier.value.min_amount)}`
    return false
  }

  if (Number(backingAmount.value) > userBalance.value) {
    backingError.value = 'Saldo tidak mencukupi. Silakan top up terlebih dahulu.'
    return false
  }

  return true
}

async function handleBacking() {
  if (!validateBacking()) return

  const backedAmount = Number(backingAmount.value)
  const backedTier = selectedTier.value

  backingLoading.value = true
  backingSuccess.value = false

  try {
    const payload = {
      campaign_id: campaign.value.id,
      amount: backedAmount,
    }
    if (backedTier) {
      payload.tier_id = backedTier.id
    }

    const res = await campaignService.backCampaign(payload)

    // Update local state
    campaign.value.collected_amount = res.data.collected_amount
    authStore.user.balance = res.data.balance

    lastBackingAmount.value = backedAmount
    backingSuccess.value = true

    // If tier selected, decrement remaining_quota locally
    if (backedTier) {
      const tier = campaign.value.tiers?.find(t => t.id === backedTier.id)
      if (tier) tier.remaining_quota = Math.max(0, (tier.remaining_quota || 0) - 1)
    }

    // Reset form
    backingAmount.value = null
    selectedTier.value = null

    toast.add({ severity: 'success', summary: 'Dukungan Berhasil!', detail: res.message || 'Dana telah masuk ke escrow.', life: 5000 })
  } catch (error) {
    backingError.value = error.response?.data?.message || 'Gagal melakukan backing. Silakan coba lagi.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: backingError.value, life: 4000 })
  } finally {
    backingLoading.value = false
  }
}

// Update form functions
function validateUpdate() {
  Object.keys(updateErrors).forEach(k => delete updateErrors[k])
  updateError.value = ''
  let valid = true

  if (!updateForm.title.trim()) {
    updateErrors.title = 'Judul pembaruan wajib diisi'
    valid = false
  }
  if (!updateForm.content.trim()) {
    updateErrors.content = 'Konten pembaruan wajib diisi'
    valid = false
  }
  return valid
}

function resetUpdateForm() {
  updateForm.title = ''
  updateForm.content = ''
  Object.keys(updateErrors).forEach(k => delete updateErrors[k])
  updateError.value = ''
}

async function handlePostUpdate() {
  if (!validateUpdate()) return

  updateLoading.value = true
  updateError.value = ''

  try {
    const res = await campaignService.postUpdate(campaign.value.id, {
      title: updateForm.title.trim(),
      content: updateForm.content.trim(),
    })
    if (!campaign.value.updates) {
      campaign.value.updates = []
    }
    campaign.value.updates.push(res.data)
    resetUpdateForm()
    toast.add({ severity: 'success', summary: 'Update Dipublikasikan', detail: 'Pembaruan kampanye berhasil diposting.', life: 4000 })
  } catch (error) {
    updateError.value = error.response?.data?.message || 'Gagal memposting update.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: updateError.value, life: 4000 })
  } finally {
    updateLoading.value = false
  }
}

onMounted(async () => {
  try {
    const response = await campaignService.getCampaignDetail(route.params.slug)
    const campaignData = response?.data || response || {}
    campaign.value = campaignData

    // Use real backings data from eager-loaded relationship
    if (campaignData.backings?.length) {
      backers.value = campaignData.backings
    } else {
      backers.value = []
    }
  } catch (e) {
    campaign.value = { title: 'Kampanye Tidak Ditemukan' }
    backers.value = []
  } finally {
    loading.value = false
  }
})
</script>
