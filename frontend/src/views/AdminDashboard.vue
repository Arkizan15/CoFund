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
            <h2 class="text-base font-bold text-gray-900">{{ $t('admin.panelTitle') }}</h2>
            <p class="text-[10px] text-gray-400">{{ $t('admin.panelSub') }}</p>
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
          {{ $t('nav.backToDashboard') }}
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
            <h1 class="text-lg font-bold text-gray-900">{{ $t('admin.panelTitle') }}</h1>
            <p class="text-xs text-gray-400">{{ $t('admin.panelSub') }}</p>
          </div>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-2">
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
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.overviewTitle') }}</h1>
          <p class="text-sm text-gray-400 mt-1">{{ $t('admin.overviewSub') }}</p>
        </div>

        <!-- Executive Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
          <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <p class="text-emerald-100 text-sm font-medium">{{ $t('admin.totalCampaigns') }}</p>
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="pi pi-flag text-lg"></i>
              </div>
            </div>
            <p class="text-3xl font-bold">{{ overview.campaigns?.total || 0 }}</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-xs text-emerald-100">
              <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>{{ $t('admin.active') }}: {{ overview.campaigns?.active || 0 }}</span>
              <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>{{ $t('admin.success') }}: {{ overview.campaigns?.success || 0 }}</span>
              <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>{{ $t('admin.failed') }}: {{ overview.campaigns?.failed || 0 }}</span>
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <p class="text-blue-100 text-sm font-medium">{{ $t('admin.totalEscrow') }}</p>
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="pi pi-wallet text-lg"></i>
              </div>
            </div>
            <p class="text-3xl font-bold">{{ formatCurrency(overview.finance?.total_collected || 0) }}</p>                <p class="text-xs text-blue-100 mt-3">{{ $t('admin.platformFee') }}: {{ formatCurrency(overview.finance?.total_platform_fee || 0) }}</p>
          </div>

          <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <p class="text-purple-100 text-sm font-medium">{{ $t('admin.totalUsers') }}</p>
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="pi pi-users text-lg"></i>
              </div>
            </div>
            <p class="text-3xl font-bold">{{ overview.users?.total || 0 }}</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-xs text-purple-100">
              <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-300"></span>{{ $t('admin.creator') }}: {{ overview.users?.creators || 0 }}</span>
              <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-300"></span>{{ $t('admin.backer') }}: {{ overview.users?.backers || 0 }}</span>
            </div>
          </div>

          <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl p-6 text-white shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <p class="text-orange-100 text-sm font-medium">{{ $t('admin.totalBackings') }}</p>
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="pi pi-heart text-lg"></i>
              </div>
            </div>
            <p class="text-3xl font-bold">{{ overview.backings?.total || 0 }}</p>
            <p class="text-xs text-orange-100 mt-3">{{ $t('admin.value') }}: {{ formatCurrency(overview.backings?.total_amount || 0) }}</p>
          </div>
        </div>

        <!-- All Campaigns Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-gray-800">{{ $t('admin.allCampaigns') }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ $t('admin.allCampaignsSub') }}</p>
            </div>
            <Badge :value="$t('admin.dataCount', { count: allCampaigns.length })" severity="info" class="!bg-slate-100 !text-slate-600 !rounded-full !text-xs !font-medium !px-3" />
          </div>
          <DataTable :value="allCampaigns" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" :header="$t('admin.id')" :style="{ width: '60px' }" />
            <Column field="title" :header="$t('admin.name')">
              <template #body="{ data }">
                <div class="max-w-[200px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ $t('categories.' + (data.category?.slug || 'general')) }}</p>
                </div>
              </template>
            </Column>
            <Column :header="$t('admin.creator')">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.target')" :style="{ width: '120px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.collected')" :style="{ width: '120px' }">
              <template #body="{ data }">
                <span class="font-semibold text-emerald-700 text-sm">{{ formatCurrency(data.collected_amount || 0) }}</span>
              </template>
            </Column>
            <Column field="status" :header="$t('admin.status')" :style="{ width: '100px' }">
              <template #body="{ data }">
                <Badge :value="statusLabel(data.status)" :severity="statusSeverity(data.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column :header="$t('admin.actions')" :style="{ width: '100px' }">
              <template #body="{ data }">
                <router-link :to="`/campaigns/${data.slug}`" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium no-underline flex items-center gap-1">
                  <i class="pi pi-external-link text-[10px]"></i> {{ $t('admin.view') }}
                </router-link>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- ==================== SECTION: APPROVAL CAMPAIGN ==================== -->
      <div v-if="activeSection === 'approval'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.approvalTitle') }}</h1>
          <p class="text-sm text-gray-400 mt-1">{{ $t('admin.approvalSub') }}</p>
        </div>

        <div v-if="approvalLoading" class="flex items-center justify-center py-20">
          <i class="pi pi-spin pi-spinner text-3xl text-emerald-600"></i>
        </div>

        <div v-else-if="pendingApprovals.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-100 shadow-sm">
          <div class="w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-check-circle text-4xl text-emerald-300"></i>
          </div>
          <p class="text-gray-500 text-lg font-medium">{{ $t('admin.noApprovals') }}</p>
          <p class="text-gray-400 text-sm mt-1">{{ $t('admin.noApprovalsSub') }}</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="pendingApprovals" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" :header="$t('admin.id')" :style="{ width: '60px' }" />
            <Column :header="$t('admin.name')">
              <template #body="{ data }">
                <div class="max-w-[200px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ $t('categories.' + (data.category?.slug || 'general')) }}</p>
                </div>
              </template>
            </Column>
            <Column :header="$t('admin.creator')">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="pi pi-user text-[8px] text-emerald-600"></i>
                  </div>
                  <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
                </div>
              </template>
            </Column>
            <Column :header="$t('admin.target')" :style="{ width: '120px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.deadline')" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ formatDate(data.deadline) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.date')" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="text-xs text-gray-500">{{ formatDate(data.created_at) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.actions')" :style="{ width: '140px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-1.5">
                  <Button icon="pi pi-check" class="!w-8 !h-8 !rounded-full !bg-emerald-500 !border-emerald-500 hover:!bg-emerald-600 !shadow-sm !text-white !text-xs" v-tooltip.left="$t('admin.approve')" @click="approveCampaign(data)" />
                  <Button icon="pi pi-times" class="!w-8 !h-8 !rounded-full !bg-slate-400 !border-slate-400 hover:!bg-slate-500 !shadow-sm !text-white !text-xs" v-tooltip.left="$t('admin.reject')" @click="openCampaignRejectDialog(data)" />
                  <Button icon="pi pi-ban" class="!w-8 !h-8 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !shadow-sm !text-white !text-xs" v-tooltip.left="$t('admin.ban')" @click="confirmBanCampaign(data)" />
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </div>

      <!-- ==================== SECTION: ACTIVE CAMPAIGNS ==================== -->
      <div v-if="activeSection === 'active'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.activeTitle') }}</h1>
          <p class="text-sm text-gray-400 mt-1">{{ $t('admin.activeSub') }}</p>
        </div>

        <!-- Active Campaigns Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium">{{ $t('admin.totalActive') }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ activeCampaigns.length }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium">{{ $t('admin.totalCollected') }}</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ formatCurrency(activeTotalCollected) }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium">{{ $t('admin.totalBackers') }}</p>
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
          <p class="text-gray-500 text-lg font-medium">{{ $t('admin.noActive') }}</p>
          <p class="text-gray-400 text-sm mt-1">{{ $t('admin.noActiveSub') }}</p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div v-for="campaign in activeCampaigns" :key="campaign.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-5">
              <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                  <h3 class="font-bold text-gray-800 line-clamp-1">{{ campaign.title }}</h3>
                  <p class="text-xs text-gray-400 mt-0.5">Oleh {{ campaign.user?.name || 'N/A' }}</p>
                </div>
                <Badge :value="statusLabel(campaign.status)" :severity="statusSeverity(campaign.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5 flex-shrink-0" />
              </div>

              <div class="space-y-2 mb-3">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">{{ $t('campaign.collected') }}</span>
                  <span class="font-bold text-emerald-700">{{ formatCurrency(campaign.collected_amount) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">{{ $t('campaign.target') }}</span>
                  <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount) }}</span>
                </div>
              </div>

              <ProgressBar :value="campaign.progress" class="!h-3 !rounded-full" />
              <div class="flex items-center justify-between text-xs mt-1.5">
                <span class="text-gray-500">{{ $t('campaign.percentCollected', { percent: campaign.progress }) }}</span>
                <span class="flex items-center gap-1 text-orange-600 font-medium">
                  <i class="pi pi-clock"></i>
                  {{ $t('common.daysLeft', { days: campaign.days_remaining }) }}
                </span>
              </div>

              <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <span class="flex items-center gap-1"><i class="pi pi-users"></i> {{ campaign.backer_count }} {{ $t('admin.backer') }}</span>
                  <span class="flex items-center gap-1"><i class="pi pi-calendar"></i> {{ $t('admin.deadline') }} {{ formatDate(campaign.deadline) }}</span>
                </div>                  <Button icon="pi pi-ban" class="!w-7 !h-7 !rounded-full !bg-red-500 !border-red-500 hover:!bg-red-600 !shadow-sm !text-white !text-[10px]" v-tooltip.left="$t('admin.ban')" @click="confirmBanCampaign(campaign)" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== SECTION: ENDED CAMPAIGNS ==================== -->
      <div v-if="activeSection === 'ended'">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('admin.endedTitle') }}</h1>
          <p class="text-sm text-gray-400 mt-1">{{ $t('admin.endedSub') }}</p>
        </div>

        <!-- Ended Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
          <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl p-5 text-white shadow-sm">
            <p class="text-emerald-100 text-xs font-medium">{{ $t('admin.successCount') }}</p>
            <p class="text-2xl font-bold mt-1">{{ endedSuccessCount }}</p>
            <p class="text-xs text-emerald-100 mt-1">{{ $t('admin.endedSuccess') }}</p>
          </div>
          <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl p-5 text-white shadow-sm">
            <p class="text-red-100 text-xs font-medium">{{ $t('admin.failedCount') }}</p>
            <p class="text-2xl font-bold mt-1">{{ endedFailedCount }}</p>
            <p class="text-xs text-red-100 mt-1">{{ $t('admin.endedFailed') }}</p>
          </div>
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium">{{ $t('admin.totalCollected') }}</p>
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
          <p class="text-gray-500 text-lg font-medium">{{ $t('admin.noEnded') }}</p>
          <p class="text-gray-400 text-sm mt-1">{{ $t('admin.noEndedSub') }}</p>
        </div>

        <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <DataTable :value="endedCampaigns" class="!text-sm" stripedRows responsiveLayout="scroll" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20]">
            <Column field="id" :header="$t('admin.id')" :style="{ width: '60px' }" />
            <Column :header="$t('admin.name')">
              <template #body="{ data }">
                <div class="max-w-[180px]">
                  <p class="font-medium text-gray-800 truncate">{{ data.title }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ $t('categories.' + (data.category?.slug || 'general')) }}</p>
                </div>
              </template>
            </Column>
            <Column :header="$t('admin.creator')">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.user?.name || 'N/A' }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.target')" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="font-medium text-gray-800 text-sm">{{ formatCurrency(data.target_amount) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.collected')" :style="{ width: '110px' }">
              <template #body="{ data }">
                <span class="font-semibold" :class="data.status === 'success' ? 'text-emerald-700' : 'text-red-600'">{{ formatCurrency(data.collected_amount) }}</span>
              </template>
            </Column>
            <Column :header="$t('admin.progress')" :style="{ width: '100px' }">
              <template #body="{ data }">
                <div class="flex items-center gap-2">
                  <ProgressBar :value="data.progress" class="!h-2 !rounded-full flex-1" />
                  <span class="text-xs text-gray-500 w-8 text-right">{{ data.progress }}%</span>
                </div>
              </template>
            </Column>
            <Column :header="$t('admin.backer')" :style="{ width: '70px' }">
              <template #body="{ data }">
                <span class="text-sm text-gray-600">{{ data.backer_count }}</span>
              </template>
            </Column>
            <Column field="status" :header="$t('campaign.status')" :style="{ width: '90px' }">
              <template #body="{ data }">
                <Badge :value="statusLabel(data.status)" :severity="statusSeverity(data.status)" class="!rounded-full !text-[10px] !font-semibold !px-2.5 !py-0.5" />
              </template>
            </Column>
            <Column :header="$t('admin.conclusion')" :style="{ width: '160px' }">
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

    <!-- ==================== DIALOGS ==================== -->

    <!-- Reject Campaign Dialog -->      <Dialog v-model:visible="campaignRejectDialogVisible" :header="$t('admin.rejectDialogTitle')" :modal="true" class="!rounded-2xl" :style="{ width: '450px' }">
      <div class="space-y-4">
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-flag text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ campaignRejectTarget?.title }}</p>
            <p class="text-xs text-gray-400">{{ $t('admin.byCreator', { name: campaignRejectTarget?.user?.name || 'N/A' }) }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">{{ $t('admin.rejectReason') }} <span class="text-red-500">*</span></label>
          <Textarea v-model="campaignRejectReason" rows="3" :placeholder="$t('admin.rejectReasonPlaceholder')" class="w-full !rounded-xl !text-sm" :class="{ 'p-invalid': !campaignRejectReason && campaignSubmitted }" />
          <small v-if="!campaignRejectReason && campaignSubmitted" class="text-red-500 text-xs flex items-center gap-1"><i class="pi pi-exclamation-circle"></i> {{ $t('admin.rejectReasonRequired') }}</small>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button :label="$t('common.cancel')" icon="pi pi-times" class="p-button-text" @click="campaignRejectDialogVisible = false" />
          <Button :label="$t('admin.rejectAction')" icon="pi pi-check" class="!bg-slate-600 !border-slate-600 !text-white !rounded-xl" :loading="campaignRejectLoading" @click="executeCampaignReject" />
        </div>
      </template>
    </Dialog>

    <!-- Ban Campaign Confirmation Dialog -->      <Dialog v-model:visible="banDialogVisible" :header="$t('admin.banDialogTitle')" :modal="true" class="!rounded-2xl !bg-white" :style="{ width: '450px' }">
      <div class="space-y-4">
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
          <i class="pi pi-exclamation-triangle text-red-500 text-xl mt-0.5"></i>
          <div>
            <p class="text-sm font-semibold text-red-800">{{ $t('admin.banWarning') }}</p>
            <p class="text-xs text-red-600 mt-1">{{ $t('admin.banWarningSub') }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
          <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
            <i class="pi pi-flag text-sm text-slate-600"></i>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">{{ banTarget?.title }}</p>
            <p class="text-xs text-gray-400">{{ $t('admin.currentStatus', { status: banTarget?.status }) }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-semibold text-gray-700">{{ $t('admin.banReason') }}</label>
          <Textarea v-model="banReason" rows="2" :placeholder="$t('admin.banReasonPlaceholder')" class="w-full !rounded-xl !text-sm" />
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button :label="$t('common.cancel')" icon="pi pi-times" class="p-button-text" @click="banDialogVisible = false" />
          <Button :label="$t('admin.banAction')" icon="pi pi-ban" class="!bg-red-600 !border-red-600 !text-white !rounded-xl" :loading="banLoading" @click="executeBan" />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import ProgressBar from 'primevue/progressbar'
import { useToast } from 'primevue/usetoast'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const toast = useToast()
const activeSection = ref('overview')

const { t } = useI18n()

const sidebarItems = computed(() => [
  { id: 'overview', label: t('admin.overview'), desc: t('admin.overviewDesc'), icon: 'pi pi-chart-bar' },
  { id: 'approval', label: t('admin.approval'), desc: t('admin.approvalDesc'), icon: 'pi pi-check-circle' },
  { id: 'active', label: t('admin.activeCampaigns'), desc: t('admin.activeDesc'), icon: 'pi pi-play-circle' },
  { id: 'ended', label: t('admin.endedCampaigns'), desc: t('admin.endedDesc'), icon: 'pi pi-flag' },
])

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

// ==================== Utility Functions ====================
function statusLabel(status) {
  const labels = { draft: t('campaign.statusDraft'), review: t('campaign.statusReview'), active: t('campaign.statusActive'), success: t('campaign.statusSuccess'), failed: t('campaign.statusFailed') }
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

// ==================== Lifecycle ====================
onMounted(async () => {
  try {
    const [overviewRes, allCampaignsRes, approvalsRes, activeRes, endedRes] = await Promise.all([
      api.get('/admin/overview').catch(() => ({ data: { data: overview.value } })),
      api.get('/admin/campaigns/all').catch(() => ({ data: { data: [] } })),
      api.get('/admin/pending-approvals').catch(() => ({ data: { data: [] } })),
      api.get('/admin/campaigns/active').catch(() => ({ data: { data: [] } })),
      api.get('/admin/campaigns/ended').catch(() => ({ data: { data: [] } })),
    ])
    overview.value = overviewRes.data?.data || overview.value
    allCampaigns.value = allCampaignsRes.data?.data || []
    pendingApprovals.value = approvalsRes.data?.data || []
    activeCampaigns.value = activeRes.data?.data || []
    endedCampaigns.value = endedRes.data?.data || []
  } catch (e) {
    allCampaigns.value = []
    activeCampaigns.value = []
    endedCampaigns.value = []
  } finally {
    approvalLoading.value = false
    activeLoading.value = false
    endedLoading.value = false
  }
})
</script>