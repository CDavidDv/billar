<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    recent: { type: Array, default: () => [] },
})

const form = useForm({
    title: '',
    artist: '',
})

const submitting = ref(false)
const success = ref(false)

function submit() {
    submitting.value = true
    form.post(route('songs.store'), {
        onSuccess: () => {
            submitting.value = false
            success.value = true
            form.reset()
            setTimeout(() => { success.value = false }, 3000)
        },
        onError: () => {
            submitting.value = false
        },
    })
}
</script>

<template>
    <div class="min-h-screen bg-neutral-950">
        <!-- Header -->
        <header class="bg-neutral-900/95 backdrop-blur border-b border-neutral-800">
            <div class="px-4 py-4 text-center">
                <div class="inline-flex items-center gap-2">
                    <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">🎵</span>
                    </div>
                    <h1 class="text-white font-bold">Música</h1>
                </div>
                <p class="text-neutral-500 text-xs mt-1">Solicita tu canción</p>
            </div>
        </header>

        <!-- Main -->
        <main class="p-4 pb-24">
            <!-- Success -->
            <div v-if="success" class="mb-4 p-4 bg-green-900/40 border border-green-700/50 rounded-xl text-green-400 text-center">
                Canción solicitada ✓
            </div>

            <!-- Request form -->
            <div class="bg-neutral-900 rounded-xl border border-neutral-800 p-4 mb-6">
                <h2 class="text-white font-semibold mb-4">Nueva solicitud</h2>
                <form @submit.prevent="submit" class="space-y-3">
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Título</label>
                        <input v-model="form.title" type="text" placeholder="Nombre de la canción"
                            class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Artista (opcional)</label>
                        <input v-model="form.artist" type="text" placeholder="Grupo o cantante"
                            class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-3 py-2 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-green-500" />
                    </div>
                    <button type="submit" :disabled="submitting || !form.title"
                        class="w-full py-3 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors">
                        {{ submitting ? 'Enviando...' : 'Solicitar' }}
                    </button>
                </form>
            </div>

            <!-- Recently played -->
            <div v-if="recent.length > 0">
                <h2 class="text-white font-semibold mb-3">Últimas reproducidas</h2>
                <div class="space-y-2">
                    <div v-for="song in recent" :key="song.id"
                        class="bg-neutral-900 rounded-lg border border-neutral-800 p-3 flex justify-between items-center">
                        <div>
                            <p class="text-white font-medium">{{ song.title }}</p>
                            <p class="text-neutral-500 text-sm">{{ song.artist }}</p>
                        </div>
                        <div class="text-amber-400 text-sm">👍 {{ song.votes }}</div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="fixed bottom-0 left-0 right-0 bg-neutral-900/95 backdrop-blur border-t border-neutral-800 py-3 text-center">
            <p class="text-neutral-600 text-xs">Vota por tu canción favorita</p>
        </footer>
    </div>
</template>