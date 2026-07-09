<template>
  <div class="relative" ref="menuRef">
    <button
      type="button"
      @click="toggleMenu"
      class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors no-underline rounded-full"
    >
      <i class="pi pi-globe text-sm"></i>
      <span class="hidden sm:inline">{{ currentLangLabel }}</span>
    </button>

    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-44 rounded-xl border border-gray-100 bg-white p-1.5 shadow-lg z-50"
    >
      <button
        v-for="option in languages"
        :key="option.value"
        type="button"
        @click="selectLanguage(option.value)"
        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-left transition-colors"
        :class="currentLang === option.value ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50'"
      >
        <span class="font-medium">{{ option.label }}</span>
        <span class="text-[11px] uppercase tracking-wide">{{ option.value }}</span>
      </button>
    </div>
  </div>

  <div id="google_translate_element" class="sr-only"></div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const isOpen = ref(false)
const menuRef = ref(null)
const currentLang = ref('id')

const languages = [
  { label: 'Bahasa Indonesia', value: 'id' },
  { label: 'English', value: 'en' },
]

const currentLangLabel = computed(() => {
  const selected = languages.find((item) => item.value === currentLang.value)
  return selected ? selected.value.toUpperCase() : 'ID'
})

function toggleMenu() {
  isOpen.value = !isOpen.value
}

function closeMenu(event) {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

function loadGoogleTranslateScript() {
  if (document.getElementById('google-translate-script')) return

  window.googleTranslateElementInit = () => {
    if (!window.google?.translate?.TranslateElement) return

    new window.google.translate.TranslateElement(
      {
        pageLanguage: 'id',
        includedLanguages: 'en,id',
        layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
      },
      'google_translate_element'
    )
  }

  const script = document.createElement('script')
  script.id = 'google-translate-script'
  script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
  script.async = true
  document.body.appendChild(script)
}

function persistSelection(lang) {
  currentLang.value = lang
  localStorage.setItem('cofund-language', lang)
  document.documentElement.lang = lang
  document.cookie = `googtrans=/auto/${lang}; path=/; max-age=31536000`
  document.cookie = `googtrans=/auto/${lang}; path=/; domain=${window.location.hostname}; max-age=31536000`

  const url = new URL(window.location.href)
  url.searchParams.set('lang', lang)
  window.history.replaceState({}, '', url.toString())
  window.location.reload()
}

function selectLanguage(lang) {
  isOpen.value = false
  if (lang === currentLang.value) return
  persistSelection(lang)
}

onMounted(() => {
  const savedLang = localStorage.getItem('cofund-language')
  if (savedLang && ['id', 'en'].includes(savedLang)) {
    currentLang.value = savedLang
  }

  document.addEventListener('click', closeMenu)
  loadGoogleTranslateScript()
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
})
</script>
