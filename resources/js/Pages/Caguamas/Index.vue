<template>
  <AppLayout title="Control de Caguamas">
    <template #header-actions>
      <button
        @click="showOpenModal = true"
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors duration-150"
      >
        <PlusIcon class="w-4 h-4" />
        Abrir caguama
      </button>
    </template>

    <div class="p-6 space-y-6">
      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.errors?.caguama" class="flex items-start gap-3 p-4 bg-red-900/30 border border-red-700/50 rounded-lg">
        <span class="text-red-400">✕</span>
        <p class="text-red-300 text-sm">{{ $page.props.errors.caguama }}</p>
      </div>

      <!-- Header stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-4">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Abiertas</p>
          <p class="text-3xl font-bold text-white mt-1">{{ caguamas.length }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-4">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Con suficiente</p>
          <p class="text-3xl font-bold text-green-400 mt-1">{{ caguamasConSuficiente }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-amber-700/30 p-4">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Recetas activas</p>
          <p class="text-3xl font-bold text-white mt-1">{{ recipes.length }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-4">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Receta por defecto</p>
          <p class="text-sm font-semibold text-amber-400 mt-2">{{ recipes[0]?.name ?? '—' }}</p>
        </div>
      </div>

      <!-- Active caguamas -->
      <div v-if="caguamas.length === 0" class="text-center py-16">
        <BeakerIcon class="w-12 h-12 text-neutral-600 mx-auto mb-3" />
        <p class="text-neutral-400">No hay caguamas abiertas</p>
        <button @click="showOpenModal = true" class="mt-4 text-green-400 text-sm hover:text-green-300">
          Abrir primera caguama →
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="c in caguamas"
          :key="c.id"
          class="bg-neutral-800 rounded-xl border p-5 space-y-4"
          :class="c.hours_open > 8 ? 'border-amber-700/50' : 'border-neutral-700'"
        >
          <!-- Header -->
          <div class="flex items-start justify-between">
            <div>
              <p class="text-white font-semibold text-lg">Caguama #{{ c.id }}</p>
              <p class="text-neutral-400 text-xs mt-0.5">{{ c.pours_count }} servicios · {{ c.hours_open }}h abierta</p>
            </div>
            <div class="flex flex-col items-end gap-1">
              <span v-if="c.hours_open > 8" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-900/50 text-amber-400">
                ⚠ Mucho tiempo
              </span>
              <span class="text-neutral-400 text-xs">por {{ c.opened_by }}</span>
            </div>
          </div>

          <!-- Progress bar -->
          <div>
            <div class="flex justify-between text-xs mb-1.5">
              <span class="text-neutral-300 font-medium">{{ c.remaining_volume_ml }} mL restantes</span>
              <span class="text-neutral-500">de {{ c.total_volume_ml }} mL</span>
            </div>
            <div class="h-3 bg-neutral-700 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-300"
                :class="c.remaining_pct > 30 ? 'bg-green-600' : c.remaining_pct > 10 ? 'bg-amber-500' : 'bg-red-600'"
                :style="{ width: c.remaining_pct + '%' }"
              />
            </div>
          </div>

          <!-- Tarros posibles por receta -->
          <div class="grid grid-cols-2 gap-2">
            <div
              v-for="recipe in recipes"
              :key="recipe.id"
              class="bg-neutral-750 rounded-lg p-2.5 text-center"
            >
              <p class="text-neutral-400 text-xs truncate">{{ recipe.name }}</p>
              <p class="text-white font-bold text-lg mt-0.5">{{ tarrosPosibles(c, recipe) }}</p>
              <p class="text-neutral-500 text-xs">tarros posibles</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 pt-1">
            <button
              @click="openPourModal(c)"
              :disabled="!recipes.length || c.remaining_volume_ml === 0"
              class="flex-1 px-3 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-lg transition-colors"
            >
              Servir michelada
            </button>
            <button
              @click="closeCaguama(c)"
              class="px-3 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-300 font-semibold text-sm rounded-lg transition-colors"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Open caguama modal -->
    <div v-if="showOpenModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-sm shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">Abrir caguama</h2>
          <button @click="showOpenModal = false" class="text-neutral-400 hover:text-white transition-colors">✕</button>
        </div>
        <form @submit.prevent="submitOpen" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Notas (opcional)</label>
            <input
              v-model="openForm.notes"
              type="text"
              placeholder="Ej. Corona fría"
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white placeholder-neutral-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
          </div>
          <div class="flex gap-3 pt-1">
            <button type="button" @click="showOpenModal = false" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
              Cancelar
            </button>
            <button type="submit" :disabled="openForm.processing" class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors">
              Abrir (1,200 mL)
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Pour modal -->
    <div v-if="showPourModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">Servir michelada</h2>
          <button @click="showPourModal = false" class="text-neutral-400 hover:text-white transition-colors">✕</button>
        </div>
        <form @submit.prevent="submitPour" class="px-6 py-5 space-y-4">
          <p class="text-neutral-400 text-sm">Caguama #{{ selectedCaguama?.id }} — {{ selectedCaguama?.remaining_volume_ml }} mL restantes</p>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Receta / vaso</label>
            <select
              v-model="pourForm.michelada_recipe_id"
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option v-for="r in recipes" :key="r.id" :value="r.id">
                {{ r.name }} ({{ r.container_volume_ml ?? r.beer_volume_ml }} mL)
              </option>
            </select>
          </div>

          <!-- Add-ins -->
          <div v-if="addIns.length">
            <label class="block text-sm font-medium text-neutral-300 mb-2">Aditamentos</label>
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="addIn in addIns"
                :key="addIn.id"
                class="flex items-center gap-2 bg-neutral-750 rounded-lg px-3 py-2 cursor-pointer hover:bg-neutral-700 transition-colors"
                :class="pourForm.add_in_ids.includes(addIn.id) ? 'border border-amber-600/60' : 'border border-neutral-600'"
              >
                <input type="checkbox" :value="addIn.id" v-model="pourForm.add_in_ids" class="accent-amber-500" />
                <span class="text-white text-sm flex-1">{{ addIn.name }}</span>
                <span v-if="addIn.volume_ml > 0" class="text-neutral-400 text-xs">{{ addIn.volume_ml }}mL</span>
              </label>
            </div>
          </div>

          <!-- Volume preview -->
          <div class="bg-neutral-750 rounded-xl p-3 border border-neutral-600 text-sm space-y-1">
            <div class="flex justify-between">
              <span class="text-neutral-400">Capacidad vaso</span>
              <span class="text-white">{{ selectedRecipe?.container_volume_ml ?? selectedRecipe?.beer_volume_ml ?? 0 }} mL</span>
            </div>
            <div v-if="pourDisplacedVolume > 0" class="flex justify-between">
              <span class="text-neutral-400">Aditamentos</span>
              <span class="text-neutral-300">− {{ pourDisplacedVolume }} mL</span>
            </div>
            <div class="flex justify-between font-semibold border-t border-neutral-600 pt-1">
              <span class="text-neutral-300">Cerveza a descontar</span>
              <span class="text-amber-400">{{ pourBeerVolume }} mL</span>
            </div>
          </div>

          <p v-if="selectedRecipe && selectedCaguama && selectedCaguama.remaining_volume_ml < pourBeerVolume" class="text-red-400 text-xs">
            Remanente insuficiente — quedan {{ selectedCaguama.remaining_volume_ml }} mL.
          </p>

          <div class="flex gap-3 pt-1">
            <button type="button" @click="showPourModal = false" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="pourForm.processing || !canPourSelected"
              class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors"
            >
              Servir
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon, BeakerIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  caguamas: { type: Array, default: () => [] },
  recipes: { type: Array, default: () => [] },
  addIns: { type: Array, default: () => [] },
})

const showOpenModal = ref(false)
const showPourModal = ref(false)
const selectedCaguama = ref(null)

const openForm = useForm({ notes: '' })
const pourForm = useForm({
  michelada_recipe_id: null,
  add_in_ids: [],
})

const caguamasConSuficiente = computed(() => {
  if (!props.recipes.length) return 0
  const minVol = Math.min(...props.recipes.map(r => r.container_volume_ml ?? r.beer_volume_ml))
  return props.caguamas.filter(c => c.remaining_volume_ml >= minVol).length
})

const selectedRecipe = computed(() =>
  props.recipes.find(r => r.id === pourForm.michelada_recipe_id)
)

const pourDisplacedVolume = computed(() =>
  props.addIns
    .filter(a => pourForm.add_in_ids.includes(a.id))
    .reduce((sum, a) => sum + a.volume_ml, 0)
)

const pourBeerVolume = computed(() => {
  if (!selectedRecipe.value) return 0
  const containerVol = selectedRecipe.value.container_volume_ml ?? selectedRecipe.value.beer_volume_ml ?? 0
  return Math.max(0, containerVol - pourDisplacedVolume.value)
})

const canPourSelected = computed(() => {
  if (!selectedCaguama.value || !selectedRecipe.value) return false
  return selectedCaguama.value.remaining_volume_ml >= pourBeerVolume.value
})

function tarrosPosibles(caguama, recipe) {
  const vol = recipe.container_volume_ml ?? recipe.beer_volume_ml
  if (!vol) return 0
  return Math.floor(caguama.remaining_volume_ml / vol)
}

function openPourModal(caguama) {
  selectedCaguama.value = caguama
  pourForm.michelada_recipe_id = props.recipes[0]?.id ?? null
  pourForm.add_in_ids = []
  showPourModal.value = true
}

function submitOpen() {
  openForm.post(route('caguamas.open'), {
    onSuccess: () => {
      showOpenModal.value = false
      openForm.reset()
    },
  })
}

function submitPour() {
  pourForm.post(route('caguamas.pour', selectedCaguama.value.id), {
    onSuccess: () => {
      showPourModal.value = false
      pourForm.reset()
    },
  })
}

function closeCaguama(caguama) {
  if (!confirm(`¿Cerrar caguama #${caguama.id}? Remanente: ${caguama.remaining_volume_ml} mL`)) return
  router.post(route('caguamas.close', caguama.id))
}
</script>
