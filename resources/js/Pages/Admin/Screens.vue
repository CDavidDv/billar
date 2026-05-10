<template>
  <AppLayout title="Gestión de Pantallas">
    <template #header-actions>
      <button
        @click="openCreate"
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors"
      >
        <PlusIcon class="w-4 h-4" />
        Nuevo contenido
      </button>
    </template>

    <div class="p-6 space-y-6">

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>

      <!-- Preview link -->
      <div class="flex items-center gap-3 p-4 bg-neutral-800 border border-neutral-700 rounded-xl">
        <TvIcon class="w-5 h-5 text-green-400 shrink-0" />
        <p class="text-neutral-300 text-sm">Vista kiosk:</p>
        <a :href="route('display.index')" target="_blank" class="text-green-400 hover:text-green-300 text-sm font-medium">
          {{ route('display.index') }} ↗
        </a>
      </div>

      <!-- Content list -->
      <div v-if="screens.length === 0" class="text-center py-16">
        <TvIcon class="w-12 h-12 text-neutral-600 mx-auto mb-3" />
        <p class="text-neutral-400">Sin contenido aún</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="screen in screens"
          :key="screen.id"
          class="bg-neutral-800 rounded-xl border p-5 flex items-start justify-between gap-4"
          :class="screen.is_active ? 'border-green-700/50' : 'border-neutral-700'"
        >
          <div class="flex items-start gap-4 min-w-0">
            <!-- Type badge -->
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="typeBg(screen.type)">
              <span class="text-lg">{{ typeIcon(screen.type) }}</span>
            </div>

            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-white font-semibold">{{ screen.title }}</p>
                <span v-if="screen.is_active" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-400">
                  Activo
                </span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-neutral-700 text-neutral-400">
                  Inactivo
                </span>
              </div>
              <p class="text-neutral-500 text-xs mt-0.5 uppercase tracking-wider">{{ screen.type }}</p>
              <p class="text-neutral-400 text-sm mt-1 truncate max-w-lg">{{ screen.content }}</p>
              <p v-if="screen.scheduled_at" class="text-amber-400 text-xs mt-1">
                ⏰ {{ formatDate(screen.scheduled_at) }}
                <span v-if="screen.scheduled_end_at"> → {{ formatDate(screen.scheduled_end_at) }}</span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button
              v-if="!screen.is_active"
              @click="activate(screen.id)"
              class="px-3 py-1.5 bg-green-700 hover:bg-green-600 text-white text-xs font-semibold rounded-lg transition-colors"
            >
              Activar
            </button>
            <button
              v-else
              @click="deactivate(screen.id)"
              class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-300 text-xs font-semibold rounded-lg border border-neutral-600 transition-colors"
            >
              Desactivar
            </button>
            <button
              @click="openEdit(screen)"
              class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 text-xs font-semibold rounded-lg border border-neutral-600 transition-colors"
            >
              Editar
            </button>
            <button
              @click="destroy(screen.id)"
              class="px-3 py-1.5 bg-red-900/50 hover:bg-red-800/50 text-red-400 text-xs font-semibold rounded-lg border border-red-700/40 transition-colors"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">{{ editing ? 'Editar contenido' : 'Nuevo contenido' }}</h2>
          <button @click="showModal = false" class="text-neutral-400 hover:text-white">✕</button>
        </div>
        <form @submit.prevent="submitForm" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Título</label>
            <input v-model="form.title" type="text" required class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Tipo</label>
            <div class="grid grid-cols-4 gap-2">
              <button
                v-for="t in contentTypes"
                :key="t.value"
                type="button"
                @click="form.type = t.value"
                class="py-2.5 rounded-lg border text-xs font-medium text-center transition-colors"
                :class="form.type === t.value ? 'border-green-600 bg-green-900/40 text-green-400' : 'border-neutral-600 bg-neutral-700 text-neutral-300 hover:bg-neutral-600'"
              >
                {{ t.icon }}<br />{{ t.label }}
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">
              <span v-if="form.type === 'youtube'">URL de YouTube</span>
              <span v-else-if="form.type === 'image'">URL de imagen</span>
              <span v-else-if="form.type === 'stream'">URL del stream</span>
              <span v-else>Texto a mostrar</span>
            </label>
            <textarea
              v-if="form.type === 'text'"
              v-model="form.content"
              rows="3"
              required
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white placeholder-neutral-400 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <input
              v-else
              v-model="form.content"
              type="url"
              required
              placeholder="https://..."
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white placeholder-neutral-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-300 mb-1.5">Inicio programado</label>
              <input v-model="form.scheduled_at" type="datetime-local" class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-neutral-300 mb-1.5">Fin programado</label>
              <input v-model="form.scheduled_end_at" type="datetime-local" class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            </div>
          </div>

          <label class="flex items-center gap-2 text-sm text-neutral-300 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="rounded bg-neutral-700 border-neutral-600 text-green-600 focus:ring-green-500" />
            Activar inmediatamente
          </label>

          <div class="flex gap-3 pt-1">
            <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors">
              {{ editing ? 'Guardar' : 'Crear' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon, TvIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  screens: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)

const contentTypes = [
  { value: 'text', label: 'Texto', icon: '📝' },
  { value: 'youtube', label: 'YouTube', icon: '▶️' },
  { value: 'image', label: 'Imagen', icon: '🖼️' },
  { value: 'stream', label: 'Stream', icon: '📡' },
]

const form = useForm({
  title: '',
  type: 'text',
  content: '',
  is_active: false,
  scheduled_at: '',
  scheduled_end_at: '',
  sort_order: 0,
})

function typeBg(type) {
  const map = { youtube: 'bg-red-900/40', image: 'bg-blue-900/40', stream: 'bg-purple-900/40', text: 'bg-neutral-700' }
  return map[type] ?? 'bg-neutral-700'
}

function typeIcon(type) {
  const map = { youtube: '▶️', image: '🖼️', stream: '📡', text: '📝' }
  return map[type] ?? '📝'
}

function formatDate(iso) {
  return new Date(iso).toLocaleString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function openCreate() {
  editing.value = null
  form.reset()
  showModal.value = true
}

function openEdit(screen) {
  editing.value = screen
  form.title = screen.title
  form.type = screen.type
  form.content = screen.content
  form.is_active = screen.is_active
  form.scheduled_at = screen.scheduled_at ? screen.scheduled_at.slice(0, 16) : ''
  form.scheduled_end_at = screen.scheduled_end_at ? screen.scheduled_end_at.slice(0, 16) : ''
  form.sort_order = screen.sort_order
  showModal.value = true
}

function submitForm() {
  if (editing.value) {
    form.put(route('admin.screens.update', editing.value.id), {
      onSuccess: () => { showModal.value = false },
    })
  } else {
    form.post(route('admin.screens.store'), {
      onSuccess: () => { showModal.value = false },
    })
  }
}

function activate(id) {
  router.post(route('admin.screens.activate', id))
}

function deactivate(id) {
  router.post(route('admin.screens.deactivate', id))
}

function destroy(id) {
  if (!confirm('¿Eliminar este contenido?')) return
  router.delete(route('admin.screens.destroy', id))
}
</script>
