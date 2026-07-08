<template>
  <Dialog
    v-model:visible="visible"
    :modal="true"
    :closable="true"
    :draggable="false"
    class="!rounded-[20px] !bg-white !max-w-full !w-[95vw] sm:!w-[500px]"
    :style="{ maxWidth: '500px' }"
    @show="currentStep = 1"
  >
    <!-- Step indicator -->
    <div class="flex items-center justify-center gap-2 mb-6">
      <div
        v-for="step in totalSteps"
        :key="step"
        class="w-2.5 h-2.5 rounded-full transition-all duration-300"
        :class="step === currentStep ? 'w-8 bg-emerald-600' : step < currentStep ? 'bg-emerald-300' : 'bg-gray-200'"
      ></div>
    </div>

    <!-- Step 1: Welcome -->
    <div v-if="currentStep === 1" class="text-center py-4">
      <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-shield text-3xl text-emerald-700"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Selamat Datang, Kreator!</h3>
      <p class="text-sm text-gray-500 leading-relaxed">
        Anda kini resmi menjadi Creator di CoFund! Ikuti panduan singkat ini untuk memulai kampanye pertama Anda.
      </p>
    </div>

    <!-- Step 2: Create Campaign -->
    <div v-if="currentStep === 2" class="py-4">
      <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-flag text-3xl text-blue-700"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Buat Kampanye</h3>
      <p class="text-sm text-gray-500 mb-4 text-center">Isi detail kampanye Anda dengan lengkap</p>
      <div class="space-y-3 bg-slate-50 rounded-xl p-4">
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="text-xs font-bold text-blue-700">1</span>
          </div>
          <p class="text-sm text-gray-600">Tentukan judul, target dana, dan batas waktu kampanye</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="text-xs font-bold text-blue-700">2</span>
          </div>
          <p class="text-sm text-gray-600">Tambahkan gambar dan deskripsi yang menarik</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <span class="text-xs font-bold text-blue-700">3</span>
          </div>
          <p class="text-sm text-gray-600">Buat tier reward untuk para backer</p>
        </div>
      </div>
    </div>

    <!-- Step 3: Approval Process -->
    <div v-if="currentStep === 3" class="py-4">
      <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-clock text-3xl text-orange-700"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Proses Review</h3>
      <p class="text-sm text-gray-500 mb-4 text-center">Setelah kampanye dibuat, tim admin akan mereview</p>
      <div class="space-y-3 bg-slate-50 rounded-xl p-4">
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-send text-[10px] text-orange-700"></i>
          </div>
          <p class="text-sm text-gray-600">Kirim kampanye untuk direview oleh admin</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-check-circle text-[10px] text-orange-700"></i>
          </div>
          <p class="text-sm text-gray-600">Admin akan menyetujui atau memberikan masukan</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-globe text-[10px] text-orange-700"></i>
          </div>
          <p class="text-sm text-gray-600">Setelah disetujui, kampanye akan tayang di platform</p>
        </div>
      </div>
    </div>

    <!-- Step 4: Fund Management -->
    <div v-if="currentStep === 4" class="py-4">
      <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-wallet text-3xl text-emerald-700"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Kelola Dana</h3>
      <p class="text-sm text-gray-500 mb-4 text-center">Pantau dan kelola dana kampanye Anda</p>
      <div class="space-y-3 bg-slate-50 rounded-xl p-4">
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-chart-bar text-[10px] text-emerald-700"></i>
          </div>
          <p class="text-sm text-gray-600">Pantau perkembangan dana di dashboard Creator</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-users text-[10px] text-emerald-700"></i>
          </div>
          <p class="text-sm text-gray-600">Lihat daftar backer yang mendukung kampanye Anda</p>
        </div>
        <div class="flex items-start gap-3">
          <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="pi pi-bell text-[10px] text-emerald-700"></i>
          </div>
          <p class="text-sm text-gray-600">Dapatkan notifikasi saat ada backer baru</p>
        </div>
      </div>
    </div>

    <!-- Step 5: Done -->
    <div v-if="currentStep === 5" class="text-center py-4">
      <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="pi pi-check-circle text-3xl text-emerald-700"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-900 mb-2">Siap Memulai!</h3>
      <p class="text-sm text-gray-500 leading-relaxed mb-4">
        Anda sudah siap menjadi kreator di CoFund! Mulai buat kampanye pertama Anda sekarang.
      </p>
      <router-link :to="{ name: 'Dashboard' }">
        <Button
          label="Buat Kampanye Sekarang"
          icon="pi pi-plus-circle"
          class="!bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700 !text-white !rounded-xl !py-3 !w-full"
          @click="close"
        />
      </router-link>
    </div>

    <!-- Navigation buttons -->
    <template #footer>
      <div class="flex items-center justify-between pt-2">
        <Button
          v-if="currentStep > 1"
          label="Kembali"
          icon="pi pi-chevron-left"
          class="p-button-text !text-gray-500 !rounded-xl"
          @click="currentStep--"
        />
        <div v-else></div>
        <div class="flex gap-2">
          <Button
            label="Lewati"
            class="p-button-text !text-gray-400 !rounded-xl !text-sm"
            @click="close"
          />
          <Button
            v-if="currentStep < totalSteps"
            :label="currentStep === totalSteps - 1 ? 'Selesai' : 'Lanjut'"
            icon="pi pi-chevron-right"
            iconPos="right"
            class="!bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700 !text-white !rounded-xl"
            @click="currentStep++"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'

const props = defineProps({
  visible: { type: Boolean, default: false },
})
const emit = defineEmits(['update:visible', 'close'])

const totalSteps = 5
const currentStep = ref(1)

function close() {
  emit('update:visible', false)
  emit('close')
}
</script>
