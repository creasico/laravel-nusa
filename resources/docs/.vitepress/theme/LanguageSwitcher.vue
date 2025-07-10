<template>
  <div class="language-switcher">
    <button
      class="language-button"
      @click="toggleDropdown"
      :aria-label="currentLocale.label"
    >
      {{ currentLocale.label }}
      <span class="icon">▼</span>
    </button>
    <div class="language-dropdown" v-if="isOpen">
      <ul class="language-list">
        <li v-for="locale in availableLocales" :key="locale.lang">
          <a
            :href="getLocalePath(locale)"
            :class="{ active: locale.lang === currentLocale.lang }"
            @click="closeDropdown"
          >
            {{ locale.label }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useData, useRoute } from 'vitepress'

const { site, localeIndex, theme, page } = useData()
const route = useRoute()

const isOpen = ref(false)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const switcher = document.querySelector('.language-switcher')
  if (switcher && !switcher.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const currentLocale = computed(() => {
  return site.value.locales[localeIndex.value]
})

const availableLocales = computed(() => {
  return Object.values(site.value.locales)
})

const getLocalePath = (locale) => {
  // Get current path
  const currentPath = page.value.relativePath
  
  // Handle root locale (English)
  if (locale.lang === 'en') {
    // If we're in Indonesian, remove the 'id/' prefix
    if (currentPath.startsWith('id/')) {
      return '/' + currentPath.replace(/^id\//, '')
    }
    return '/'
  }
  
  // Handle Indonesian locale
  if (locale.lang === 'id') {
    // If we're already in Indonesian, keep the path
    if (currentPath.startsWith('id/')) {
      return '/' + currentPath
    }
    
    // If we're in root (English), add 'id/' prefix
    return '/id/' + currentPath
  }
  
  // Default fallback
  return '/'
}
</script>

<style scoped>
.language-switcher {
  position: relative;
  display: inline-block;
  margin-right: 1rem;
}

.language-button {
  display: flex;
  align-items: center;
  background: transparent;
  border: 1px solid var(--vp-c-divider);
  border-radius: 4px;
  padding: 0.25rem 0.5rem;
  font-size: 0.9rem;
  cursor: pointer;
  color: var(--vp-c-text-1);
}

.language-button .icon {
  font-size: 0.7rem;
  margin-left: 0.25rem;
}

.language-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.25rem;
  background: var(--vp-c-bg);
  border: 1px solid var(--vp-c-divider);
  border-radius: 4px;
  min-width: 120px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.language-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.language-list li a {
  display: block;
  padding: 0.5rem 1rem;
  text-decoration: none;
  color: var(--vp-c-text-1);
}

.language-list li a:hover {
  background: var(--vp-c-bg-soft);
}

.language-list li a.active {
  font-weight: bold;
  background: var(--vp-c-bg-soft);
}
</style>
