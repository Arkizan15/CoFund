<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-pulse flex flex-col">
    <!-- Image skeleton -->
    <div class="h-48 bg-gray-200"></div>

    <!-- Content skeleton -->
    <div class="p-5 space-y-4">
      <!-- Title -->
      <div class="h-5 bg-gray-200 rounded-lg w-3/4"></div>
      <div class="h-5 bg-gray-200 rounded-lg w-1/2"></div>

      <!-- Stats -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <div class="h-4 bg-gray-200 rounded w-20"></div>
          <div class="h-4 bg-gray-200 rounded w-24"></div>
        </div>
        <div class="flex items-center justify-between">
          <div class="h-4 bg-gray-200 rounded w-16"></div>
          <div class="h-4 bg-gray-200 rounded w-24"></div>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="h-2.5 bg-gray-200 rounded-full w-full"></div>

      <!-- Bottom stats -->
      <div class="flex items-center justify-between">
        <div class="h-3 bg-gray-200 rounded w-20"></div>
        <div class="h-3 bg-gray-200 rounded w-16"></div>
      </div>

      <!-- Actions -->
      <div class="pt-4 border-t border-gray-100 flex gap-2">
        <div class="h-10 bg-gray-200 rounded-xl flex-1"></div>
        <div class="h-10 bg-gray-200 rounded-xl flex-1"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Skeleton loader for CampaignCard — pulse animation is handled by Tailwind's animate-pulse
</script>
