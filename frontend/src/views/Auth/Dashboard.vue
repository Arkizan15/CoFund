<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-chart-bar text-emerald-700 text-lg"></i>
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500 text-sm">Selamat datang kembali, <strong class="text-emerald-700">{{ authStore.user?.name || 'User' }}</strong></p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-wallet text-lg text-emerald-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Saldo Tersedia</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(authStore.user?.balance || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-heart text-lg text-blue-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Backing</p>
              <p class="text-xl font-bold text-gray-900">{{ myBackingsCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-flag text-lg text-purple-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Kampanye Saya</p>
              <p class="text-xl font-bold text-gray-900">{{ myCampaigns.length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-shield text-lg text-emerald-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Role Akun</p>
              <Badge
                :value="roleLabel"
                :severity="roleSeverity"
                class="!px-3 !py-1 !rounded-full !text-xs !font-semibold"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Creator: Campaign Creation Form -->
      <div v-if="isCreator" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-plus-circle text-lg text-emerald-700"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">Buat Kampanye Baru</h2>
            <p class="text-xs text-gray-400">Lengkapi detail kampanye crowdfunding Anda</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-5">
          <!-- Title -->
          <div class="flex flex-col gap-1.5 lg:col-span-2">
            <label for="form-title" class="text-sm font-semibold text-gray-700">Judul Kampanye <span class="text-red-400">*</span></label>
            <InputText
              id="form-title"
              v-model="form.title"
              placeholder="Masukkan judul kampanye yang menarik"
              class="w-full !rounded-xl !text-sm"
              :class="{ 'p-invalid': formErrors.title }"
              :maxlength="100"
            />
            <small class="text-gray-400 text-xs text-right">{{ form.title.length }}/100</small>
            <small v-if="formErrors.title" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.title }}
            </small>
          </div>

          <!-- Category -->
          <div class="flex flex-col gap-1.5">
            <label for="form-category" class="text-sm font-semibold text-gray-700">Kategori <span class="text-red-400">*</span></label>
            <Dropdown
              id="form-category"
              v-model="form.category_id"
              :options="categories"
              optionLabel="name"
              optionValue="id"
              placeholder="Pilih kategori"
              class="w-full"
              :class="{ 'p-invalid': formErrors.category_id }"
            />
            <small v-if="formErrors.category_id" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.category_id }}
            </small>
          </div>

          <!-- Deadline -->
          <div class="flex flex-col gap-1.5">
            <label for="form-deadline" class="text-sm font-semibold text-gray-700">Tenggat Waktu <span class="text-red-400">*</span></label>
            <Calendar
              id="form-deadline"
              v-model="form.deadline"
              :minDate="minDeadline"
              dateFormat="dd/mm/yy"
              placeholder="Pilih tanggal"
              class="w-full"
              :class="{ 'p-invalid': formErrors.deadline }"
              showIcon
            />
            <small v-if="formErrors.deadline" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.deadline }}
            </small>
            <small v-else class="text-gray-400 text-xs">Minimal 7 hari dari sekarang</small>
          </div>

          <!-- Target Amount -->
          <div class="flex flex-col gap-1.5">
            <label for="form-target" class="text-sm font-semibold text-gray-700">Target Dana <span class="text-red-400">*</span></label>
            <InputNumber
              id="form-target"
              v-model="form.target_amount"
              :min="100000"
              :step="500000"
              prefix="Rp "
              placeholder="Masukkan target dana"
              class="w-full"
              :class="{ 'p-invalid': formErrors.target_amount }"
              fluid
            />
            <small v-if="formErrors.target_amount" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.target_amount }}
            </small>
            <small v-else class="text-gray-400 text-xs">Minimal Rp 100.000</small>
          </div>

          <!-- Video URL (optional) -->
          <div class="flex flex-col gap-1.5">
            <label for="form-video" class="text-sm font-semibold text-gray-700">Video URL <span class="text-gray-400 font-normal">(Opsional)</span></label>
            <InputText
              id="form-video"
              v-model="form.video_url"
              placeholder="https://youtube.com/watch?v=..."
              class="w-full !rounded-xl !text-sm"
              :class="{ 'p-invalid': formErrors.video_url }"
            />
            <small v-if="formErrors.video_url" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.video_url }}
            </small>
          </div>

          <!-- Description -->
          <div class="flex flex-col gap-1.5 lg:col-span-2">
            <label for="form-desc" class="text-sm font-semibold text-gray-700">Deskripsi Kampanye <span class="text-red-400">*</span></label>
            <Textarea
              id="form-desc"
              v-model="form.description"
              rows="6"
              placeholder="Jelaskan secara detail kampanye Anda — latar belakang, tujuan, rencana penggunaan dana, dan dampak yang akan dicapai..."
              class="w-full !rounded-xl !text-sm"
              :class="{ 'p-invalid': formErrors.description }"
            />
            <small v-if="formErrors.description" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.description }}
            </small>
          </div>
        </div>

        <!-- Error Banner -->
        <div
          v-if="formError"
          class="mt-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-start gap-3"
        >
          <i class="pi pi-exclamation-circle mt-0.5 flex-shrink-0"></i>
          <span>{{ formError }}</span>
        </div>

        <!-- Success Banner -->
        <div
          v-if="createdCampaign"
          class="mt-5 p-5 bg-emerald-50 border border-emerald-200 rounded-xl"
        >
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-200 flex items-center justify-center flex-shrink-0">
              <i class="pi pi-check-circle text-emerald-700"></i>
            </div>
            <div class="flex-1">
              <p class="text-sm font-semibold text-emerald-800">Kampanye berhasil dibuat sebagai draft!</p>
              <p class="text-xs text-emerald-600 mt-0.5">{{ createdCampaign.title }}</p>
              <div class="mt-3 flex gap-2">
                <Button
                  label="Kirim ke Review"
                  icon="pi pi-send"
                  class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !rounded-xl !text-sm !px-5"
                  :loading="submitLoading"
                  @click="handleSubmitForReview"
                />
                <Button
                  label="Edit Lagi"
                  icon="pi pi-pencil"
                  class="p-button-text !text-emerald-700 !rounded-xl !text-sm"
                  @click="createdCampaign = null"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="!createdCampaign" class="mt-6 flex flex-col sm:flex-row gap-3 justify-end border-t border-gray-100 pt-6">
          <Button
            label="Reset Form"
            icon="pi pi-refresh"
            class="p-button-text !text-gray-500 !rounded-xl"
            @click="resetForm"
          />
          <Button
            label="Buat Kampanye"
            icon="pi pi-plus-circle"
            class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !px-8 !rounded-xl shadow-sm"
            :loading="createLoading"
            :disabled="createLoading"
            @click="handleCreateCampaign"
          />
        </div>
      </div>

      <!-- Non-Creator: Upgrade prompt -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-shield text-xl text-purple-700"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Jadilah Creator untuk Membuat Kampanye</h2>
            <p class="text-sm text-gray-500 mt-1 max-w-2xl">
              Anda saat ini terdaftar sebagai <strong>Backer</strong>. Untuk membuat kampanye crowdfunding,
              ajukan upgrade akun menjadi Creator melalui halaman Profil Saya.
            </p>
            <router-link :to="{ name: 'Profile' }">
              <Button
                label="Ajukan Upgrade ke Creator"
                icon="pi pi-shield"
                class="mt-4 !bg-purple-600 !border-none hover:!bg-purple-700 !text-white !rounded-xl"
              />
            </router-link>
          </div>
        </div>
      </div>

      <!-- My Campaigns Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-gray-800">Kampanye Saya</h2>
            <p class="text-xs text-gray-400 mt-0.5">Riwayat kampanye yang telah Anda buat</p>
          </div>
          <Badge
            :value="myCampaigns.length + ' kampanye'"
            severity="info"
            class="!bg-emerald-50 !text-emerald-700 !rounded-full !text-xs !font-medium !px-3"
          />
        </div>

        <div v-if="myCampaignsLoading" class="flex items-center justify-center py-12">
          <i class="pi pi-spin pi-spinner text-2xl text-emerald-600"></i>
        </div>

        <div v-else-if="myCampaigns.length === 0" class="text-center py-12">
          <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
            <i class="pi pi-flag text-2xl text-gray-300"></i>
          </div>
          <p class="text-gray-500 text-sm font-medium">Belum ada kampanye</p>
          <p class="text-gray-400 text-xs mt-1">Kampanye yang Anda buat akan muncul di sini</p>
        </div>

        <DataTable
          v-else
          :value="myCampaigns"
          class="!text-sm"
          stripedRows
          responsiveLayout="scroll"
          :paginator="true"
          :rows="10"
        >
          <Column field="id" header="ID" :style="{ width: '60px' }" />
          <Column field="title" header="Judul">
            <template #body="{ data }">
              <div class="max-w-[220px]">
                <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Tanpa Kategori' }}</p>
              </div>
            </template>
          </Column>
          <Column header="Target" :style="{ width: '120px' }">
            <template #body="{ data }">
              <span class="font-medium text-gray-800">{{ formatCurrency(data.target_amount) }}</span>
            </template>
          </Column>
          <Column header="Terkumpul" :style="{ width: '120px' }">
            <template #body="{ data }">
              <span class="font-semibold text-emerald-700">{{ formatCurrency(data.collected_amount || 0) }}</span>
            </template>
          </Column>
          <Column header="Status" :style="{ width: '100px' }">
            <template #body="{ data }">
              <Badge
                :value="statusLabel(data.status)"
                :severity="statusSeverity(data.status)"
                class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5"
              />
            </template>
          </Column>
          <Column header="Aksi" :style="{ width: '120px' }">
            <template #body="{ data }">
              <div class="flex items-center gap-1.5">
                <router-link
                  :to="`/campaigns/${data.slug}`"
                  class="flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-medium no-underline px-2 py-1 rounded-lg hover:bg-emerald-50 transition-colors"
                >
                  <i class="pi pi-eye text-[10px]"></i>Lihat
                </router-link>
                <Button
                  v-if="data.status === 'draft'"
                  icon="pi pi-send"
                  class="!w-7 !h-7 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !text-white !text-[10px] !shadow-sm"
                  v-tooltip.left="'Kirim ke Review'"
                  @click="handleTableSubmit(data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useToast } from 'primevue/usetoast'
import campaignService from '@/services/campaignService'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const isCreator = computed(() => authStore.getUserRole === 'creator')
const isAdmin = computed(() => authStore.getUserRole === 'admin')

const roleLabel = computed(() => {
  const labels = { admin: 'Admin', creator: 'Creator', backer: 'Backer', guest: 'Tamu' }
  return labels[authStore.user?.role] || 'Backer'
})

const roleSeverity = computed(() => {
  const severities = { admin: 'danger', creator: 'success', backer: 'info', guest: 'warning' }
  return severities[authStore.user?.role] || 'info'
})

// My Campaigns
const myCampaigns = ref([])
const myCampaignsLoading = ref(true)
const myBackingsCount = computed(() => authStore.user?.total_backings || 0)

// Categories
const categories = ref([])

// Campaign Form
const form = reactive({
  title: '',
  category_id: null,
  description: '',
  target_amount: null,
  deadline: null,
  video_url: '',
})
const formErrors = reactive({})
const formError = ref('')
const createLoading = ref(false)
const submitLoading = ref(false)
const createdCampaign = ref(null)

const minDeadline = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 7)
  return date
})

function resetForm() {
  form.title = ''
  form.category_id = null
  form.description = ''
  form.target_amount = null
  form.deadline = null
  form.video_url = ''
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  formError.value = ''
  createdCampaign.value = null
}

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

function validateForm() {
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  formError.value = ''
  let valid = true

  if (!form.title.trim()) {
    formErrors.title = 'Judul kampanye wajib diisi'
    valid = false
  }
  if (!form.category_id) {
    formErrors.category_id = 'Kategori wajib dipilih'
    valid = false
  }
  if (!form.description.trim()) {
    formErrors.description = 'Deskripsi kampanye wajib diisi'
    valid = false
  }
  if (!form.target_amount || form.target_amount < 100000) {
    formErrors.target_amount = 'Target dana minimal Rp 100.000'
    valid = false
  }
  if (!form.deadline) {
    formErrors.deadline = 'Tenggat waktu wajib diisi'
    valid = false
  }
  if (form.video_url && !/^https?:\/\/.+/.test(form.video_url)) {
    formErrors.video_url = 'URL video tidak valid. Harus diawali http:// atau https://'
    valid = false
  }
  return valid
}

async function handleCreateCampaign() {
  if (!validateForm()) return

  createLoading.value = true
  formError.value = ''

  try {
    const payload = {
      title: form.title.trim(),
      category_id: form.category_id,
      description: form.description.trim(),
      target_amount: form.target_amount,
      deadline: form.deadline.toISOString().split('T')[0],
    }
    if (form.video_url.trim()) {
      payload.video_url = form.video_url.trim()
    }

    const res = await campaignService.createCampaign(payload)
    createdCampaign.value = res.data
    toast.add({ severity: 'success', summary: 'Berhasil', detail: res.message || 'Kampanye berhasil dibuat.', life: 4000 })
    await fetchMyCampaigns()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Gagal membuat kampanye. Silakan coba lagi.'
    toast.add({ severity: 'error', summary: 'Gagal', detail: formError.value, life: 4000 })
  } finally {
    createLoading.value = false
  }
}

async function handleSubmitForReview() {
  if (!createdCampaign.value?.id) return

  submitLoading.value = true
  try {
    await campaignService.submitForReview(createdCampaign.value.id)
    toast.add({ severity: 'success', summary: 'Dikirim ke Review', detail: 'Kampanye dikirim ke admin untuk direview.', life: 4000 })
    createdCampaign.value.status = 'review'
    createdCampaign.value = null
    resetForm()
    await fetchMyCampaigns()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal mengirim ke review.', life: 4000 })
  } finally {
    submitLoading.value = false
  }
}

async function handleTableSubmit(data) {
  try {
    await campaignService.submitForReview(data.id)
    data.status = 'review'
    toast.add({ severity: 'success', summary: 'Dikirim ke Review', detail: 'Kampanye dikirim ke admin.', life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 4000 })
  }
}

async function fetchMyCampaigns() {
  try {
    const res = await campaignService.getMyCampaigns()
    myCampaigns.value = res?.data || []
  } catch (e) {
    myCampaigns.value = []
  } finally {
    myCampaignsLoading.value = false
  }
}

async function fetchCategories() {
  try {
    const res = await campaignService.getCategories()
    categories.value = res?.data || []
  } catch (e) {
    categories.value = []
  }
}

onMounted(async () => {
  await Promise.all([
    fetchMyCampaigns(),
    fetchCategories(),
  ])
})
</script>
