<template>
  <AppLayout title="Configuración">
    <div class="p-6 space-y-6">

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>

      <form @submit.prevent="save" class="space-y-6">
        <!-- Group sections -->
        <div
          v-for="(items, group) in configs"
          :key="group"
          class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden"
        >
          <div class="px-5 py-4 border-b border-neutral-700">
            <h3 class="text-white font-semibold text-lg capitalize">{{ group }}</h3>
          </div>
          <div class="p-5 space-y-4">
            <div v-for="config in items" :key="config.key">
              <label class="block text-sm font-medium text-neutral-300 mb-1.5">{{ config.label }}</label>
              <p v-if="config.description" class="text-neutral-500 text-xs mb-2">{{ config.description }}</p>

              <!-- Boolean toggle -->
              <label v-if="config.type === 'boolean'" class="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  :checked="localValues[config.key] === 'true'"
                  @change="localValues[config.key] = $event.target.checked ? 'true' : 'false'"
                  class="rounded bg-neutral-700 border-neutral-600 text-green-600 focus:ring-green-500"
                />
                <span class="text-neutral-300 text-sm">{{ localValues[config.key] === 'true' ? 'Activado' : 'Desactivado' }}</span>
              </label>

              <!-- Select -->
              <select
                v-else-if="config.type === 'select'"
                v-model="localValues[config.key]"
                class="w-full sm:w-64 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option v-for="opt in selectOptions(config.key)" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>

              <!-- Number/string -->
              <input
                v-else
                v-model="localValues[config.key]"
                :type="config.type === 'number' ? 'number' : 'text'"
                :step="config.type === 'number' ? '0.01' : undefined"
                class="w-full sm:w-64 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              />
            </div>
          </div>
        </div>

        <!-- Save -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2.5 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors"
          >
            Guardar configuración
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  configs: { type: Object, default: () => ({}) },
})

// Build flat key→value map for local editing
const localValues = ref(
  Object.values(props.configs).flat().reduce((acc, c) => {
    acc[c.key] = c.value
    return acc
  }, {})
)

const form = useForm({})

function selectOptions(key) {
  const map = {
    billing_rounding: [
      { value: 'fraction', label: 'Fracción exacta (ej. 45 min = $37.50)' },
      { value: 'hour', label: 'Hora completa (siempre redondear arriba)' },
    ],
  }
  return map[key] ?? []
}

function save() {
  const configs = Object.entries(localValues.value).map(([key, value]) => ({ key, value: String(value) }))

  form.transform(() => ({ configs })).post(route('admin.config.update'), {
    preserveScroll: true,
  })
}
</script>
