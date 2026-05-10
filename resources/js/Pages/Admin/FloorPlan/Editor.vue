<template>
  <AppLayout title="Editor de Mapa">
    <template #header-actions>
      <button
        @click="savePositions"
        :disabled="saving"
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors"
      >
        {{ saving ? 'Guardando...' : 'Guardar posiciones' }}
      </button>
    </template>

    <div class="p-6 space-y-5">

      <!-- Flash -->
      <div v-if="flashSuccess" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ flashSuccess }}</p>
      </div>
      <div v-if="saveError" class="flex items-start gap-3 p-4 bg-red-900/30 border border-red-700/50 rounded-lg">
        <span class="text-red-400">✗</span>
        <p class="text-red-300 text-sm">Error al guardar posiciones: {{ saveError }}</p>
      </div>

      <!-- Canvas settings -->
      <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
        <h3 class="text-white font-semibold mb-4">Configuración del canvas</h3>
        <form @submit.prevent="saveConfig" class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Ancho (px)</label>
            <input v-model.number="configForm.canvas_width" type="number" min="400" max="3000"
              class="w-28 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Alto (px)</label>
            <input v-model.number="configForm.canvas_height" type="number" min="300" max="2000"
              class="w-28 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Imagen de fondo</label>
            <input
              ref="bgFileInput"
              type="file"
              accept="image/*"
              class="text-sm text-neutral-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-neutral-700 file:text-neutral-200 file:text-xs hover:file:bg-neutral-600"
              @change="onBgFileChange"
            />
          </div>
          <button type="submit" :disabled="configForm.processing" class="px-4 py-2 bg-neutral-700 hover:bg-neutral-600 disabled:opacity-50 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
            Guardar config
          </button>
        </form>
      </div>

      <!-- Instructions -->
      <p class="text-neutral-500 text-sm">
        Arrastra las mesas para posicionarlas. Los cambios se guardan con el botón superior.
      </p>

      <!-- Konva editor canvas -->
      <div class="bg-neutral-900 rounded-xl border border-neutral-700 overflow-auto">
        <v-stage ref="stageRef" :config="stageConfig">
          <v-layer>
            <!-- Background -->
            <v-rect :config="{ width: configForm.canvas_width, height: configForm.canvas_height, fill: '#111111' }" />
            <v-image v-if="bgImage" :config="{ image: bgImage, width: configForm.canvas_width, height: configForm.canvas_height, opacity: 0.25 }" />

            <!-- Tables — draggable -->
            <template v-for="table in localTables" :key="table.id">
              <v-rect
                :config="tableDragRect(table)"
                @dragend="(e) => onDragEnd(table, e)"
              />
              <v-text :config="tableLabel(table)" />
            </template>
          </v-layer>
        </v-stage>
      </div>

      <!-- Tables list for size editing -->
      <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-700">
          <h3 class="text-white font-semibold">Tamaño y posición exacta</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-neutral-900">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Mesa</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">X</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Y</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ancho</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Alto</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-700">
              <tr v-for="table in localTables" :key="table.id" class="hover:bg-neutral-750 transition-colors">
                <td class="px-4 py-3 text-neutral-200 font-medium">{{ table.name }}</td>
                <td class="px-4 py-3">
                  <input v-model.number="table.map_x" type="number" min="0" class="w-20 bg-neutral-700 border border-neutral-600 rounded px-2 py-1 text-white text-xs text-right focus:outline-none focus:ring-1 focus:ring-green-500" />
                </td>
                <td class="px-4 py-3">
                  <input v-model.number="table.map_y" type="number" min="0" class="w-20 bg-neutral-700 border border-neutral-600 rounded px-2 py-1 text-white text-xs text-right focus:outline-none focus:ring-1 focus:ring-green-500" />
                </td>
                <td class="px-4 py-3">
                  <input v-model.number="table.map_width" type="number" min="20" class="w-20 bg-neutral-700 border border-neutral-600 rounded px-2 py-1 text-white text-xs text-right focus:outline-none focus:ring-1 focus:ring-green-500" />
                </td>
                <td class="px-4 py-3">
                  <input v-model.number="table.map_height" type="number" min="20" class="w-20 bg-neutral-700 border border-neutral-600 rounded px-2 py-1 text-white text-xs text-right focus:outline-none focus:ring-1 focus:ring-green-500" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  config: { type: Object, default: () => ({ canvas_width: 1200, canvas_height: 700, background_image_url: null }) },
  tables: { type: Array, default: () => [] },
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)

// Local mutable copies
const localTables = ref(props.tables.map(t => ({ ...t, map_x: t.map_x ?? 50, map_y: t.map_y ?? 50 })))

const saving = ref(false)
const bgImage = ref(null)
const bgFileInput = ref(null)
const stageRef = ref(null)

const configForm = useForm({
  canvas_width: props.config.canvas_width,
  canvas_height: props.config.canvas_height,
  background_image: null,
})

const stageConfig = computed(() => ({
  width: configForm.canvas_width,
  height: configForm.canvas_height,
}))

// Load initial background
if (props.config.background_image_url) {
  const img = new Image()
  img.onload = () => { bgImage.value = img }
  img.src = props.config.background_image_url
}

function tableDragRect(table) {
  return {
    x: table.map_x,
    y: table.map_y,
    width: table.map_width,
    height: table.map_height,
    fill: table.is_active ? '#15803d' : '#404040',
    stroke: '#4ade80',
    strokeWidth: 1,
    cornerRadius: 8,
    opacity: 0.9,
    draggable: true,
  }
}

function tableLabel(table) {
  return {
    x: table.map_x,
    y: table.map_y + table.map_height / 2 - 8,
    text: table.name,
    width: table.map_width,
    align: 'center',
    fontSize: 13,
    fontStyle: 'bold',
    fill: '#ffffff',
    fontFamily: 'Inter, system-ui, sans-serif',
    listening: false,
  }
}

function onDragEnd(table, e) {
  table.map_x = Math.round(e.target.x())
  table.map_y = Math.round(e.target.y())
}

const saveError = ref(null)

async function savePositions() {
  saving.value = true
  saveError.value = null
  try {
    await axios.post(route('admin.floor-plan.positions'), {
      tables: localTables.value.map(t => ({
        id: t.id,
        map_x: t.map_x,
        map_y: t.map_y,
        map_width: t.map_width,
        map_height: t.map_height,
      })),
    })
  } catch (e) {
    saveError.value = e.response?.data?.message ?? e.message
  } finally {
    saving.value = false
  }
}

function onBgFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  configForm.background_image = file
  const reader = new FileReader()
  reader.onload = (ev) => {
    const img = new Image()
    img.onload = () => { bgImage.value = img }
    img.src = ev.target.result
  }
  reader.readAsDataURL(file)
}

function saveConfig() {
  configForm.post(route('admin.floor-plan.config'), {
    forceFormData: true,
  })
}
</script>
