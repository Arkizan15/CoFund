<template>
  <div
    class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 flex flex-col"
  >
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-gray-100">
      <img
        v-if="campaign.image_url || campaign.image"
        :src="campaign.image_url || campaign.image"
        :alt="campaign.title"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
        <i class="pi pi-image text-4xl"></i>
      </div>

      <!-- Badges -->
      <div class="absolute top-3 left-3 flex flex-col gap-1.5">
        <span class="bg-white/90 backdrop-blur-sm text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">
          {{ campaign.category?.name || 'Umum' }}
        </span>
        <span
          v-if="campaign.status !== 'active'"
          class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1 rounded-full shadow-sm"
          :class="statusBadgeClass"
        >
          <i class="pi text-[10px]" :class="statusIcon"></i>
          {{ statusLabel }}
        </span>
      </div>

      <!-- Progress overlay on image for completed campaigns -->
      <div
        v-if="progressPercent >= 100"
        class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-emerald-600/80 to-transparent px-3 pb-2 pt-8"
      >
        <span class="text-white text-xs font-semibold flex items-center gap-1">
          <i class="pi pi-check-circle text-[10px]"></i>
          100% Tercapai
        </span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-5 flex flex-col flex-1">
      <router-link
        :to="`/campaigns/${campaign.slug}`"
        class="text-lg font-bold text-gray-800 hover:text-emerald-600 transition-colors mb-2 line-clamp-2 leading-snug no-underline"
      >
        {{ campaign.title }}
      </router-link>

      <!-- Funding Stats -->
      <div class="space-y-2 mb-4">
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500">Terkumpul</span>
          <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.collected_amount || 0) }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500">Target</span>
          <span class="font-semibold text-gray-800">{{ formatCurrency(campaign.target_amount || 0) }}</span>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="space-y-2">
        <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="progressBarColor"
            :style="{ width: progressPercent + '%' }"
          ></div>
        </div>
        <div class="flex items-center justify-between text-xs">
          <span
            class="font-medium"
            :class="progressPercent >= 100 ? 'text-emerald-600' : 'text-gray-500'"
          >
            {{ progressPercent }}% terkumpul
          </span>
          <span v-if="campaign.status === 'active'" class="flex items-center gap-1 text-orange-600 font-medium">
            <i class="pi pi-clock"></i>
            {{ remainingDays }} hari lagi
          </span>
          <span v-else-if="campaign.status === 'success'" class="flex items-center gap-1 text-emerald-600 font-medium">
            <i class="pi pi-check-circle"></i>
            Berhasil
          </span>
          <span v-else-if="campaign.status === 'failed'" class="flex items-center gap-1 text-red-500 font-medium">
            <i class="pi pi-times-circle"></i>
            Gagal
          </span>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-5 pt-4 border-t border-gray-100 flex gap-2">
        <router-link :to="`/campaigns/${campaign.slug}`" class="flex-1">
          <Button
            label="Lihat Detail"
            icon="pi pi-arrow-right"
            iconPos="right"
            class="w-full bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 font-medium !py-2.5 !rounded-xl shadow-sm transition-all"
          />
        </router-link>
        <Button
          v-if="campaign.status === 'active' && showBackingButton"
          label="Dukung"
          icon="pi pi-heart"
          class="flex-1 bg-emerald-600 border-none hover:bg-emerald-700 text-white font-medium !py-2.5 !rounded-xl shadow-sm transition-all"
          @click="$emit('backing', campaign)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Button from 'primevue/button'

const props = defineProps({
  campaign: {
    type: Object,
    required: true,
  },
  showBackingButton: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['backing'])

// ── Computed Helpers ──────────────────────────────────────────

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

const progressPercent = computed(() => {
  const col = Number(props.campaign.collected_amount || 0)
  const tar = Number(props.campaign.target_amount || 1)
  return Math.min(100, Math.round((col / tar) * 100))
})

const progressBarColor = computed(() => {
  const pct = progressPercent.value
  if (pct >= 100) return 'bg-emerald-700'
  if (pct >= 75) return 'bg-emerald-600'
  if (pct >= 50) return 'bg-emerald-500'
  if (pct >= 25) return 'bg-emerald-400'
  return 'bg-emerald-300'
})

const remainingDays = computed(() => {
  if (!props.campaign.deadline) return '-'
  const d = new Date(props.campaign.deadline)
  const diff = Math.ceil((d - new Date()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const statusLabel = computed(() => {
  const labels = { active: 'Aktif', success: 'Berhasil', failed: 'Gagal', draft: 'Draft', review: 'Review' }
  return labels[props.campaign.status] || props.campaign.status || '-'
})

const statusBadgeClass = computed(() => {
  const classes = {
    success: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    failed: 'bg-red-100 text-red-800 border-red-200',
    draft: 'bg-gray-100 text-gray-600 border-gray-200',
    review: 'bg-amber-100 text-amber-800 border-amber-200',
  }
  return classes[props.campaign.status] || ''
})

const statusIcon = computed(() => {
  const icons = {
    success: 'pi-check-circle',
    failed: 'pi-times-circle',
    draft: 'pi-pencil',
    review: 'pi-clock',
  }
  return icons[props.campaign.status] || ''
})
</script>
