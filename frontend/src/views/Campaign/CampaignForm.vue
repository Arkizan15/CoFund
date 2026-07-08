<template>
  <div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Back Navigation -->
      <div class="mb-6">
        <router-link
          :to="{ name: 'Dashboard' }"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-emerald-600 transition-colors no-underline"
        >
          <i class="pi pi-arrow-left text-xs"></i>
          Kembali ke Dashboard
        </router-link>
      </div>

      <!-- Page Header -->
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
          <i class="pi pi-plus-circle text-lg text-emerald-700"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Kampanye' : 'Buat Kampanye Baru' }}</h1>
          <p class="text-sm text-gray-400">{{ isEditing ? 'Perbarui detail kampanye Anda' : 'Lengkapi detail kampanye crowdfunding Anda' }}</p>
        </div>
      </div>

      <!-- Campaign Form -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
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

          <!-- Slug -->
          <div class="flex flex-col gap-1.5 lg:col-span-2">
            <label for="form-slug" class="text-sm font-semibold text-gray-700">
              Slug URL
              <span class="text-gray-400 font-normal">(Otomatis dari judul, bisa diedit)</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-mono">{{ baseUrl }}/</span>
              <InputText
                id="form-slug"
                v-model="form.slug"
                class="w-full !rounded-xl !text-sm !pl-[calc(4rem+8px)] !font-mono"
                :class="{ 'p-invalid': formErrors.slug }"
                :maxlength="120"
              />
            </div>
            <small v-if="formErrors.slug" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ formErrors.slug }}
            </small>
            <small v-else class="text-gray-400 text-xs">Hanya huruf kecil, angka, dan tanda hubung (-)</small>
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
              class="w-full !bg-white"
              :class="{ 'p-invalid': formErrors.category_id }"
              showClear
              :filter="true"
              :filterPlaceholder="'Cari kategori...'"
              panelClass="!bg-white !rounded-xl !shadow-lg !border !border-gray-100"
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
              panelClass="!bg-white !rounded-xl !shadow-lg !border !border-gray-100"
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

            <!-- YouTube Thumbnail Preview -->
            <div v-if="thumbnailPreview" class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 relative group">
              <img
                :src="thumbnailPreview"
                alt="YouTube Thumbnail Preview"
                class="w-full h-44 object-cover transition-transform duration-300 group-hover:scale-105"
                @load="thumbnailLoaded = true"
                @error="thumbnailError = true"
              />
              <div v-if="!thumbnailLoaded && !thumbnailError" class="absolute inset-0 flex items-center justify-center bg-gray-50/80">
                <i class="pi pi-spin pi-spinner text-xl text-emerald-600"></i>
              </div>
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-12 h-12 rounded-full bg-black/60 flex items-center justify-center backdrop-blur-sm">
                  <i class="pi pi-play text-white text-lg ml-0.5"></i>
                </div>
              </div>
              <div v-if="thumbnailLoaded" class="absolute top-2 right-2 bg-emerald-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                <i class="pi pi-check text-[8px]"></i> Thumbnail ditemukan
              </div>
              <div v-if="thumbnailError" class="absolute inset-0 flex items-center justify-center bg-red-50/90">
                <div class="text-center">
                  <i class="pi pi-exclamation-circle text-2xl text-red-400 mb-1 block"></i>
                  <p class="text-xs text-red-600 font-medium">Thumbnail tidak tersedia</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload Images (Multiple) -->
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-gray-700">
              Gambar Kampanye
              <span class="text-gray-400 font-normal">(Min. 1, Maks. 5)</span>
            </label>

            <!-- Image Gallery Preview -->
            <div v-if="uploadedImages.length > 0" class="grid grid-cols-3 gap-2 mb-3">
              <div
                v-for="(img, idx) in uploadedImages"
                :key="img.id || idx"
                class="relative group rounded-xl overflow-hidden border-2 transition-all duration-200 cursor-pointer"
                :class="img.is_primary ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300'"
                @click="!isEditing && img.id && handleSetPrimary(img.id)"
              >
                <img
                  :src="img.image_url || img.preview"
                  :alt="'Gambar ' + (idx + 1)"
                  class="w-full h-24 object-cover"
                />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-200 flex items-center justify-center">
                  <button
                    v-if="isEditing || !img.id"
                    type="button"
                    class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center shadow-sm hover:bg-red-600 transition-all"
                    @click.stop="removeUploadedImage(idx, img)"
                  >
                    <i class="pi pi-trash text-[10px]"></i>
                  </button>
                </div>
                <div
                  v-if="img.is_primary"
                  class="absolute top-1 left-1 bg-emerald-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-sm"
                >
                  UTAMA
                </div>
                <div v-else-if="!isEditing && img.id" class="absolute bottom-1 left-1 bg-black/50 text-white text-[8px] px-1.5 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                  Klik jadikan utama
                </div>
              </div>
            </div>

            <div
              class="relative border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200"
              :class="uploadedImages.length >= 5 ? 'border-gray-200 bg-gray-100 cursor-not-allowed opacity-60' : 'hover:border-emerald-400 hover:bg-emerald-50/30 border-gray-200 bg-gray-50'"
              @click="uploadedImages.length < 5 && triggerImageInput()"
            >
              <input
                ref="imageInputRef"
                type="file"
                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                class="hidden"
                multiple
                @change="handleImageSelect"
              />

              <div v-if="imageUploadLoading" class="space-y-2">
                <i class="pi pi-spin pi-spinner text-2xl text-emerald-500 block mx-auto"></i>
                <p class="text-sm text-emerald-600 font-medium">Mengunggah gambar...</p>
              </div>

              <div v-else class="space-y-2">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  <i class="pi pi-cloud-upload text-lg text-gray-400"></i>
                </div>
                <p class="text-sm text-gray-500 font-medium">
                  {{ uploadedImages.length >= 5 ? 'Maksimal 5 gambar' : 'Klik untuk upload gambar' }}
                </p>
                <p class="text-xs text-gray-400">{{ uploadedImages.length }}/5 — JPEG, PNG, WebP. Maks 5MB</p>
              </div>
            </div>
            <small v-if="imageUploadError" class="text-red-500 text-xs flex items-center gap-1">
              <i class="pi pi-exclamation-circle"></i>{{ imageUploadError }}
            </small>
            <small v-if="imageUploadSuccess" class="text-emerald-600 text-xs flex items-center gap-1">
              <i class="pi pi-check-circle"></i>{{ imageUploadSuccess }}
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

        <!-- ===== TIER REWARDS SECTION ===== -->
        <div class="mt-8 pt-6 border-t border-gray-100">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
              <i class="pi pi-gift text-amber-700"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Tier Reward</h3>
              <p class="text-xs text-gray-400">Daftar tier reward untuk backer</p>
            </div>
          </div>

          <!-- Tier Cards (read-only display) -->
          <div v-if="form.tiers.length > 0" class="space-y-3">
            <div
              v-for="(tier, idx) in form.tiers"
              :key="idx"
              class="flex items-start gap-3 bg-white rounded-[15px] border border-emerald-200 p-4"
            >
              <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <span class="text-xs font-bold text-amber-700">{{ idx + 1 }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <h4 class="font-bold text-gray-800 text-sm truncate">{{ tier.name || 'Tier ' + (idx + 1) }}</h4>
                  <div class="flex items-center gap-1 flex-shrink-0">
                    <button
                      type="button"
                      class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm hover:bg-amber-50 hover:border-amber-300 transition-all"
                      @click="editTier(idx)"
                      v-tooltip.left="'Edit'"
                    >
                      <i class="pi pi-pencil text-[10px] text-gray-500"></i>
                    </button>
                    <button
                      v-if="form.tiers.length > 1"
                      type="button"
                      class="w-7 h-7 rounded-full bg-white border border-gray-200 flex items-center justify-center shadow-sm hover:bg-red-50 hover:border-red-300 transition-all"
                      @click="removeTier(idx)"
                      v-tooltip.left="'Hapus'"
                    >
                      <i class="pi pi-trash text-[10px] text-red-400"></i>
                    </button>
                  </div>
                </div>
                <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-1.5">
                  <span class="font-bold text-sm text-emerald-700">{{ formatCurrency(tier.min_amount) }}</span>
                  <span class="text-gray-300">|</span>
                  <span v-if="tier.quota > 0" class="text-xs text-gray-500">
                    <i class="pi pi-users text-[10px]"></i> Kuota: {{ tier.quota }} backer
                  </span>
                  <span v-else class="text-xs text-gray-500">
                    <i class="pi pi-users text-[10px]"></i> Kuota tidak terbatas
                  </span>
                </div>
                <p v-if="tier.reward_description" class="text-sm text-gray-600 mt-2 leading-relaxed">
                  {{ tier.reward_description }}
                </p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <i class="pi pi-gift text-2xl text-gray-300 mb-2 block"></i>
            <p class="text-sm text-gray-500">Belum ada tier reward</p>
            <p class="text-xs text-gray-400 mt-0.5">Tambahkan minimal 1 tier untuk backer</p>
          </div>

          <Button
            v-if="form.tiers.length < 10"
            label="Tambah Tier"
            icon="pi pi-plus"
            class="mt-3 !text-emerald-600 !border-emerald-200 hover:!bg-emerald-50 !rounded-xl !text-sm"
            severity="success"
            outlined
            @click="openAddTierDialog"
          />
          <small class="ml-2 text-gray-400 text-xs">{{ form.tiers.length }}/10 tier</small>
        </div>

        <!-- Tier Add/Edit Dialog -->
        <Dialog
          v-model:visible="tierDialogVisible"
          :header="editingTierIndex !== null ? 'Edit Tier Reward' : 'Tambah Tier Reward'"
          :modal="true"
          class="app-dialog !rounded-[15px] !bg-white !border-2 !border-emerald-300 !shadow-lg !max-w-full !w-[95vw] sm:!w-auto"
          :style="{ maxWidth: '500px' }"
          @show="onDialogShow"
        >
          <div class="space-y-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-semibold text-gray-700">Nama Tier <span class="text-red-400">*</span></label>
              <InputText
                v-model="tierForm.name"
                placeholder="Contoh: Early Bird"
                class="w-full !rounded-xl !text-sm"
                :class="{ 'p-invalid': tierDialogErrors.name }"
                :maxlength="100"
              />
              <small v-if="tierDialogErrors.name" class="text-red-500 text-xs">{{ tierDialogErrors.name }}</small>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-semibold text-gray-700">Min. Nominal <span class="text-red-400">*</span></label>
              <InputNumber
                v-model="tierForm.min_amount"
                :min="10000"
                :step="10000"
                prefix="Rp "
                placeholder="Minimal nominal"
                class="w-full"
                :class="{ 'p-invalid': tierDialogErrors.min_amount }"
                fluid
              />
              <small v-if="tierDialogErrors.min_amount" class="text-red-500 text-xs">{{ tierDialogErrors.min_amount }}</small>
              <small v-else class="text-gray-400 text-xs">Minimal Rp 10.000</small>
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-semibold text-gray-700">Kuota <span class="text-gray-400 font-normal">(0 = tidak terbatas)</span></label>
              <InputNumber
                v-model="tierForm.quota"
                :min="0"
                :step="10"
                placeholder="Jumlah slot"
                class="w-full"
                fluid
              />
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-semibold text-gray-700">Deskripsi Reward <span class="text-gray-400 font-normal">(Opsional)</span></label>
              <Textarea
                v-model="tierForm.reward_description"
                rows="3"
                placeholder="Apa yang didapat backer di tier ini"
                class="w-full !rounded-xl !text-sm"
                :maxlength="1000"
              />
              <small class="text-gray-400 text-xs text-right">{{ (tierForm.reward_description || '').length }}/1000</small>
            </div>
          </div>

          <template #footer>
            <div class="flex gap-2 justify-end">
              <Button label="Batal" icon="pi pi-times" class="p-button-text !rounded-xl" @click="tierDialogVisible = false" />
              <Button
                label="Simpan"
                icon="pi pi-check"
                class="!bg-emerald-600 !border-emerald-600 hover:!bg-emerald-700 !text-white !rounded-xl !px-6"
                :loading="tierDialogLoading"
                @click="saveTier"
              />
            </div>
          </template>
        </Dialog>

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
            :label="isEditing ? 'Batal' : 'Reset Form'"
            :icon="isEditing ? 'pi pi-times' : 'pi pi-refresh'"
            class="p-button-text !text-gray-500 !rounded-xl"
            @click="isEditing ? cancelEdit() : resetForm()"
          />
          <router-link :to="{ name: 'Dashboard' }">
            <Button
              label="Kembali ke Dashboard"
              icon="pi pi-arrow-left"
              class="p-button-text !text-gray-500 !rounded-xl"
            />
          </router-link>
          <Button
            :label="isEditing ? 'Simpan Perubahan' : 'Buat Kampanye'"
            :icon="isEditing ? 'pi pi-save' : 'pi pi-plus-circle'"
            class="!bg-emerald-600 !border-none hover:!bg-emerald-700 !text-white !font-semibold !py-3 !px-8 !rounded-xl shadow-sm"
            :loading="createLoading"
            :disabled="createLoading"
            @click="handleCreateCampaign"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGenieEffect } from '@/composables/useGenieEffect'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import Dialog from 'primevue/dialog'
import { useToast } from 'primevue/usetoast'
import campaignService from '@/services/campaignService'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const toast = useToast()
const { onDialogShow } = useGenieEffect()

const isEditing = computed(() => !!route.params.slug)
const editingCampaignId = ref(null)

// Categories
const rawCategories = ref([])
const categories = computed(() => {
  return rawCategories.value.map(c => ({
    ...c,
    name: c.name
  }))
})

// Campaign Form
const form = reactive({
  title: '',
  slug: '',
  category_id: null,
  description: '',
  target_amount: null,
  deadline: null,
  video_url: '',
  tiers: [],
})
const formErrors = reactive({})
const formError = ref('')
const createLoading = ref(false)
const submitLoading = ref(false)
const createdCampaign = ref(null)

// Tier dialog state
const tierDialogVisible = ref(false)
const tierDialogLoading = ref(false)
const editingTierIndex = ref(null)
const tierForm = reactive({
  name: '',
  min_amount: null,
  quota: 0,
  reward_description: '',
})
const tierDialogErrors = reactive({})

// Image Upload State (multiple)
const imageInputRef = ref(null)
const uploadedImages = ref([])
const imageUploadError = ref('')
const imageUploadSuccess = ref('')
const imageUploadLoading = ref(false)

// Computed helpers
const baseUrl = computed(() => window.location.origin + '/campaigns/')

const minDeadline = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 7)
  return date
})

const thumbnailPreview = computed(() => {
  const url = form.video_url?.trim()
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
      return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`
    }
  }
  return null
})

const thumbnailLoaded = ref(false)
const thumbnailError = ref(false)

watch(thumbnailPreview, () => {
  thumbnailLoaded.value = false
  thumbnailError.value = false
})

// Auto-generate slug from title
watch(() => form.title, (newTitle) => {
  if (!isEditing.value && !form.slug) {
    form.slug = newTitle
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '')
      .substring(0, 80)
  }
})

// Tier functions
function defaultTier() {
  return { name: '', min_amount: null, quota: 0, reward_description: '' }
}

function openAddTierDialog() {
  editingTierIndex.value = null
  Object.assign(tierForm, defaultTier())
  Object.keys(tierDialogErrors).forEach(k => delete tierDialogErrors[k])
  tierDialogVisible.value = true
}

function editTier(index) {
  editingTierIndex.value = index
  const tier = form.tiers[index]
  Object.assign(tierForm, {
    name: tier.name,
    min_amount: tier.min_amount,
    quota: tier.quota,
    reward_description: tier.reward_description,
  })
  Object.keys(tierDialogErrors).forEach(k => delete tierDialogErrors[k])
  tierDialogVisible.value = true
}

function saveTier() {
  Object.keys(tierDialogErrors).forEach(k => delete tierDialogErrors[k])
  let valid = true

  if (!tierForm.name.trim()) {
    tierDialogErrors.name = 'Nama tier wajib diisi'
    valid = false
  }
  if (!tierForm.min_amount || tierForm.min_amount < 10000) {
    tierDialogErrors.min_amount = 'Minimal nominal tier Rp 10.000'
    valid = false
  }

  if (!valid) return

  const tierData = {
    name: tierForm.name.trim(),
    min_amount: tierForm.min_amount,
    quota: tierForm.quota || 0,
    reward_description: tierForm.reward_description?.trim() || '',
  }

  if (editingTierIndex.value !== null) {
    form.tiers[editingTierIndex.value] = { ...tierData }
  } else {
    form.tiers.push({ ...tierData })
  }

  tierDialogVisible.value = false
}

function removeTier(index) {
  form.tiers.splice(index, 1)
}

// Image functions
function triggerImageInput() {
  imageInputRef.value?.click()
}

function handleImageSelect(event) {
  const files = event.target.files
  if (!files || files.length === 0) return

  imageUploadError.value = ''
  imageUploadSuccess.value = ''

  const maxFiles = 5 - uploadedImages.value.length

  for (let i = 0; i < Math.min(files.length, maxFiles); i++) {
    const file = files[i]

    if (file.size > 5 * 1024 * 1024) {
      imageUploadError.value = 'Ukuran gambar maksimal 5MB per file'
      continue
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      imageUploadError.value = 'Format gambar tidak didukung. Gunakan JPEG, PNG, atau WebP'
      continue
    }

    if (isEditing.value && editingCampaignId.value) {
      uploadImageToCampaign(editingCampaignId.value, file)
    } else {
      const reader = new FileReader()
      reader.onload = (e) => {
        uploadedImages.value.push({
          file: file,
          preview: e.target.result,
          is_primary: uploadedImages.value.length === 0,
          id: null,
        })
      }
      reader.readAsDataURL(file)
    }
  }

  if (imageInputRef.value) imageInputRef.value.value = ''
}

async function uploadImageToCampaign(campaignId, file) {
  imageUploadLoading.value = true
  imageUploadError.value = ''
  try {
    const formData = new FormData()
    formData.append('image', file)
    const res = await campaignService.uploadCampaignImage(campaignId, formData)
    uploadedImages.value.push({
      id: res.data.id,
      image_url: res.data.image_url,
      is_primary: res.data.is_primary,
      file: null,
      preview: null,
    })
    imageUploadSuccess.value = 'Gambar berhasil diunggah!'
  } catch (error) {
    imageUploadError.value = error.response?.data?.message || 'Gagal mengunggah gambar'
  } finally {
    imageUploadLoading.value = false
  }
}

function removeUploadedImage(index, img) {
  if (img.id && isEditing.value) {
    campaignService.deleteSpecificImage(editingCampaignId.value, img.id)
      .then(() => {
        uploadedImages.value.splice(index, 1)
        if (img.is_primary && uploadedImages.value.length > 0) {
          uploadedImages.value[0].is_primary = true
        }
        toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Gambar berhasil dihapus.', life: 2000 })
      })
      .catch(err => {
        toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Gagal menghapus gambar', life: 3000 })
      })
  } else {
    uploadedImages.value.splice(index, 1)
    if (img.is_primary && uploadedImages.value.length > 0) {
      uploadedImages.value[0].is_primary = true
    }
  }
}

async function handleSetPrimary(imageId) {
  if (!editingCampaignId.value) return
  try {
    await campaignService.setPrimaryImage(editingCampaignId.value, imageId)
    uploadedImages.value.forEach(img => { img.is_primary = img.id === imageId })
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Gambar utama berhasil diubah.', life: 2000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal mengubah gambar utama', life: 3000 })
  }
}

// Validation & Submission
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
  if (form.slug && !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(form.slug)) {
    formErrors.slug = 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung'
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

  if (form.tiers.length === 0) {
    formError.value = 'Kampanye harus memiliki minimal 1 tier reward'
    valid = false
  }
  form.tiers.forEach((tier) => {
    if (!tier.name.trim()) {
      formError.value = 'Semua tier harus memiliki nama'
      valid = false
    }
    if (!tier.min_amount || tier.min_amount < 10000) {
      formError.value = 'Semua tier harus memiliki nominal minimal Rp 10.000'
      valid = false
    }
  })

  return valid
}

async function handleCreateCampaign() {
  if (!validateForm()) return

  createLoading.value = true
  formError.value = ''

  try {
    const payload = {
      title: form.title.trim(),
      slug: form.slug.trim() || undefined,
      category_id: form.category_id,
      description: form.description.trim(),
      target_amount: form.target_amount,
      deadline: form.deadline.toISOString().split('T')[0],
      tiers: form.tiers.map(t => ({
        id: t.id || undefined,
        name: t.name.trim(),
        min_amount: t.min_amount,
        quota: t.quota || 0,
        reward_description: t.reward_description?.trim() || undefined,
      })),
    }
    if (form.video_url.trim()) {
      payload.video_url = form.video_url.trim()
    }

    if (isEditing.value) {
      const res = await campaignService.updateCampaign(editingCampaignId.value, payload)

      if (uploadedImages.value.some(img => img.file)) {
        for (const img of uploadedImages.value) {
          if (img.file) {
            await uploadImageToCampaign(editingCampaignId.value, img.file)
          }
        }
      }

      toast.add({ severity: 'success', summary: 'Berhasil', detail: res.message || 'Kampanye berhasil diperbarui.', life: 4000 })
      router.push({ name: 'Dashboard' })
    } else {
      const res = await campaignService.createCampaign(payload)
      const newCampaignId = res.data.id

      if (uploadedImages.value.some(img => img.file)) {
        for (const img of uploadedImages.value) {
          if (img.file) {
            await uploadImageToCampaign(newCampaignId, img.file)
          }
        }
      }

      createdCampaign.value = res.data
      toast.add({ severity: 'success', summary: 'Berhasil', detail: res.message || 'Kampanye berhasil dibuat.', life: 4000 })
    }
  } catch (error) {
    formError.value = error.response?.data?.message || 'Gagal menyimpan kampanye. Silakan coba lagi.'
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
    router.push({ name: 'Dashboard' })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error.response?.data?.message || 'Gagal mengirim ke review.', life: 4000 })
  } finally {
    submitLoading.value = false
  }
}

function resetForm() {
  form.title = ''
  form.slug = ''
  form.category_id = null
  form.description = ''
  form.target_amount = null
  form.deadline = null
  form.video_url = ''
  form.tiers = []
  Object.keys(formErrors).forEach(k => delete formErrors[k])
  formError.value = ''
  createdCampaign.value = null
  editingCampaignId.value = null
  uploadedImages.value = []
  imageUploadError.value = ''
  imageUploadSuccess.value = ''
}

function cancelEdit() {
  router.push({ name: 'Dashboard' })
}

async function loadCampaignForEdit(slug) {
  try {
    const res = await campaignService.getCampaignDetail(slug)
    const campaign = res?.data || {}
    form.title = campaign.title || ''
    form.slug = campaign.slug || ''
    form.category_id = campaign.category_id || null
    form.description = campaign.description || ''
    form.target_amount = campaign.target_amount || null
    form.deadline = campaign.deadline ? new Date(campaign.deadline) : null
    form.video_url = campaign.video_url || ''
    form.tiers = (campaign.tiers || []).map(t => ({
      id: t.id,
      name: t.name,
      min_amount: t.min_amount,
      quota: t.quota,
      reward_description: t.reward_description || '',
    }))
    editingCampaignId.value = campaign.id

    uploadedImages.value = (campaign.images || [])
      .filter(img => img.title !== 'Video Thumbnail')
      .map(img => ({
        id: img.id,
        image_url: img.image_url,
        is_primary: img.is_primary,
        file: null,
        preview: null,
      }))
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat data kampanye.', life: 3000 })
    router.push({ name: 'Dashboard' })
  }
}

onMounted(async () => {
  // Fetch categories
  try {
    const res = await campaignService.getCategories()
    rawCategories.value = res?.data || []
  } catch (e) {
    rawCategories.value = []
  }

  // If editing, load campaign data
  if (route.params.slug) {
    await loadCampaignForEdit(route.params.slug)
  }

  // Initialize with one tier slot if creating new
  if (!isEditing.value && form.tiers.length === 0) {
    form.tiers.push(defaultTier())
  }
})
</script>
