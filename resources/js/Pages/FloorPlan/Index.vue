<template>
  <AppLayout title="Mapa del local">
    <div class="p-6 space-y-4">
      <!-- Legend -->
      <div class="flex items-center gap-6 text-xs text-neutral-400">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-600 inline-block"></span>Disponible</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-600 inline-block"></span>Ocupada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-neutral-600 inline-block"></span>Inactiva</span>
        <span class="ml-auto text-neutral-600">Actualiza cada 10s</span>
      </div>

      <!-- Canvas container -->
      <div class="bg-neutral-900 rounded-xl border border-neutral-700 overflow-auto">
        <v-stage
          :config="stageConfig"
          @click="onStageClick"
        >
          <v-layer>
            <!-- Background image if set -->
            <v-image v-if="bgImage" :config="bgImageConfig" />

            <!-- Tables -->
            <v-group
              v-for="table in tables"
              :key="table.id"
              :config="{ x: table.map_x, y: table.map_y }"
            >
              <v-rect :config="tableRectConfig(table)" @click="() => onTableClick(table)" />
              <v-text :config="tableNameConfig(table)" />
              <v-text v-if="table.status === 'occupied'" :config="tableTimerConfig(table)" />
            </v-group>
          </v-layer>
        </v-stage>
      </div>
    </div>

    <!-- Session action modal -->
    <div v-if="selectedTable" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">{{ selectedTable.name }}</h2>
          <button @click="selectedTable = null" class="text-neutral-400 hover:text-white">✕</button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <!-- Available: open session -->
          <template v-if="selectedTable.status === 'available'">
            <p class="text-neutral-400 text-sm">Mesa disponible. ¿Abrir sesión?</p>
            <div>
              <label class="block text-sm font-medium text-neutral-300 mb-1.5">Tipo de cobro</label>
              <select v-model="openForm.billing_type" class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <option value="por_hora">Por hora</option>
                <option value="precio_fijo">Precio fijo</option>
              </select>
            </div>
            <div class="flex gap-3">
              <button @click="selectedTable = null" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
                Cancelar
              </button>
              <button @click="openSession" :disabled="openForm.processing" class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors">
                Abrir sesión
              </button>
            </div>
          </template>

          <!-- Occupied: view session -->
          <template v-else>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-neutral-400">Tiempo abierta</span>
                <span class="text-white font-semibold">{{ formatMinutes(selectedTable.minutes_open) }}</span>
              </div>
            </div>
            <div class="flex gap-3">
              <button @click="selectedTable = null" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
                Cerrar
              </button>
              <a
                :href="route('tables.sessions.show', selectedTable.session_id)"
                class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors text-center"
              >
                Ver cuenta
              </a>
            </div>
          </template>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  config: { type: Object, default: () => ({ canvas_width: 1200, canvas_height: 700, background_image_path: null }) },
  tables: { type: Array, default: () => [] },
})

const tables = ref([...props.tables])
const selectedTable = ref(null)
const bgImage = ref(null)
let pollInterval = null

const stageConfig = computed(() => ({
  width: props.config.canvas_width,
  height: props.config.canvas_height,
}))

const bgImageConfig = computed(() => ({
  image: bgImage.value,
  width: props.config.canvas_width,
  height: props.config.canvas_height,
  opacity: 0.3,
}))

const openForm = useForm({ billing_type: 'por_hora' })

function tableColor(table) {
  if (table.status === 'occupied') return '#b91c1c'
  if (table.status === 'available') return '#15803d'
  return '#404040'
}

function tableRectConfig(table) {
  return {
    width: table.map_width,
    height: table.map_height,
    fill: tableColor(table),
    cornerRadius: 8,
    shadowColor: tableColor(table),
    shadowBlur: table.status === 'occupied' ? 12 : 4,
    shadowOpacity: 0.4,
    cursor: 'pointer',
  }
}

function tableNameConfig(table) {
  return {
    text: table.name,
    width: table.map_width,
    align: 'center',
    y: table.status === 'occupied' ? table.map_height / 2 - 16 : table.map_height / 2 - 8,
    fontSize: 13,
    fontStyle: 'bold',
    fill: '#ffffff',
    fontFamily: 'Inter, system-ui, sans-serif',
    listening: false,
  }
}

function tableTimerConfig(table) {
  return {
    text: formatMinutes(table.minutes_open),
    width: table.map_width,
    align: 'center',
    y: table.map_height / 2 + 4,
    fontSize: 11,
    fill: '#fca5a5',
    fontFamily: 'Inter, system-ui, sans-serif',
    listening: false,
  }
}

function formatMinutes(mins) {
  if (!mins && mins !== 0) return ''
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return h > 0 ? `${h}h ${m}m` : `${m}m`
}

function onStageClick() {}

function onTableClick(table) {
  if (table.status === 'inactive') return
  selectedTable.value = table
}

function openSession() {
  openForm.post(route('tables.open', selectedTable.value.id), {
    onSuccess: () => { selectedTable.value = null },
  })
}

async function poll() {
  try {
    const res = await fetch(route('floor-plan.poll'))
    const data = await res.json()
    tables.value = data.tables
  } catch {
    // silent
  }
}

onMounted(() => {
  // Load background image if set
  if (props.config.background_image_path) {
    const img = new Image()
    img.onload = () => { bgImage.value = img }
    img.src = props.config.background_image_path
  }

  pollInterval = setInterval(poll, 10000)
})

onUnmounted(() => clearInterval(pollInterval))
</script>
