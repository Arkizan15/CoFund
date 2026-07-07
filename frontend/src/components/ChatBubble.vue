<template>
  <div
    class="flex items-start gap-3 min-w-0"
    :class="[
      variant === 'sent' ? 'flex-row-reverse' : 'flex-row',
    ]"
  >
    <!-- Avatar -->
    <div
      v-if="showAvatar"
      class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold shadow-sm ring-2 ring-white"
      :style="{ backgroundColor: avatarBg }"
    >
      <span v-if="!avatarImage" class="text-white">{{ avatarInitial }}</span>
      <img v-else :src="avatarImage" :alt="name" class="w-full h-full rounded-full object-cover" />
    </div>

    <!-- Bubble Content -->
    <div class="flex flex-col max-w-[85%] sm:max-w-[75%]" :class="variant === 'sent' ? 'items-end' : 'items-start'">
      <!-- Sender Name -->
      <span
        v-if="showName && name"
        class="text-[11px] font-semibold text-gray-500 mb-1 px-1"
        :class="variant === 'sent' ? 'text-right' : 'text-left'"
      >
        {{ name }}
      </span>

      <!-- Message Bubble -->
      <div
        class="relative px-4 py-3 break-words leading-relaxed shadow-sm"
        :class="[
          variant === 'sent'
            ? 'bg-bubble-primary text-white rounded-bubble rounded-tr-[4px]'
            : 'bg-bubble-surface text-gray-800 rounded-bubble rounded-tl-[4px]',
          bubbleClass,
        ]"
      >
        <!-- Message body -->
        <p class="text-sm whitespace-pre-line">{{ message }}</p>

        <!-- Timestamp -->
        <div
          class="flex items-center gap-1.5 mt-1.5"
          :class="variant === 'sent' ? 'justify-end' : 'justify-start'"
        >
          <span
            class="text-[10px] leading-none"
            :class="variant === 'sent' ? 'text-white/70' : 'text-gray-400'"
          >
            {{ time }}
          </span>
          <i
            v-if="variant === 'sent' && status"
            :class="statusIcon"
            class="text-[10px]"
            :style="{ color: statusColor }"
          ></i>
        </div>
      </div>

      <!-- Reactions Row -->
      <div
        v-if="reactions && reactions.length > 0"
        class="flex items-center gap-1 mt-1"
        :class="variant === 'sent' ? 'flex-row-reverse' : 'flex-row'"
      >
        <span
          v-for="(reaction, rIdx) in reactions"
          :key="rIdx"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] bg-white border border-gray-200 shadow-sm cursor-default select-none"
          v-tooltip.top="reaction.users?.join(', ')"
        >
          {{ reaction.emoji }}
          <span v-if="reaction.count > 1" class="text-gray-400 font-medium">{{ reaction.count }}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: { type: String, required: true },
  variant: { type: String, default: 'received' }, // 'sent' | 'received'
  name: { type: String, default: '' },
  time: { type: String, default: '' },
  status: { type: String, default: '' }, // 'sent' | 'delivered' | 'read' | 'error'
  showAvatar: { type: Boolean, default: false },
  showName: { type: Boolean, default: false },
  avatarImage: { type: String, default: '' },
  avatarBg: { type: String, default: '#22C55E' },
  reactions: { type: Array, default: () => [] }, // [{ emoji: '❤️', count: 2, users: [] }]
  bubbleClass: { type: [String, Array, Object], default: '' },
})

const avatarInitial = computed(() => {
  if (!props.name) return '?'
  return props.name.charAt(0).toUpperCase()
})

const statusIcon = computed(() => {
  const icons = { sent: 'pi pi-check', delivered: 'pi pi-check', read: 'pi pi-check-circle', error: 'pi pi-exclamation-circle' }
  return icons[props.status] || ''
})

const statusColor = computed(() => {
  const colors = { sent: '#94a3b8', delivered: '#22C55E', read: '#3B82F6', error: '#EF4444' }
  return colors[props.status] || ''
})
</script>
