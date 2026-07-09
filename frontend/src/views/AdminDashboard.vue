<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 hidden lg:flex flex-col">
      <div class="p-5 border-b border-gray-100">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-shield text-emerald-700 text-lg"></i>
          </div>
          <div>
            <h2 class="text-base font-bold text-gray-900">Panel Admin</h2>
            <p class="text-[10px] text-gray-400">Manajemen platform</p>
          </div>
        </div>
      </div>

      <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <button
          v-for="item in sidebarItems"
          :key="item.id"
          @click="activeSection = item.id"
          class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
          :class="activeSection === item.id
            ? 'bg-emerald-50 text-emerald-700 shadow-sm'
            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800'"
        >
          <div
            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-200"
            :class="activeSection === item.id ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
          >
            <i :class="item.icon" class="text-sm"></i>
          </div>
          <div class="flex-1 text-left">
            <p class="text-sm font-medium">{{ item.label }}</p>
            <p class="text-[10px] text-gray-400">{{ item.desc }}</p>
          </div>
        </button>
      </nav>

      <div class="p-4 border-t border-gray-100">
        <router-link
          :to="{ name: 'Dashboard' }"
          class="flex items-center gap-2 text-xs text-gray-400 hover:text-emerald-600 transition-colors no-underline"
        >
          <i class="pi pi-arrow-left text-[10px]"></i>
          Kembali ke Dashboard
        </router-link>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 py-8 px-4 sm:px-6 lg:px-8">
      <!-- Mobile Section Selector -->
      <div class="lg:hidden mb-6">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-shield text-emerald-700 text-lg"></i>
          </div>
          <div>
            <h1 class="text-lg font-bold text-gray-900">Panel Admin</h1>
            <p class="text-xs text-gray-400">Manajemen platform</p>
          </div>
        </div>
        <div class="flex gap-2 overflow-x-auto flex-nowrap pb-2">
          <button
            v-for="item in sidebarItems"
            :key="item.id"
            @click="activeSection = item.id"
            class="flex-shrink-0 flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-medium transition-all border"
            :class="activeSection === item.id
              ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
              : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
          >
            <i :class="item.icon" class="text-[10px]"></i>
            {{ item.label }}
          </button>
        </div>
      </div>

      <!-- ==================== SECTION: OVERVIEW ==================== -->
      <div v-if="activeSection === 'overview'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Overview Platform</h1>
          <p class="text-sm text-gray-400 mt-1">Ringkasan statistik dan seluruh kampanye di platform</p>
        </div>

        <!-- Executive Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
          <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-4 md:p-6 text-white shadow-sm">
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

          <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-4 md:p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <p class="text-blue-100 text-sm font-medium">Total Dana Escrow</p>
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="pi pi-wallet text-lg"></i>
              </div>
            </div>
            <p class="text-3xl font-bold">{{ formatCurrency(overview.finance?.total_collected || 0) }}</p>                <p class="text-xs text-blue-100 mt-3">Fee Platform: {{ formatCurrency(overview.finance?.total_platform_fee || 0) }}</p>
          </div>

          <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-4 md:p-6 text-white shadow-sm">
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

          <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl p-4 md:p-6 text-white shadow-sm">
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

        <!-- All Campaigns Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 md:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-gray-800">Semua Kampanye</h3>
              <p class="text-xs text-gray-400 mt-0.5">Seluruh kampanye yang pernah dibuat di platform</p>
            </div>
            <Badge :value="allCampaigns.length + ' Data'" severity="info" class="!bg-slate-100 !text-slate-600 !rounded-full !text-xs !font-medium !px-3" />
          </div>
          <DataTable :value="allCampaigns" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" header="ID" :style="{ width: '60px' }" />
            <Column field="title" header="Nama">
              <template #body="{ data }">
                <div class="max-w-[200px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Umum' }}</p>
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
                <Badge :value="statusLabel(data.status)" :severity="statusSeverity(data.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column header="Aksi" :style="{ width: '100px' }">
              <template #body="{ data }">
                <router-link :to="`/campaigns/${data.slug}`" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1">
                  <i class="pi pi-external-link text-[10px]"></i> Lihat
                </router-link>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- ==================== SECTION: APPROVAL CAMPAIGN ==================== -->
      <div v-if="activeSection === 'approval'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Persetujuan Kampanye</h1>
          <p class="text-sm text-gray-400 mt-1">Setujui atau tolak kampanye yang dikirim oleh Creator untuk direview</p>
        </div>

        <div v-if="approvalLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else-if="pendingApprovals.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-check-circle text-4xl text-emerald-300"></i>
          </div>
          <p class="text-gray-500 text-lg font-medium">Tidak ada kampanye menunggu persetujuan</p>
          <p class="text-gray-400 text-sm mt-1">Semua kampanye telah diproses</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="pendingApprovals" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" header="ID" :style="{ width: '60px' }" />
            <Column header="Nama">
              <template #body="{ data }">
                <div class="max-w-[200px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Umum' }}</p>
                </div>
              </template>
            </Column>
            <Column header="Creator">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="pi pi-user text-[8px] text-emerald-600"></i>
                  </div>
                  <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
                </div>
              </template>
            </Column>
            <Column header="Target" :style="{ width: '120px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
              </template>
            </Column>
            <Column header="Tenggat" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ formatDate(data.deadline) }}</span>
              </template>
            </Column>
            <Column header="Tanggal" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ formatDate(data.created_at) }}</span>
              </template>
            </Column>
            <Column header="Aksi" :style="{ width: '140px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <Button icon="pi pi-check" class="!w-8 !h-8 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !shadow-sm !text-white !text-xs" v-tooltip.left="'Setujui'" @click="approveCampaign(data)" />
                  <Button icon="pi pi-times" class="!w-8 !h-8 !rounded-full !bg-slate-400 !border-slate-400 hover:!bg-slate-500 !shadow-sm !text-white !text-xs" v-tooltip.left="'Tolak'" @click="openCampaignRejectDialog(data)" />
                  <Button icon="pi pi-ban" class="!w-8 !h-8 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !shadow-sm !text-white !text-xs" v-tooltip.left="'Ban'" @click="confirmBanCampaign(data)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- ==================== SECTION: CREATOR REQUESTS ==================== -->
      <div v-if="activeSection === 'creatorRequests'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Creator Requests</h1>
          <p class="text-sm text-gray-400 mt-1">Setujui atau tolak permintaan user untuk upgrade menjadi Creator</p>
        </div>

        <div v-if="creatorRequestsLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else-if="pendingCreatorRequests.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-shield text-4xl text-emerald-300"></i>
          </div>
          <p class="text-gray-500 text-lg font-medium">Tidak ada permintaan creator baru</p>
          <p class="text-gray-400 text-sm mt-1">Semua permintaan telah diproses</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="pendingCreatorRequests" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" header="ID" :style="{ width: '60px' }" />
            <Column header="User">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <i class="pi pi-user text-[10px] text-purple-600"></i>
                  </div>
                  <div>
                    <p class="font-medium text-gray-800 text-sm">{{ data.user?.name || 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ data.user?.email || '' }}</p>
                  </div>
                </div>
              </template>
            </Column>
            <Column field="user.role" header="Role Saat Ini" :style="{ width: '120px' }">
              <template #body="{ data }">
                <Badge :value="formatRole(data.user?.role)" severity="info" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column field="reason" header="Alasan">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.reason || '-' }}</span>
              </template>
            </Column>
            <Column header="Tanggal" :style="{ width: '120px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                  <i class="pi pi-calendar"></i>
                  {{ formatDate(data.created_at) }}
                </div>
              </template>
            </Column>
            <Column header="Aksi" :style="{ width: '140px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <Button icon="pi pi-check" class="!w-8 !h-8 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !shadow-sm !text-white !text-xs" v-tooltip.left="'Setujui'" @click="approveCreatorRequest(data)" />
                  <Button icon="pi pi-times" class="!w-8 !h-8 !rounded-full !bg-red-400 !border-red-400 hover:!bg-red-500 !shadow-sm !text-white !text-xs" v-tooltip.left="'Tolak'" @click="openRejectCreatorDialog(data)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- ==================== SECTION: ACTIVE CAMPAIGNS ==================== -->
      <div v-if="activeSection === 'active'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Kampanye Aktif</h1>
          <p class="text-sm text-gray-400 mt-1">Pantau kampanye yang sedang berjalan di platform</p>
        </div>

        <!-- Active Campaigns Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 md:p-5">
            <p class="text-xs text-gray-500 font-medium">Total Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ activeCampaigns.length }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium">Total Terkumpul</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ formatCurrency(activeTotalCollected) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 md:p-5">
            <p class="text-xs text-gray-500 font-medium">Total Backer</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ activeTotalBackers }}</p>
          </div>
        </div>

        <div v-if="activeLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else-if="activeCampaigns.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-flag text-4xl text-gray-300"></i>
          </div>
          <p class="text-gray-500 text-lg font-medium">Belum ada kampanye aktif</p>
          <p class="text-gray-400 text-sm mt-1">Kampanye yang disetujui akan muncul di sini</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div v-for="campaign in activeCampaigns" :key="campaign.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-4 md:p-5">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <h3 class="font-bold text-gray-800 line-clamp-1">{{ campaign.title }}</h3>
                  <p class="text-xs text-gray-400 mt-0.5">Oleh {{ campaign.user?.name || 'N/A' }}</p>
                </div>
                <Badge :value="statusLabel(campaign.status)" :severity="statusSeverity(campaign.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5 flex-shrink-0" />
              </div>

              <div class="space-y-2 mb-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">Terkumpul</span>
                  <span class="font-bold text-emerald-700">{{ formatCurrency(campaign.collected_amount) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">Target</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount) }}</span>
                </div>
              </div>

              <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="progressBarColor(campaign.progress || 0)"
                    :style="{ width: (campaign.progress || 0) + '%' }"
                  ></div>
                </div>
              <div class="flex items-center justify-between text-xs mt-1.5">
                <span class="text-gray-500">{{ campaign.progress }}% terkumpul</span>
                <span class="flex items-center gap-1 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ campaign.days_remaining }} hari lagi
                </span>
              </div>

              <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <span class="flex items-center gap-1"><i class="pi pi-users"></i> {{ campaign.backer_count }} Backer</span>
                  <span class="flex items-center gap-1"><i class="pi pi-calendar"></i> Tenggat {{ formatDate(campaign.deadline) }}</span>
                </div>                  <Button icon="pi pi-ban" class="!w-7 !h-7 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !shadow-sm !text-white !text-[10px]" v-tooltip.left="'Ban'" @click="confirmBanCampaign(campaign)" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== SECTION: ENDED CAMPAIGNS ==================== -->
      <div v-if="activeSection === 'ended'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Kampanye Berakhir</h1>
          <p class="text-sm text-gray-400 mt-1">Riwayat kampanye yang telah selesai, baik sukses maupun gagal</p>
        </div>

        <!-- Ended Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl p-4 md:p-5 text-white shadow-sm">
            <p class="text-emerald-100 text-xs font-medium">Sukses</p>
            <p class="text-2xl font-bold mt-1">{{ endedSuccessCount }}</p>
            <p class="text-xs text-emerald-100 mt-1">Mencapai target</p>
          </div>
          <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl p-4 md:p-5 text-white shadow-sm">
            <p class="text-red-100 text-xs font-medium">Gagal</p>
            <p class="text-2xl font-bold mt-1">{{ endedFailedCount }}</p>
            <p class="text-xs text-red-100 mt-1">Tidak mencapai target</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 md:p-5">
            <p class="text-xs text-gray-500 font-medium">Total Terkumpul</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(endedTotalCollected) }}</p>
          </div>
        </div>

        <div v-if="endedLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else-if="endedCampaigns.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-flag text-4xl text-gray-300"></i>
          </div>
          <p class="text-gray-500 text-lg font-medium">Belum ada kampanye berakhir</p>
          <p class="text-gray-400 text-sm mt-1">Kampanye yang sukses atau gagal akan muncul di sini</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="endedCampaigns" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" header="ID" :style="{ width: '60px' }" />
            <Column header="Nama">
              <template #body="{ data }">
                <div class="max-w-[180px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ data.category?.name || 'Umum' }}</p>
                </div>
              </template>
            </Column>
            <Column header="Creator">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
              </template>
            </Column>
            <Column header="Target" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
              </template>
            </Column>
            <Column header="Terkumpul" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="font-semibold" :class="data.status === 'success' ? 'text-emerald-700' : 'text-red-600'">{{ formatCurrency(data.collected_amount) }}</span>
              </template>
            </Column>
            <Column header="Progress" :style="{ width: '100px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden flex-1">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :class="progressBarColor(data.progress || 0)"
                      :style="{ width: (data.progress || 0) + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-gray-500 w-8 text-right">{{ data.progress }}%</span>
                </div>
              </template>
            </Column>
            <Column header="Backer" :style="{ width: '70px' }">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.backer_count }}</span>
              </template>
            </Column>
            <Column field="status" header="Status" :style="{ width: '90px' }">
              <template #body="{ data }">
                <Badge :value="statusLabel(data.status)" :severity="statusSeverity(data.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column header="Kesimpulan" :style="{ width: '160px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <i :class="data.status === 'success' ? 'pi pi-check-circle text-emerald-500' : 'pi pi-times-circle text-red-400'" class="text-xs"></i>
                  <span class="text-xs" :class="data.status === 'success' ? 'text-emerald-700' : 'text-red-600'">{{ data.conclusion }}</span>
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>
    </div>

    <!-- ==================== SECTION: USERS ==================== -->
      <div v-if="activeSection === 'users'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Manajemen User</h1>
          <p class="text-sm text-gray-400 mt-1">Kelola seluruh akun pengguna di platform</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-center">
          <Dropdown
            v-model="userFilterRole"
            :options="userRoleOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Semua Role"
            class="!w-40 !rounded-xl !text-sm !bg-white"
            @change="fetchUsers"
          />
          <InputText
            v-model="userFilterSearch"
            placeholder="Cari nama atau email..."
            class="!w-60 !rounded-xl !text-sm"
            @input="onAdminSearchInput"
          />
        </div>

        <div v-if="usersLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="usersData" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10">
            <Column field="id" header="ID" :style="{ width: '60px' }" />
            <Column header="Nama">
              <template #body="{ data }">
                <div>
                  <p class="font-medium text-gray-800">{{ data.name }}</p>
                  <p class="text-xs text-gray-400">{{ data.email }}</p>
                </div>
              </template>
            </Column>
            <Column header="Role" :style="{ width: '100px' }">
              <template #body="{ data }">
                <Badge :value="formatRole(data.role)" :severity="data.role === 'admin' ? 'danger' : data.role === 'creator' ? 'success' : 'info'" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column header="Saldo" :style="{ width: '120px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.balance) }}</span>
              </template>
            </Column>
            <Column header="Kampanye" :style="{ width: '80px' }">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.campaigns_count }}</span>
              </template>
            </Column>
            <Column header="Backing" :style="{ width: '80px' }">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.backings_count }}</span>
              </template>
            </Column>
            <Column header="Status" :style="{ width: '100px' }">
              <template #body="{ data }">
                <Badge :value="data.suspended_at ? 'Dinonaktifkan' : 'Aktif'" :severity="data.suspended_at ? 'danger' : 'success'" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column header="Aksi" :style="{ width: '160px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <Button
                    :icon="data.suspended_at ? 'pi pi-check-circle' : 'pi pi-ban'"
                    :class="data.suspended_at ? '!w-7 !h-7 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !text-white !text-[10px]' : '!w-7 !h-7 !rounded-full !bg-orange-500 !border-orange-500 hover:!bg-orange-600 !text-white !text-[10px]'"
                    @click="toggleUserStatus(data)"
                  />
                  <Button
                    icon="pi pi-eye"
                    class="!w-7 !h-7 !rounded-full !bg-sky-500 !border-sky-500 hover:!bg-sky-600 !text-white !text-[10px]"
                    v-tooltip="'Lihat Detail'"
                    @click="openUserDetail(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </div>

        <Dialog v-model:visible="userDetailVisible" header="Detail User" :modal="true" class="app-dialog !rounded-[15px] !bg-white !max-w-full !w-[95vw] sm:!w-auto" :style="{ maxWidth: '600px' }" @show="onDialogShow">
          <div v-if="userDetailData" class="space-y-4">
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
              <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="pi pi-user text-emerald-700"></i>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-800">{{ userDetailData.user.name }}</p>
                <p class="text-xs text-gray-400">{{ userDetailData.user.email }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div class="p-3 bg-slate-50 rounded-xl">
                <p class="text-xs text-gray-400">Role</p>
                <p class="text-sm font-semibold">{{ formatRole(userDetailData.user.role) }}</p>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl">
                <p class="text-xs text-gray-400">Saldo</p>
                <p class="text-sm font-semibold">{{ formatCurrency(userDetailData.user.balance) }}</p>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl">
                <p class="text-xs text-gray-400">Kampanye Dibuat</p>
                <p class="text-sm font-semibold">{{ userDetailData.user.campaigns_count }}</p>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl">
                <p class="text-xs text-gray-400">Backing Dilakukan</p>
                <p class="text-sm font-semibold">{{ userDetailData.user.backings_count }}</p>
              </div>
            </div>
            <Divider />
            <h4 class="text-sm font-bold text-gray-700">Riwayat Transaksi Wallet</h4>
            <div class="max-h-48 overflow-y-auto space-y-2">
              <div v-if="!userDetailData.wallet_transactions?.length" class="text-center py-4 text-xs text-gray-400">Tidak ada transaksi</div>
              <div v-for="tx in userDetailData.wallet_transactions" :key="tx.id" class="flex items-center justify-between p-2 bg-slate-50 rounded-lg text-xs">
                <div>
                  <span class="font-medium text-gray-700">{{ tx.type }}</span>
                  <p class="text-gray-400">{{ tx.description || '-' }}</p>
                </div>
                <span class="font-semibold" :class="tx.type === 'top_up' || tx.type === 'refund' ? 'text-emerald-600' : 'text-gray-800'">{{ tx.type === 'top_up' || tx.type === 'refund' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}</span>
              </div>
            </div>
          </div>
        </Dialog>
      </div>

    <!-- ==================== DIALOGS ==================== -->

    <!-- Reject Campaign Dialog -->      <Dialog v-model:visible="campaignRejectDialogVisible" header="Tolak Kampanye" :modal="true" class="app-dialog !rounded-[15px] !bg-white !border-2 !border-emerald-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto" :style="{ maxWidth: '450px' }" @show="onDialogShow">
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
          <Textarea v-model="campaignRejectReason" rows="3" placeholder="Masukkan alasan mengapa kampanye ditolak..." class="w-full !rounded-xl !text-sm" :class="{ 'p-invalid': !campaignRejectReason && campaignSubmitted }" />
          <small v-if="!campaignRejectReason && campaignSubmitted" class="text-red-500 text-xs flex items-center gap-1"><i class="pi pi-exclamation-circle"></i> Alasan penolakan wajib diisi</small>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="campaignRejectDialogVisible = false" />
          <Button label="Tolak" icon="pi pi-check" class="!bg-slate-600 !border-slate-600 !text-white !rounded-xl" :loading="campaignRejectLoading" @click="executeCampaignReject" />
        </div>
      </template>
    </Dialog>

    <!-- Reject Creator Request Dialog -->
    <Dialog v-model:visible="rejectCreatorDialogVisible" header="Tolak Creator Request" :modal="true" class="app-dialog !rounded-[15px] !bg-white !border-2 !border-emerald-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto" :style="{ maxWidth: '450px' }" @show="onDialogShow">
      <div class="space-y-4">
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-user text-sm text-purple-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ rejectCreatorTarget?.user?.name || 'N/A' }}</p>
            <p class="text-xs text-gray-400">{{ rejectCreatorTarget?.user?.email || '' }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
          <Textarea v-model="rejectCreatorReason" rows="3" placeholder="Masukkan alasan mengapa permintaan ditolak..." class="w-full !rounded-xl !text-sm" :class="{ 'p-invalid': !rejectCreatorReason && rejectCreatorSubmitted }" />
          <small v-if="!rejectCreatorReason && rejectCreatorSubmitted" class="text-red-500 text-xs flex items-center gap-1"><i class="pi pi-exclamation-circle"></i> Alasan penolakan wajib diisi</small>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="rejectCreatorDialogVisible = false" />
          <Button label="Tolak" icon="pi pi-check" class="!bg-red-400 !border-red-400 !text-white !rounded-xl" :loading="rejectCreatorLoading" @click="executeRejectCreator" />
        </div>
      </template>
    </Dialog>

    <!-- Ban Campaign Confirmation Dialog -->      <Dialog v-model:visible="banDialogVisible" header="Ban Kampanye" :modal="true" class="app-dialog app-dialog-danger !rounded-[15px] !bg-white !border-2 !border-red-400 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto" :style="{ maxWidth: '450px' }" @show="onDialogShow">
      <div class="space-y-4">
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
          <i class="pi pi-exclamation-triangle text-red-500 text-xl mt-0.5"></i>
          <div>
            <p class="text-sm font-semibold text-red-800">Tindakan ini tidak dapat dibatalkan</p>
            <p class="text-xs text-red-600 mt-1">Memban kampanye akan mengubah status menjadi gagal dan menghentikan semua pendanaan yang masuk.</p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-flag text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ banTarget?.title }}</p>
            <p class="text-xs text-gray-400">Status saat ini: {{ banTarget?.status }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">Alasan Ban</label>
          <Textarea v-model="banReason" rows="2" placeholder="Catatan internal untuk ban..." class="w-full !rounded-xl !text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button label="Batal" icon="pi pi-times" class="p-button-text" @click="banDialogVisible = false" />
          <Button label="Ban" icon="pi pi-ban" class="!bg-red-600 !border-red-600 !text-white !rounded-xl" :loading="banLoading" @click="executeBan" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useGenieEffect } from '@/composables/useGenieEffect'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

import Dropdown from 'primevue/dropdown'
import InputText from 'primevue/inputtext'
import Divider from 'primevue/divider'
import { useToast } from 'primevue/usetoast'
import api from '@/services/api'
import roleService from '@/services/roleService'

const toast = useToast()
const { onDialogShow } = useGenieEffect()
const activeSection = ref('overview')

const sidebarItems = [
  { id: 'overview', label: 'Overview', desc: 'Statistik + Grafik', icon: 'pi pi-chart-bar' },
  { id: 'approval', label: 'Approval Campaign', desc: 'Persetujuan kampanye', icon: 'pi pi-check-circle' },
  { id: 'creatorRequests', label: 'Creator Requests', desc: 'Persetujuan creator', icon: 'pi pi-shield' },
  { id: 'active', label: 'Kampanye Aktif', desc: 'Kampanye berjalan', icon: 'pi pi-play-circle' },
  { id: 'ended', label: 'Kampanye Berakhir', desc: 'Riwayat kampanye', icon: 'pi pi-flag' },
  { id: 'users', label: 'Manajemen User', desc: 'Kelola akun pengguna', icon: 'pi pi-users' },
  { id: 'activity-logs', label: 'Activity Logs', desc: 'Log aktivitas admin', icon: 'pi pi-book' },
]

// ==================== Section 1: Overview ====================
const allCampaigns = ref([])
const overview = ref({
  campaigns: { total: 0, active: 0, success: 0, failed: 0 },
  finance: { total_collected: 0, total_platform_fee: 0 },
  users: { total: 0, creators: 0, backers: 0 },
  backings: { total: 0, total_amount: 0 },
})

// ==================== Section 2: Approval Campaign ====================
const pendingApprovals = ref([])
const approvalLoading = ref(true)

// ==================== Section 2b: Creator Requests ====================
const pendingCreatorRequests = ref([])
const creatorRequestsLoading = ref(true)

// ==================== Section 6: User Management ====================
const usersData = ref([])
const usersLoading = ref(false)
const userFilterRole = ref(null)
const userFilterSearch = ref('')
const userRoleOptions = [
  { label: 'Semua Role', value: null },
  { label: 'Admin', value: 'admin' },
  { label: 'Creator', value: 'creator' },
  { label: 'Backer', value: 'backer' },
]
const userDetailVisible = ref(false)
const userDetailData = ref(null)
let adminSearchTimeout = null

function onAdminSearchInput() {
  if (adminSearchTimeout) clearTimeout(adminSearchTimeout)
  adminSearchTimeout = setTimeout(() => {
    fetchUsers()
  }, 400)
}

async function fetchUsers() {
  usersLoading.value = true
  try {
    const params = {}
    if (userFilterRole.value) params.role = userFilterRole.value
    if (userFilterSearch.value.trim()) params.search = userFilterSearch.value.trim()
    const res = await api.get('/admin/users', { params })
    usersData.value = res.data?.data?.data || []
  } catch (e) {
    usersData.value = []
  } finally {
    usersLoading.value = false
  }
}

async function toggleUserStatus(user) {
  try {
    const res = await api.post(`/admin/users/${user.id}/toggle-status`)
    user.suspended_at = res.data?.data?.suspended_at || null
    toast.add({ severity: 'success', summary: 'Berhasil', detail: res.data?.message, life: 2000 })
  } catch (e) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: e.response?.data?.message || 'Terjadi kesalahan', life: 3000 })
  }
}

async function openUserDetail(user) {
  userDetailData.value = null
  userDetailVisible.value = true
  try {
    const res = await api.get(`/admin/users/${user.id}`)
    userDetailData.value = res.data?.data
  } catch (e) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat detail user', life: 3000 })
  }
}

function formatRole(role) {
  const labels = { admin: 'Admin', creator: 'Creator', backer: 'Backer', guest: 'Guest' }
  return labels[role] || role || '-'
}

// Reject Creator Request Dialog
const rejectCreatorDialogVisible = ref(false)
const rejectCreatorTarget = ref(null)
const rejectCreatorReason = ref('')
const rejectCreatorLoading = ref(false)
const rejectCreatorSubmitted = ref(false)

function openRejectCreatorDialog(data) {
  rejectCreatorTarget.value = data
  rejectCreatorReason.value = ''
  rejectCreatorSubmitted.value = false
  rejectCreatorDialogVisible.value = true
}

async function executeRejectCreator() {
  rejectCreatorSubmitted.value = true
  if (!rejectCreatorReason.value.trim()) return
  rejectCreatorLoading.value = true
  try {
    const data = rejectCreatorTarget.value
    await roleService.updateRequestStatusAdmin(data.id, 'rejected', rejectCreatorReason.value)
    pendingCreatorRequests.value = pendingCreatorRequests.value.filter(r => r.id !== data.id)
    rejectCreatorDialogVisible.value = false
    toast.add({ severity: 'info', summary: 'Ditolak', detail: 'Permintaan creator ditolak.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    rejectCreatorLoading.value = false
  }
}

async function approveCreatorRequest(data) {
  try {
    await roleService.updateRequestStatusAdmin(data.id, 'approved')
    data.user.role = 'creator'
    pendingCreatorRequests.value = pendingCreatorRequests.value.filter(r => r.id !== data.id)
    toast.add({ severity: 'success', summary: 'Disetujui', detail: `${data.user?.name} kini menjadi Creator!`, life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  }
}

// ==================== Section 3: Active Campaigns ====================
const activeCampaigns = ref([])
const activeLoading = ref(true)

const activeTotalCollected = computed(() => {
  return activeCampaigns.value.reduce((sum, c) => sum + Number(c.collected_amount), 0)
})

const activeTotalBackers = computed(() => {
  return activeCampaigns.value.reduce((sum, c) => sum + (c.backer_count || 0), 0)
})

// ==================== Section 4: Ended Campaigns ====================
const endedCampaigns = ref([])
const endedLoading = ref(true)

const endedSuccessCount = computed(() => endedCampaigns.value.filter(c => c.status === 'success').length)
const endedFailedCount = computed(() => endedCampaigns.value.filter(c => c.status === 'failed').length)
const endedTotalCollected = computed(() => endedCampaigns.value.reduce((sum, c) => sum + Number(c.collected_amount), 0))

// ==================== Reject Campaign Dialog ====================
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
    pendingApprovals.value = pendingApprovals.value.filter(c => c.id !== data.id)
    allCampaigns.value = allCampaigns.value.map(c => c.id === data.id ? { ...c, status: 'draft' } : c)
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
    pendingApprovals.value = pendingApprovals.value.filter(c => c.id !== data.id)
    allCampaigns.value = allCampaigns.value.map(c => c.id === data.id ? { ...c, status: 'active' } : c)
    toast.add({ severity: 'success', summary: 'Disetujui', detail: 'Kampanye kini aktif.', life: 3000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  }
}

// ==================== Ban Campaign Dialog ====================
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
    pendingApprovals.value = pendingApprovals.value.filter(c => c.id !== data.id)
    allCampaigns.value = allCampaigns.value.map(c => c.id === data.id ? { ...c, status: 'failed' } : c)
    banDialogVisible.value = false
    toast.add({ severity: 'warn', summary: 'Kampanye Dibanned', detail: `"${data.title}" telah di-ban.`, life: 4000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Terjadi kesalahan.', life: 3000 })
  } finally {
    banLoading.value = false
  }
}
function statusLabel(status) {
  const labels = { draft: 'Draft', review: 'Review', active: 'Aktif', success: 'Sukses', failed: 'Gagal' }
  return labels[status] || status || '-'
}

function statusSeverity(status) {
  const severities = { draft: 'info', review: 'warn', active: 'success', success: 'success', failed: 'danger' }
  return severities[status] || 'info'
}

function progressBarColor(pct) {
  if (pct >= 100) return 'bg-emerald-700'
  if (pct >= 75) return 'bg-emerald-600'
  if (pct >= 50) return 'bg-emerald-500'
  if (pct >= 25) return 'bg-emerald-400'
  return 'bg-emerald-300'
}

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

// ==================== Lifecycle ====================
onMounted(async () => {
  try {
    const [overviewRes, allCampaignsRes, approvalsRes, activeRes, endedRes, creatorRequestsRes] = await Promise.all([
      api.get('/admin/overview').catch(() => ({ data: { data: overview.value } })),
      api.get('/admin/campaigns/all').catch(() => ({ data: { data: [] } })),
      api.get('/admin/pending-approvals').catch(() => ({ data: { data: [] } })),
      api.get('/admin/campaigns/active').catch(() => ({ data: { data: [] } })),
      api.get('/admin/campaigns/ended').catch(() => ({ data: { data: [] } })),
      roleService.getPendingRequestsAdmin().catch(() => ({ data: { data: [] } })),
    ])
    overview.value = overviewRes.data?.data || overview.value
    allCampaigns.value = allCampaignsRes.data?.data || []
    pendingApprovals.value = approvalsRes.data?.data || []
    activeCampaigns.value = activeRes.data?.data || []
    endedCampaigns.value = endedRes.data?.data || []
    pendingCreatorRequests.value = creatorRequestsRes.data?.data || []
  } catch (e) {
    allCampaigns.value = []
    activeCampaigns.value = []
    endedCampaigns.value = []
  } finally {
    approvalLoading.value = false
    activeLoading.value = false
    endedLoading.value = false
    creatorRequestsLoading.value = false
  }

  fetchUsers()
})

</script>