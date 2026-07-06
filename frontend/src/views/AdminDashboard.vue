<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-shield text-emerald-700 text-lg"></i>
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Panel Admin</h1>
            <p class="text-gray-500 text-sm">Kelola permintaan Creator dan pantau platform</p>
          </div>
        </div>
      </div>

      <TabView class="!rounded-2xl !border-gray-100 !shadow-sm">
        <!-- Tab 1: Verifikasi Kreator -->
        <TabPanel header="Verifikasi Kreator">
          <div v-if="loadingRequests" class="flex items-center justify-center py-16">
            <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
          </div>

          <div v-else-if="pendingRequests.length === 0" class="text-center py-16">
            <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
              <i class="pi pi-check-circle text-4xl text-emerald-300"></i>
            </div>
            <p class="text-gray-500 text-lg font-medium">Tidak ada permintaan menunggu</p>
            <p class="text-gray-400 text-sm mt-1">Semua permintaan upgrade Creator telah diproses</p>
          </div>

          <DataTable
            v-else
            :value="pendingRequests"
            class="!text-sm"
            stripedRows
            responsiveLayout="scroll"
            :paginator="true"
            :rows="10"
            :rowsPerPageOptions="[5, 10, 20]"
          >
            <Column field="id" header="ID" :style="{ width: '70px' }" />
            <Column header="Pemohon">
              <template #body="{ data }">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="pi pi-user text-xs text-emerald-600"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ data.user?.name || 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ data.user?.email }}</p>
                  </div>
                </div>
              </template>
            </Column>
            <Column field="reason" header="Alasan">
              <template #body="{ data }">
                <p class="text-gray-600 text-xs max-w-[200px] truncate" :title="data.reason || '-'">
                  {{ data.reason || '-' }}
                </p>
              </template>
            </Column>
            <Column field="created_at" header="Tanggal">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ formatDate(data.created_at) }}</span>
              </template>
            </Column>
            <Column header="Aksi" :style="{ width: '180px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <Button
                    icon="pi pi-check"
                    class="!w-9 !h-9 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !shadow-sm !text-white"
                    v-tooltip.left="'Setujui'"
                    @click="approveRequest(data)"
                  />
                  <Button
                    icon="pi pi-times"
                    class="!w-9 !h-9 !rounded-full !bg-slate-400 !border-slate-400 hover:!bg-slate-500 !shadow-sm !text-white"
                    v-tooltip.left="'Tolak'"
                    @click="confirmReject(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </TabPanel>

        <!-- Tab 2: Overview Platform -->
        <TabPanel header="Overview Platform">
          <!-- Executive Metric Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <p class="text-emerald-100 text-sm font-medium">Total Kampanye</p>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                  <i class="pi pi-flag text-lg"></i>
                </div>
              </div>
              <p class="text-3xl font-bold">{{ overview.campaigns?.total || 0 }}</p>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-xs text-emerald-100">
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>Aktif: {{ overview.campaigns?.active || 0 }}</span>
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>Sukses: {{ overview.campaigns?.success || 0 }}</span>
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>Gagal: {{ overview.campaigns?.failed || 0 }}</span>
              </div>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <p class="text-blue-100 text-sm font-medium">Total Dana Escrow</p>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                  <i class="pi pi-wallet text-lg"></i>
                </div>
              </div>
              <p class="text-3xl font-bold">{{ formatCurrency(overview.finance?.total_collected || 0) }}</p>
              <p class="text-xs text-blue-100 mt-3">Fee Platform: {{ formatCurrency(overview.finance?.total_platform_fee || 0) }}</p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-6 text-white shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <p class="text-purple-100 text-sm font-medium">Total Pengguna</p>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                  <i class="pi pi-users text-lg"></i>
                </div>
              </div>
              <p class="text-3xl font-bold">{{ overview.users?.total || 0 }}</p>
              <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-xs text-purple-100">
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-300"></span>Creator: {{ overview.users?.creators || 0 }}</span>
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-300"></span>Backer: {{ overview.users?.backers || 0 }}</span>
              </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl p-6 text-white shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <p class="text-orange-100 text-sm font-medium">Total Backing</p>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                  <i class="pi pi-heart text-lg"></i>
                </div>
              </div>
              <p class="text-3xl font-bold">{{ overview.backings?.total || 0 }}</p>
              <p class="text-xs text-orange-100 mt-3">Nilai: {{ formatCurrency(overview.backings?.total_amount || 0) }}</p>
            </div>
          </div>

          <!-- Campaign Management Table -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-800">Semua Kampanye</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kelola dan pantau seluruh kampanye di platform</p>
              </div>
              <Badge
                :value="allCampaigns.length + ' kampanye'"
                severity="info"
                class="!bg-slate-100 !text-slate-600 !rounded-full !text-xs !font-medium !px-3"
              />
            </div>
            <DataTable
              :value="allCampaigns"
              class="!text-sm"
              stripedRows
              responsiveLayout="scroll"
              :paginator="true"
              :rows="10"
              :rowsPerPageOptions="[5, 10, 20]"
            >
              <Column field="id" header="ID" :style="{ width: '60px' }" />
              <Column field="title" header="Judul Kampanye">
                <template #body="{ data }">
                  <div class="max-w-[200px]">
                    <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Tanpa Kategori' }}</p>
                  </div>
                </template>
              </Column>
              <Column header="Creator">
                <template #body="{ data }">
                  <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
                </template>
              </Column>
              <Column header="Target" :style="{ width: '120px' }">
                <template #body="{ data }">
                  <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
                </template>
              </Column>
              <Column header="Terkumpul" :style="{ width: '120px' }">
                <template #body="{ data }">
                  <span class="font-semibold text-emerald-700 text-sm">{{ formatCurrency(data.collected_amount || 0) }}</span>
                </template>
              </Column>
              <Column field="status" header="Status" :style="{ width: '100px' }">
                <template #body="{ data }">
                  <Badge
                    :value="statusLabel(data.status)"
                    :severity="statusSeverity(data.status)"
                    class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5"
                  />
                </template>
              </Column>
              <Column header="Aksi" :style="{ width: '140px' }">
                <template #body="{ data }">
                  <div class="flex items-center gap-1.5">
                    <Button
                      v-if="data.status === 'review'"
                      icon="pi pi-check"
                      class="!w-8 !h-8 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !shadow-sm !text-white !text-xs"
                      v-tooltip.left="'Setujui'"
                      @click="approveCampaign(data)"
                    />
                    <Button
                      v-if="data.status === 'review'"
                      icon="pi pi-times"
                      class="!w-8 !h-8 !rounded-full !bg-slate-400 !border-slate-400 hover:!bg-slate-500 !shadow-sm !text-white !text-xs"
                      v-tooltip.left="'Tolak'"
                      @click="openCampaignRejectDialog(data)"
                    />
                    <Button
                      v-if="data.status === 'active' || data.status === 'review'"
                      icon="pi pi-ban"
                      class="!w-8 !h-8 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !shadow-sm !text-white !text-xs"
                      v-tooltip.left="'Ban Kampanye'"
                      @click="confirmBanCampaign(data)"
                    />
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </TabPanel>
      </TabView>
    </div>

    <!-- Reject Role Request Dialog -->
    <Dialog
      v-model:visible="rejectDialogVisible"
      header="Tolak Permintaan Creator"
      :modal="true"
      class="!rounded-2xl"
      :style="{ width: '450px' }"
    >
      <div class="space-y-4">
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-user text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ rejectTarget?.user?.name }}</p>
            <p class="text-xs text-gray-400">{{ rejectTarget?.user?.email }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
          <Textarea
            v-model="rejectReason"
            rows="3"
            placeholder="Masukkan alasan mengapa permintaan ditolak..."
            class="w-full !rounded-xl !text-sm"
            :class="{ 'p-invalid': !rejectReason && submitted }"
          />
          <small v-if="!rejectReason && submitted" class="text-red-500 text-xs flex items-center gap-1">
            <i class="pi pi-exclamation-circle"></i>Alasan penolakan wajib diisi
          </small>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="rejectDialogVisible = false" />
          <Button
            label="Tolak Permintaan"
            icon="pi pi-check"
            class="!bg-slate-600 !border-slate-600 !text-white !rounded-xl"
            :loading="rejectLoading"
            @click="executeReject"
          />
        </div>
      </template>
    </Dialog>

    <!-- Reject Campaign Dialog -->
    <Dialog
      v-model:visible="campaignRejectDialogVisible"
      header="Tolak Kampanye"
      :modal="true"
      class="!rounded-2xl"
      :style="{ width: '450px' }"
    >
      <div class="space-y-4">
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-flag text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ campaignRejectTarget?.title }}</p>
            <p class="text-xs text-gray-400">Oleh {{ campaignRejectTarget?.user?.name || 'N/A' }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
          <Textarea
            v-model="campaignRejectReason"
            rows="3"
            placeholder="Masukkan alasan mengapa kampanye ditolak..."
            class="w-full !rounded-xl !text-sm"
            :class="{ 'p-invalid': !campaignRejectReason && campaignSubmitted }"
          />
          <small v-if="!campaignRejectReason && campaignSubmitted" class="text-red-500 text-xs flex items-center gap-1">
            <i class="pi pi-exclamation-circle"></i>Alasan penolakan wajib diisi
          </small>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="campaignRejectDialogVisible = false" />
          <Button
            label="Tolak Kampanye"
            icon="pi pi-check"
            class="!bg-slate-600 !border-slate-600 !text-white !rounded-xl"
            :loading="campaignRejectLoading"
            @click="executeCampaignReject"
          />
        </div>
      </template>
    </Dialog>

    <!-- Ban Campaign Confirmation Dialog -->
    <Dialog
      v-model:visible="banDialogVisible"
      header="Ban Kampanye"
      :modal="true"
      class="!rounded-2xl"
      :style="{ width: '450px' }"
    >
      <div class="space-y-4">
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
          <i class="pi pi-exclamation-triangle text-red-500 text-xl mt-0.5"></i>
          <div>
            <p class="text-sm font-semibold text-red-800">Tindakan ini tidak dapat dibatalkan</p>
            <p class="text-xs text-red-600 mt-1">
              Memban kampanye akan mengubah status menjadi <strong>failed</strong> dan menghentikan semua pendanaan yang masuk.
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-flag text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ banTarget?.title }}</p>
            <p class="text-xs text-gray-400">Status saat ini: <span class="font-medium capitalize">{{ banTarget?.status }}</span></p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Alasan Ban (Opsional)</label>
          <Textarea
            v-model="banReason"
            rows="2"
            placeholder="Catatan internal untuk ban..."
            class="w-full !rounded-xl !text-sm"
          />
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="banDialogVisible = false" />
          <Button
            label="Ban Kampanye"
            icon="pi pi-ban"
            class="!bg-red-600 !border-red-600 !text-white !rounded-xl"
            :loading="banLoading"
            @click="executeBan"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import TabView from 'primevue/tabview'
import TabPanel from 'primevue/tabpanel'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import { useToast } from 'primevue/usetoast'
import roleService from '@/services/roleService'
import api from '@/services/api'

const toast = useToast()

const pendingRequests = ref([])
const allCampaigns = ref([])
const loadingRequests = ref(true)
const overview = ref({
  campaigns: { total: 0, active: 0, success: 0, failed: 0 },
  finance: { total_collected: 0, total_platform_fee: 0 },
  users: { total: 0, creators: 0, backers: 0 },
  backings: { total: 0, total_amount: 0 },
})

// Tab 1: Reject Role Request
const rejectDialogVisible = ref(false)
const rejectTarget = ref(null)
const rejectReason = ref('')
const rejectLoading = ref(false)
const submitted = ref(false)

function confirmReject(data) {
  rejectTarget.value = data
  rejectReason.value = ''
  submitted.value = false
  rejectDialogVisible.value = true
}

async function executeReject() {
  submitted.value = true
  if (!rejectReason.value.trim()) return

  rejectLoading.value = true
  try {
    await roleService.updateRequestStatusAdmin(rejectTarget.value.id, 'rejected', rejectReason.value)
    pendingRequests.value = pendingRequests.value.filter(r => r.id !== rejectTarget.value.id)
    rejectDialogVisible.value = false
    toast.add({ severity: 'success', summary: 'Ditolak', detail: 'Permintaan berhasil ditolak.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    rejectLoading.value = false
  }
}

async function approveRequest(data) {
  try {
    await roleService.updateRequestStatusAdmin(data.id, 'approved')
    pendingRequests.value = pendingRequests.value.filter(r => r.id !== data.id)
    toast.add({ severity: 'success', summary: 'Disetujui', detail: `${data.user?.name} kini menjadi Creator.`, life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  }
}

// Tab 2: Reject Campaign
const campaignRejectDialogVisible = ref(false)
const campaignRejectTarget = ref(null)
const campaignRejectReason = ref('')
const campaignRejectLoading = ref(false)
const campaignSubmitted = ref(false)

function openCampaignRejectDialog(data) {
  campaignRejectTarget.value = data
  campaignRejectReason.value = ''
  campaignSubmitted.value = false
  campaignRejectDialogVisible.value = true
}

async function executeCampaignReject() {
  campaignSubmitted.value = true
  if (!campaignRejectReason.value.trim()) return

  campaignRejectLoading.value = true
  try {
    const data = campaignRejectTarget.value
    await api.post(`/admin/campaigns/${data.id}/reject`, { reason: campaignRejectReason.value })
    data.status = 'draft'
    campaignRejectDialogVisible.value = false
    toast.add({ severity: 'info', summary: 'Ditolak', detail: 'Kampanye dikembalikan ke draft.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    campaignRejectLoading.value = false
  }
}

async function approveCampaign(data) {
  try {
    await api.post(`/admin/campaigns/${data.id}/approve`)
    data.status = 'active'
    toast.add({ severity: 'success', summary: 'Disetujui', detail: 'Kampanye kini aktif.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  }
}

// Ban Campaign
const banDialogVisible = ref(false)
const banTarget = ref(null)
const banReason = ref('')
const banLoading = ref(false)

function confirmBanCampaign(data) {
  banTarget.value = data
  banReason.value = ''
  banDialogVisible.value = true
}

async function executeBan() {
  banLoading.value = true
  try {
    const data = banTarget.value
    await api.post(`/admin/campaigns/${data.id}/ban`)
    data.status = 'failed'
    banDialogVisible.value = false
    toast.add({ severity: 'warn', summary: 'Kampanye Dibanned', detail: `"${data.title}" telah di-ban. Status: failed.`, life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    banLoading.value = false
  }
}

// Utility functions
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
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(async () => {
  try {
    const [reqRes, overviewRes, campaignRes] = await Promise.all([
      roleService.getPendingRequestsAdmin().catch(() => ({ data: { data: [] } })),
      api.get('/admin/overview').catch(() => ({ data: { data: overview.value } })),
      api.get('/admin/pending-approvals').catch(() => ({ data: { data: [] } })),
    ])
    pendingRequests.value = reqRes.data?.data || []
    overview.value = overviewRes.data?.data || overview.value
    allCampaigns.value = campaignRes.data?.data || []
  } catch (e) {
    pendingRequests.value = []
    allCampaigns.value = []
  } finally {
    loadingRequests.value = false
  }
})
</script>
