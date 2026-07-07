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
      <div class="perspective-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
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

      <!-- Creator: Campaign Creation Form -->
      <div v-if="isCreator || isEditing" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
            <i class="pi pi-plus-circle text-lg text-emerald-700"></i>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Kampanye' : 'Buat Kampanye Baru' }}</h2>
            <p class="text-xs text-gray-400">{{ isEditing ? 'Perbarui detail kampanye Anda' : 'Lengkapi detail kampanye crowdfunding Anda' }}</p>
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
              class="w-full"
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

      <!-- Non-Creator: Upgrade prompt -->
      <div v-else-if="!isEditing && !isAdmin" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
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

      <!-- Tabs: My Campaigns / My Backings -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
        class="!rounded-2xl app-dialog !max-w-full !w-[95vw] sm:!w-auto"
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
        class="!rounded-2xl app-dialog !max-w-full !w-[95vw] sm:!w-auto"
        :style="{ maxWidth: '420px' }"
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
import { useGenieEffect } from '@/composables/useGenieEffect'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import Dialog from 'primevue/dialog'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useToast } from 'primevue/usetoast'
import campaignService from '@/services/campaignService'
import dashboardService from '@/services/dashboardService'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()
const { onDialogShow } = useGenieEffect()

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

const baseUrl = computed(() => {
  return window.location.origin + '/campaigns/'
})

// My Campaigns
const myCampaigns = ref([])
const myCampaignsLoading = ref(true)
const myBackingsCount = computed(() => authStore.user?.total_backings || 0)

// My Backings
const myBackings = ref([])
const myBackingsLoading = ref(false)

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
const editingCampaignId = ref(null)
const isEditing = computed(() => !!editingCampaignId.value)

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

// Image Upload State (multiple)
const imageInputRef = ref(null)
const uploadedImages = ref([])
const imageUploadError = ref('')
const imageUploadSuccess = ref('')
const imageUploadLoading = ref(false)

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

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      imageUploadError.value = 'Ukuran gambar maksimal 5MB per file'
      continue
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      imageUploadError.value = 'Format gambar tidak didukung. Gunakan JPEG, PNG, atau WebP'
      continue
    }

    // If editing existing campaign, upload immediately
    if (isEditing.value && editingCampaignId.value) {
      uploadImageToCampaign(editingCampaignId.value, file)
    } else {
      // For new campaign, store locally to upload after creation
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
    // Delete from server
    campaignService.deleteSpecificImage(editingCampaignId.value, img.id)
      .then(() => {
        uploadedImages.value.splice(index, 1)
        // Reassign primary if needed
        if (img.is_primary && uploadedImages.value.length > 0) {
          uploadedImages.value[0].is_primary = true
        }
        toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Gambar berhasil dihapus.', life: 2000 })
      })
      .catch(err => {
        toast.add({ severity: 'error', summary: 'Gagal', detail: err.response?.data?.message || 'Gagal menghapus gambar', life: 3000 })
      })
  } else {
    // Just remove from local state
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

const minDeadline = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 7)
  return date
})

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
  resetForm()
}

async function handleEditCampaign(data) {
  try {
    const res = await campaignService.getCampaignDetail(data.slug)
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
    editingCampaignId.value = data.id

    // Set existing images
    uploadedImages.value = (campaign.images || [])
      .filter(img => img.title !== 'Video Thumbnail')
      .map(img => ({
        id: img.id,
        image_url: img.image_url,
        is_primary: img.is_primary,
        file: null,
        preview: null,
      }))

    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat data kampanye.', life: 3000 })
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

function formatCurrency(val) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0)
}

function formatShortDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
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

  // Validate tiers
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
      // Update existing campaign
      const res = await campaignService.updateCampaign(editingCampaignId.value, payload)

      // Upload images that were selected locally (for new campaign create/edit)
      if (uploadedImages.value.some(img => img.file)) {
        for (const img of uploadedImages.value) {
          if (img.file) {
            await uploadImageToCampaign(editingCampaignId.value, img.file)
          }
        }
      }

      toast.add({ severity: 'success', summary: 'Berhasil', detail: res.message || 'Kampanye berhasil diperbarui.', life: 4000 })
      editingCampaignId.value = null
    } else {
      // Create new campaign
      const res = await campaignService.createCampaign(payload)
      const newCampaignId = res.data.id

      // Upload images that were selected
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

    await fetchMyCampaigns()
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

async function fetchCategories() {
  try {
    const res = await campaignService.getCategories()
    rawCategories.value = res?.data || []
  } catch (e) {
    rawCategories.value = []
  }
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
    fetchCategories(),
  ]

  if (isCreator.value) {
    promises.push(fetchCreatorStats(), fetchFundingChart())
  } else {
    promises.push(fetchBackerStats())
  }

  await Promise.all(promises)

  if (form.tiers.length === 0) {
    form.tiers.push(defaultTier())
  }
})
</script>
