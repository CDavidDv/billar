<template>
  <KioskLayout>
    <div class="w-full h-full flex items-center justify-center bg-black overflow-hidden">

      <!-- YouTube embed -->
      <template v-if="current.type === 'youtube'">
        <iframe
          :src="youtubeEmbedUrl"
          class="w-full h-full"
          frameborder="0"
          allow="autoplay; fullscreen"
          allowfullscreen
        />
      </template>

      <!-- Image fullscreen -->
      <template v-else-if="current.type === 'image'">
        <img
          :src="current.content"
          :alt="current.title"
          class="w-full h-full object-contain"
        />
      </template>

      <!-- Stream / external URL -->
      <template v-else-if="current.type === 'stream'">
        <iframe
          :src="current.content"
          class="w-full h-full"
          frameborder="0"
          allow="autoplay; fullscreen"
          allowfullscreen
        />
      </template>

      <!-- Text / default -->
      <template v-else>
        <div class="text-center px-16">
          <p class="text-neutral-600 text-xl font-medium mb-4 uppercase tracking-widest">Billar</p>
          <h1 class="text-white font-bold leading-tight" style="font-size: clamp(3rem, 8vw, 8rem)">
            {{ current.content }}
          </h1>
        </div>
      </template>

    </div>
  </KioskLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import KioskLayout from '@/Layouts/KioskLayout.vue'

const props = defineProps({
  initial: { type: Object, default: () => ({ type: 'text', content: 'Billar', title: 'Bienvenido' }) },
})

const current = ref({ ...props.initial })
let interval = null

const youtubeEmbedUrl = computed(() => {
  const url = current.value.content ?? ''
  const match = url.match(/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{11})/)
  if (!match) return url
  return `https://www.youtube.com/embed/${match[1]}?autoplay=1&mute=1&loop=1&playlist=${match[1]}&controls=0`
})

async function poll() {
  try {
    const res = await fetch(route('display.content'))
    const data = await res.json()
    current.value = data
  } catch {
    // silent — keep showing current content
  }
}

onMounted(() => {
  interval = setInterval(poll, 30000)
})

onUnmounted(() => {
  clearInterval(interval)
})
</script>
