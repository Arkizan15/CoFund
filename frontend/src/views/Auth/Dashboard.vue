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
            <p class="text-gray-500 text-sm">Selamat datang kembali, {{ authStore.user?.name || 'User' }}</p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div v-if="!isAdmin" class="perspective-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="card-3d card-visible card-3d-inner bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
          <div class="card-3d-inner flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-wallet text-lg text-emerald-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Saldo Tersedia</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(authStore.user?.balance || 0) }}</p>
            </div>
          </div>
        </div>

        <div class="card-3d card-visible card-3d-inner bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
          <div class="card-3d-inner flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-heart text-lg text-blue-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Backing</p>
              <p class="text-xl font-bold text-gray-900">{{ myBackingsCount }}</p>
            </div>
          </div>
        </div>

        <div class="card-3d card-visible card-3d-inner bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
          <div class="card-3d-inner flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-flag text-lg text-purple-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Kampanye Saya</p>
              <p class="text-xl font-bold text-gray-900">{{ myCampaigns.length }}</p>
            </div>
          </div>
        </div>

        <div class="card-3d card-visible card-3d-inner bg-white rounded-2xl shadow-sm border border-gray-100 p-4 md:p-5">
          <div class="card-3d-inner flex items-center gap-3">
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

      <!-- Creator: Stats Cards -->
      <div v-if="isCreator" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-users text-lg text-emerald-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Backer</p>
              <p class="text-xl font-bold text-gray-900">{{ creatorStats.total_backers }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-dollar text-lg text-blue-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Terkumpul</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(creatorStats.total_collected) }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-chart-line text-lg text-purple-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Kampanye Aktif</p>
              <p class="text-xl font-bold text-gray-900">{{ creatorStats.active_campaigns }} / {{ creatorStats.total_campaigns }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Creator: Funding Chart -->
      <div v-if="isCreator && chartData.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-chart-bar text-lg text-emerald-700"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Funding Harian</h2>
            <p class="text-xs text-gray-400">Grafik kumulatif pendanaan per hari</p>
          </div>
        </div>
        <div class="w-full h-64 relative">
          <svg viewBox="0 0 800 200" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
            <defs>
              <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#059669" stop-opacity="0.2" />
                <stop offset="100%" stop-color="#059669" stop-opacity="0" />
              </linearGradient>
            </defs>
            <path
              :d="chartPath"
              fill="none"
              stroke="#059669"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              :d="chartAreaPath"
              fill="url(#chartGrad)"
            />
          </svg>
          <div class="flex justify-between mt-2 text-xs text-gray-400">
            <span>{{ chartData[0]?.date || '' }}</span>
            <span>{{ chartData[chartData.length - 1]?.date || '' }}</span>
          </div>
        </div>
      </div>

      <!-- Backer: Stats Cards -->
      <div v-if="!isCreator && activeTab === 'backings'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-heart text-lg text-blue-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Dibacking</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(backerStats.total_backed) }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-history text-lg text-orange-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Total Refund</p>
              <p class="text-xl font-bold text-gray-900">{{ formatCurrency(backerStats.total_refund) }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-check-circle text-lg text-purple-700"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium">Backing Sukses</p>
              <p class="text-xl font-bold text-gray-900">{{ backerStats.backing_count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Creator: Campaign CTA -->
      <div v-if="isCreator" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-plus-circle text-2xl text-emerald-700"></i>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-bold text-gray-800">Buat Kampanye Baru</h2>
            <p class="text-sm text-gray-500 mt-0.5">Mulai kampanye crowdfunding Anda dan kumpulkan dana dari para backer</p>
          </div>
          <router-link :to="{ name: 'CampaignCreate' }">
            <Button
              label="Buat Kampanye"
              icon="pi pi-plus"
              class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !px-6 !rounded-xl shadow-sm"
            />
          </router-link>
        </div>
      </div>

      <!-- Non-Creator: Upgrade prompt -->
      <div v-if="!isCreator && !isAdmin" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-shield text-xl text-purple-700"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Jadilah Creator untuk Membuat Kampanye</h2>
            <p class="text-sm text-gray-500 mt-1 max-w-2xl">
              Anda saat ini terdaftar sebagai Backer. Untuk membuat kampanye crowdfunding, ajukan upgrade akun menjadi Creator melalui halaman Profil Saya.
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

      <!-- Admin: Simple dashboard message -->
      <div v-if="isAdmin" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
          <i class="pi pi-shield text-3xl text-emerald-700"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Panel Admin</h2>
        <p class="text-gray-500 text-sm mt-2 max-w-md mx-auto">
          Anda login sebagai Admin. Gunakan menu Panel Admin untuk mengelola pengguna, kampanye, dan pengaturan platform.
        </p>
        <router-link :to="{ name: 'AdminDashboard' }">
          <Button
            label="Buka Panel Admin"
            icon="pi pi-external-link"
            class="mt-5 !bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !rounded-xl !py-3 !px-6"
          />
        </router-link>
      </div>

      <!-- Tabs: My Campaigns / My Backings -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex border-b border-gray-100">
          <button
            class="flex-1 sm:flex-none px-6 py-4 text-sm font-semibold transition-all duration-200 relative"
            :class="activeTab === 'campaigns'
              ? 'text-emerald-700'
              : 'text-gray-500 hover:text-gray-700'"
            @click="activeTab = 'campaigns'"
          >
            <i class="pi pi-flag mr-1.5 text-xs"></i>
            Kampanye Saya
            <span
              v-if="activeTab === 'campaigns'"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-full"
            ></span>
          </button>
          <button
            class="flex-1 sm:flex-none px-6 py-4 text-sm font-semibold transition-all duration-200 relative"
            :class="activeTab === 'backings'
              ? 'text-emerald-700'
              : 'text-gray-500 hover:text-gray-700'"
            @click="activeTab = 'backings'"
          >
            <i class="pi pi-heart mr-1.5 text-xs"></i>
            Riwayat Backing
            <span
              v-if="activeTab === 'backings'"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 rounded-full"
            ></span>
          </button>
        </div>

        <!-- ===== MY CAMPAIGNS TABLE ===== -->
        <div v-if="activeTab === 'campaigns'">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <p class="text-xs text-gray-400">Riwayat kampanye yang telah Anda buat</p>
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
                  <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Umum' }}</p>
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
            <Column header="Tier" :style="{ width: '80px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ data.tiers?.length || 0 }} tier</span>
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
            <Column header="Aksi" :style="{ width: '200px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <router-link
                    :to="`/campaigns/${data.slug}`"
                    class="flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-medium no-underline px-2 py-1 rounded-lg hover:bg-emerald-50 transition-colors"
                  >
                    <i class="pi pi-eye text-[10px]"></i> Lihat
                  </router-link>
                  <Button
                    v-if="data.status === 'draft'"
                    icon="pi pi-pencil"
                    class="!w-7 !h-7 !rounded-full !bg-sky-500 !border-sky-500 hover:!bg-sky-600 !text-white !text-[10px] !shadow-sm"
                    v-tooltip.left="'Edit'"
                    @click="handleEditCampaign(data)"
                  />
                  <Button
                    v-if="data.status === 'draft'"
                    icon="pi pi-send"
                    class="!w-7 !h-7 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !text-white !text-[10px] !shadow-sm"
                    v-tooltip.left="'Kirim ke Review'"
                    @click="handleTableSubmit(data)"
                  />
                  <Button
                    v-if="data.status === 'draft'"
                    icon="pi pi-trash"
                    class="!w-7 !h-7 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !text-white !text-[10px] !shadow-sm"
                    v-tooltip.left="'Hapus'"
                    @click="confirmDeleteCampaign(data)"
                  />
                  <Button
                    v-if="data.status === 'active'"
                    icon="pi pi-megaphone"
                    class="!w-7 !h-7 !rounded-full !bg-violet-500 !border-violet-500 hover:!bg-violet-600 !text-white !text-[10px] !shadow-sm"
                    v-tooltip.left="'Post Update'"
                    @click="openUpdateDialog(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </div>

        <!-- ===== MY BACKINGS TABLE ===== -->
        <div v-if="activeTab === 'backings'">
          <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
            <p class="text-xs text-gray-400">Riwayat pendanaan kampanye yang Anda dukung</p>
            <Badge
              :value="myBackings.length + ' backing'"
              severity="info"
              class="!bg-blue-50 !text-blue-700 !rounded-full !text-xs !font-medium !px-3"
            />
          </div>

          <div v-if="myBackingsLoading" class="flex items-center justify-center py-12">
            <i class="pi pi-spin pi-spinner text-2xl text-blue-600"></i>
          </div>

          <div v-else-if="myBackings.length === 0" class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
              <i class="pi pi-heart text-2xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">Belum ada riwayat backing</p>
            <p class="text-gray-400 text-xs mt-1">Kampanye yang Anda dukung akan muncul di sini</p>
          </div>

          <DataTable
            v-else
            :value="myBackings"
            class="!text-sm"
            stripedRows
            responsiveLayout="scroll"
            :paginator="true"
            :rows="10"
          >
            <Column header="Kampanye">
              <template #body="{ data }">
                <div class="max-w-[200px]">
                  <router-link
                    :to="`/campaigns/${data.campaign?.slug}`"
                    class="font-medium text-gray-800 hover:text-emerald-600 transition-colors no-underline truncate block"
                  >
                    {{ data.campaign?.title || 'N/A' }}
                  </router-link>
                  <p class="text-xs text-gray-400">{{ data.campaign?.category?.name || 'Umum' }}</p>
                </div>
              </template>
            </Column>
            <Column field="amount" header="Jumlah" :style="{ width: '130px' }">
              <template #body="{ data }">
                <span class="font-semibold text-emerald-700">{{ formatCurrency(data.amount) }}</span>
              </template>
            </Column>
            <Column header="Tier" :style="{ width: '160px' }">
              <template #body="{ data }">
                <div>
                  <span class="text-xs text-gray-500">{{ data.tier?.name || 'Tanpa Tier' }}</span>
                  <p v-if="data.tier?.reward_description" class="text-[10px] text-gray-400 truncate max-w-[140px]">{{ data.tier.reward_description }}</p>
                </div>
              </template>
            </Column>
            <Column field="status" header="Status" :style="{ width: '100px' }">
              <template #body="{ data }">
                <Badge
                  :value="data.status === 'completed' ? 'Selesai' : data.status === 'pending' ? 'Menunggu' : 'Refund'"
                  :severity="data.status === 'completed' ? 'success' : data.status === 'pending' ? 'warn' : 'danger'"
                  class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5"
                />
              </template>
            </Column>
            <Column field="created_at" header="Tanggal" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-400">{{ formatShortDate(data.created_at) }}</span>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>
      <!-- Post Update Dialog -->
      <Dialog
        v-model:visible="updateDialogVisible"
        header="Post Update Kampanye"
        :modal="true"
        class="app-dialog !rounded-[15px] !bg-white !border-2 !border-emerald-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto"
        :style="{ maxWidth: '500px' }"
        @show="onDialogShow"
      >
        <div class="flex flex-col gap-4 py-2">
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700">Judul Update</label>
            <InputText
              v-model="updateForm.title"
              placeholder="Judul pembaruan"
              class="w-full !rounded-xl !text-sm"
            />
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700">Konten Update</label>
            <Textarea
              v-model="updateForm.content"
              placeholder="Tulis pembaruan kampanye..."
              class="w-full !rounded-xl !text-sm"
              rows="5"
            />
          </div>
        </div>
        <template #footer>
          <div class="flex gap-2 justify-end">
            <Button
              label="Batal"
              icon="pi pi-times"
              class="p-button-text !text-gray-500 !rounded-xl"
              @click="updateDialogVisible = false"
            />
            <Button
              label="Posting Update"
              icon="pi pi-send"
              class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !rounded-xl"
              :loading="updateLoading"
              @click="handlePostUpdate"
            />
          </div>
        </template>
      </Dialog>

      <!-- Confirm Delete Campaign Dialog -->
      <Dialog
        v-model:visible="deleteDialogVisible"
        header="Hapus Kampanye"
        :modal="true"
        class="app-dialog !rounded-[15px] !bg-white !border-2 !border-red-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto"
        :style="{ maxWidth: '420px' }"
        @show="onDialogShow"
      >
        <div class="text-center py-4">
          <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-3">
            <i class="pi pi-exclamation-triangle text-2xl text-red-500"></i>
          </div>
          <p class="text-gray-700 font-medium">Apakah Anda yakin ingin menghapus kampanye ini?</p>
          <p class="text-gray-400 text-xs mt-2">Kampanye yang dihapus tidak dapat dikembalikan. Semua data terkait kampanye ini akan dihapus.</p>
          <p class="text-gray-500 text-xs font-semibold mt-3 bg-gray-50 rounded-lg p-2">{{ deleteTargetCampaign?.title }}</p>
        </div>
        <template #footer>
          <div class="flex gap-2 justify-end">
            <Button
              label="Batal"
              icon="pi pi-times"
              class="p-button-text !text-gray-500 !rounded-xl"
              @click="deleteDialogVisible = false"
            />
            <Button
              label="Hapus"
              icon="pi pi-trash"
              class="!bg-red-600 !border-none hover:!bg-red-700 !text-white !rounded-xl"
              :loading="deleteLoading"
              @click="handleDeleteCampaign"
            />
          </div>
        </template>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGenieEffect } from '@/composables/useGenieEffect'
import { useAuthStore } from '@/stores/auth'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useToast } from 'primevue/usetoast'
import campaignService from '@/services/campaignService'
import dashboardService from '@/services/dashboardService'

const { onDialogShow } = useGenieEffect()

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const activeTab = ref('campaigns')

const isCreator = computed(() => authStore.getUserRole === 'creator')
const isAdmin = computed(() => authStore.getUserRole === 'admin')

// Creator Stats
const creatorStats = reactive({
  total_campaigns: 0,
  active_campaigns: 0,
  total_backers: 0,
  total_collected: 0,
})
const chartData = ref([])

// Backer Stats
const backerStats = reactive({
  total_backed: 0,
  total_refund: 0,
  backing_count: 0,
  refund_count: 0,
})

// Funding Chart computed
const maxCumulative = computed(() => {
  if (chartData.value.length === 0) return 0
  return Math.max(...chartData.value.map(d => d.cumulative), 1)
})

const chartPath = computed(() => {
  const data = chartData.value
  if (data.length < 2) return ''
  const w = 800, h = 180
  const pad = 10
  const stepX = (w - pad * 2) / (data.length - 1)
  const max = maxCumulative.value
  return data.map((d, i) => {
    const x = pad + i * stepX
    const y = h - pad - ((d.cumulative / max) * (h - pad * 2))
    return `${i === 0 ? 'M' : 'L'}${x},${y}`
  }).join(' ')
})

const chartAreaPath = computed(() => {
  const data = chartData.value
  if (data.length < 2) return ''
  const w = 800, h = 180
  const pad = 10
  const stepX = (w - pad * 2) / (data.length - 1)
  const max = maxCumulative.value
  const lastX = pad + (data.length - 1) * stepX
  const bottom = h - pad
  let path = chartPath.value
  if (path) {
    path += ` L${lastX},${bottom} L${pad},${bottom} Z`
  }
  return path
})

// Update Dialog
const updateDialogVisible = ref(false)
const updateLoading = ref(false)
const updateCampaignTarget = ref(null)
const updateForm = reactive({ title: '', content: '' })

const deleteDialogVisible = ref(false)
const deleteLoading = ref(false)
const deleteTargetCampaign = ref(null)

function confirmDeleteCampaign(campaign) {
  deleteTargetCampaign.value = campaign
  deleteDialogVisible.value = true
}

async function handleDeleteCampaign() {
  if (!deleteTargetCampaign.value) return
  deleteLoading.value = true
  try {
    await campaignService.deleteCampaign(deleteTargetCampaign.value.id)
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kampanye berhasil dihapus.', life: 3000 })
    deleteDialogVisible.value = false
    await fetchMyCampaigns()
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal menghapus kampanye.', life: 3000 })
  } finally {
    deleteLoading.value = false
  }
}

function openUpdateDialog(campaign) {
  updateCampaignTarget.value = campaign
  updateForm.title = ''
  updateForm.content = ''
  updateDialogVisible.value = true
}

async function handlePostUpdate() {
  if (!updateForm.title.trim() || !updateForm.content.trim()) {
    toast.add({ severity: 'warn', summary: 'Lengkapi Form', detail: 'Judul dan konten update wajib diisi.', life: 2000 })
    return
  }
  updateLoading.value = true
  try {
    await campaignService.postUpdate(updateCampaignTarget.value.id, {
      title: updateForm.title.trim(),
      content: updateForm.content.trim(),
    })
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Update kampanye berhasil diposting.', life: 3000 })
    updateDialogVisible.value = false
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal posting update.', life: 3000 })
  } finally {
    updateLoading.value = false
  }
}

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

// My Backings
const myBackings = ref([])
const myBackingsLoading = ref(false)

function handleEditCampaign(data) {
  router.push({ name: 'CampaignEdit', params: { slug: data.slug } })
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

function formatShortDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
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

async function handleTableSubmit(data) {
  try {
    await campaignService.submitForReview(data.id)
    data.status = 'review'
    toast.add({ severity: 'success', summary: 'Dikirim ke Review', detail: 'Kampanye dikirim ke admin.', life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 4000 })
  }
}

async function fetchMyBackings() {
  myBackingsLoading.value = true
  try {
    const res = await campaignService.getMyBackings()
    myBackings.value = res?.data?.data || []
  } catch (e) {
    myBackings.value = []
  } finally {
    myBackingsLoading.value = false
  }
}

async function fetchCreatorStats() {
  try {
    const res = await dashboardService.getCreatorStats()
    if (res.data?.success) {
      Object.assign(creatorStats, res.data.data)
    }
  } catch (e) { /* ignore */ }
}

async function fetchFundingChart() {
  try {
    const res = await dashboardService.getFundingChart()
    if (res.data?.success) {
      chartData.value = res.data.data || []
    }
  } catch (e) { /* ignore */ }
}

async function fetchBackerStats() {
  try {
    const res = await dashboardService.getBackerStats()
    if (res.data?.success) {
      Object.assign(backerStats, res.data.data)
    }
  } catch (e) { /* ignore */ }
}

// Watch tab changes to fetch backings when needed
watch(activeTab, (tab) => {
  if (tab === 'backings' && myBackings.value.length === 0) {
    fetchMyBackings()
  }
})

onMounted(async () => {
  const promises = [
    fetchMyCampaigns(),
  ]

  if (isCreator.value) {
    promises.push(fetchCreatorStats(), fetchFundingChart())
  } else {
    promises.push(fetchBackerStats())
  }

  await Promise.all(promises)
})


</script>
