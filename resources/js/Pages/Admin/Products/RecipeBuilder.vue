<template>
  <AppLayout :title="`Receta: ${product.name}`">
    <template #header-actions>
      <Link :href="route('admin.products.index')" class="btn-secondary text-sm">
        ← Productos
      </Link>
    </template>

    <div class="p-6 max-w-5xl mx-auto space-y-6">
      <!-- Product header -->
      <div class="bg-neutral-800 rounded-lg p-5 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-semibold">{{ product.name }}</h2>
          <p class="text-neutral-400 text-sm mt-0.5">{{ product.category?.name }}</p>
        </div>
        <div class="text-right">
          <p class="text-amber-400 text-2xl font-bold">${{ formatPrice(product.price) }}</p>
          <p class="text-neutral-400 text-xs">Precio de venta</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recipe form -->
        <div class="lg:col-span-2 space-y-4">
          <div class="bg-neutral-800 rounded-lg p-5">
            <h3 class="text-white font-medium mb-4">Ingredientes de la receta</h3>

            <div class="space-y-3">
              <div
                v-for="(ing, i) in ingredients"
                :key="i"
                class="grid grid-cols-12 gap-2 items-end"
              >
                <div class="col-span-4">
                  <label v-if="i === 0" class="label-base">Ingrediente</label>
                  <input
                    v-model="ing.name"
                    type="text"
                    class="input-base"
                    placeholder="Vodka..."
                    required
                    @input="recalculate"
                  />
                </div>
                <div class="col-span-2">
                  <label v-if="i === 0" class="label-base">Cantidad</label>
                  <input
                    v-model="ing.amount"
                    type="number"
                    step="0.001"
                    min="0.001"
                    class="input-base"
                    required
                    @input="recalculate"
                  />
                </div>
                <div class="col-span-2">
                  <label v-if="i === 0" class="label-base">Unidad</label>
                  <select v-model="ing.unit" class="input-base" @change="recalculate">
                    <option value="ml">ml</option>
                    <option value="oz">oz</option>
                    <option value="g">g</option>
                    <option value="pcs">pcs</option>
                  </select>
                </div>
                <div class="col-span-3">
                  <label v-if="i === 0" class="label-base">Costo / unidad ($)</label>
                  <input
                    v-model="ing.unit_cost"
                    type="number"
                    step="0.0001"
                    min="0"
                    class="input-base"
                    required
                    @input="recalculate"
                  />
                </div>
                <div class="col-span-1 flex justify-center">
                  <div v-if="i === 0" class="label-base invisible">x</div>
                  <button
                    type="button"
                    @click="removeIngredient(i)"
                    class="text-red-500 hover:text-red-400 p-1"
                    :disabled="ingredients.length === 1"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="addIngredient"
              class="mt-3 text-green-400 hover:text-green-300 text-sm flex items-center gap-1"
            >
              <PlusIcon class="w-4 h-4" />
              Agregar ingrediente
            </button>

            <div class="mt-4">
              <label class="label-base">Notas (opcional)</label>
              <textarea v-model="recipeNotes" class="input-base resize-none" rows="2" placeholder="Preparación, presentación..."></textarea>
            </div>
          </div>

          <!-- Modifiers section -->
          <div class="bg-neutral-800 rounded-lg p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-white font-medium">Modificadores</h3>
              <button type="button" @click="addModifier" class="text-green-400 hover:text-green-300 text-sm flex items-center gap-1">
                <PlusIcon class="w-4 h-4" />
                Añadir grupo
              </button>
            </div>

            <div v-if="modifiers.length === 0" class="text-neutral-500 text-sm text-center py-4">
              Sin modificadores. Ej: nivel de picante, extras.
            </div>

            <div v-for="(mod, mi) in modifiers" :key="mi" class="mb-4 border border-neutral-700 rounded-lg p-4">
              <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                  <label class="label-base">Nombre del grupo</label>
                  <input v-model="mod.name" type="text" class="input-base" placeholder="Nivel de picante" required />
                </div>
                <div>
                  <label class="label-base">Tipo</label>
                  <select v-model="mod.type" class="input-base">
                    <option value="single">Elegir uno</option>
                    <option value="multiple">Elegir varios</option>
                  </select>
                </div>
              </div>
              <div class="flex items-center justify-between mb-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="mod.is_required" type="checkbox" class="checkbox-base" />
                  <span class="text-neutral-300 text-sm">Obligatorio</span>
                </label>
                <button type="button" @click="removeModifier(mi)" class="text-red-500 hover:text-red-400 text-xs">
                  Eliminar grupo
                </button>
              </div>

              <div class="space-y-2">
                <div
                  v-for="(opt, oi) in mod.options"
                  :key="oi"
                  class="grid grid-cols-8 gap-2 items-center"
                >
                  <div class="col-span-5">
                    <input v-model="opt.name" type="text" class="input-base text-sm" placeholder="Sin picante..." required />
                  </div>
                  <div class="col-span-2">
                    <input v-model="opt.extra_cost" type="number" step="0.01" min="0" class="input-base text-sm" placeholder="+$0" />
                  </div>
                  <div class="col-span-1 flex justify-center">
                    <button type="button" @click="removeOption(mod, oi)" class="text-red-500 hover:text-red-400" :disabled="mod.options.length === 1">
                      <XMarkIcon class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
              <button type="button" @click="addOption(mod)" class="mt-2 text-green-400 hover:text-green-300 text-xs flex items-center gap-1">
                <PlusIcon class="w-3 h-3" />
                Agregar opción
              </button>
            </div>
          </div>

          <!-- Save buttons -->
          <div class="flex gap-3">
            <button @click="saveRecipe" :disabled="savingRecipe" class="btn-primary flex-1">
              {{ savingRecipe ? 'Guardando...' : 'Guardar receta' }}
            </button>
            <button v-if="modifiers.length" @click="saveModifiers" :disabled="savingModifiers" class="btn-secondary flex-1">
              {{ savingModifiers ? 'Guardando...' : 'Guardar modificadores' }}
            </button>
          </div>

          <p v-if="saveSuccess" class="text-green-400 text-sm text-center">{{ saveSuccess }}</p>
          <p v-if="saveError" class="text-red-400 text-sm text-center">{{ saveError }}</p>
        </div>

        <!-- Cost breakdown sidebar -->
        <div class="space-y-4">
          <div class="bg-neutral-800 rounded-lg p-5 sticky top-4">
            <h3 class="text-white font-medium mb-4">Costo estimado</h3>

            <div class="space-y-2">
              <div
                v-for="ing in ingredients"
                :key="ing.name"
                class="flex justify-between text-sm"
              >
                <span class="text-neutral-400 truncate">{{ ing.name || '—' }}</span>
                <span class="text-neutral-300 shrink-0 ml-2">${{ calcLineCost(ing) }}</span>
              </div>
            </div>

            <div class="border-t border-neutral-700 mt-4 pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-neutral-400">Costo receta</span>
                <span class="text-white">${{ formatPrice(totalCost) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-neutral-400">Precio venta</span>
                <span class="text-amber-400">${{ formatPrice(product.price) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-neutral-400">Margen</span>
                <span :class="margin >= 0 ? 'text-green-400' : 'text-red-400'">${{ formatPrice(margin) }}</span>
              </div>
              <div class="flex justify-between font-semibold">
                <span class="text-neutral-300">Margen %</span>
                <span :class="marginPct >= 50 ? 'text-green-400' : marginPct >= 25 ? 'text-amber-400' : 'text-red-400'">
                  {{ marginPct }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  product: Object,
  breakdown: Object,
})

const formatPrice = (val) => Number(val ?? 0).toFixed(2)

// ── Ingredients ─────────────────────────────────────────────
const ingredients = ref(
  props.product.recipe?.ingredients?.length
    ? props.product.recipe.ingredients.map(i => ({ ...i }))
    : [{ name: '', amount: '', unit: 'ml', unit_cost: '' }]
)
const recipeNotes = ref(props.product.recipe?.notes ?? '')

function addIngredient() {
  ingredients.value.push({ name: '', amount: '', unit: 'ml', unit_cost: '' })
}

function removeIngredient(i) {
  if (ingredients.value.length > 1) ingredients.value.splice(i, 1)
}

// ── Cost calculation ─────────────────────────────────────────
const calcLineCost = (ing) => {
  const val = (parseFloat(ing.amount) || 0) * (parseFloat(ing.unit_cost) || 0)
  return formatPrice(val)
}

const totalCost = computed(() =>
  ingredients.value.reduce((sum, ing) => sum + (parseFloat(ing.amount) || 0) * (parseFloat(ing.unit_cost) || 0), 0)
)

const margin = computed(() => (parseFloat(props.product.price) || 0) - totalCost.value)

const marginPct = computed(() => {
  const price = parseFloat(props.product.price) || 0
  if (price === 0) return 0
  return Math.round((margin.value / price) * 100 * 10) / 10
})

function recalculate() {}

// ── Modifiers ─────────────────────────────────────────────
const modifiers = ref(
  props.product.modifiers?.length
    ? props.product.modifiers.map(m => ({
        ...m,
        options: m.options.map(o => ({ ...o })),
      }))
    : []
)

function addModifier() {
  modifiers.value.push({
    name: '',
    type: 'single',
    is_required: false,
    sort_order: modifiers.value.length,
    options: [{ name: '', extra_cost: 0, sort_order: 0 }],
  })
}

function removeModifier(i) {
  modifiers.value.splice(i, 1)
}

function addOption(mod) {
  mod.options.push({ name: '', extra_cost: 0, sort_order: mod.options.length })
}

function removeOption(mod, oi) {
  if (mod.options.length > 1) mod.options.splice(oi, 1)
}

// ── Save ──────────────────────────────────────────────────
const savingRecipe = ref(false)
const savingModifiers = ref(false)
const saveSuccess = ref('')
const saveError = ref('')

function clearMessages() {
  saveSuccess.value = ''
  saveError.value = ''
}

async function saveRecipe() {
  clearMessages()
  savingRecipe.value = true

  router.post(route('admin.products.recipe.save', props.product.id), {
    notes: recipeNotes.value,
    ingredients: ingredients.value.map((ing, i) => ({
      name: ing.name,
      amount: parseFloat(ing.amount),
      unit: ing.unit,
      unit_cost: parseFloat(ing.unit_cost),
      sort_order: i,
    })),
  }, {
    onSuccess: () => { saveSuccess.value = 'Receta guardada.' },
    onError: () => { saveError.value = 'Error al guardar receta.' },
    onFinish: () => { savingRecipe.value = false },
  })
}

async function saveModifiers() {
  clearMessages()
  savingModifiers.value = true

  router.post(route('admin.products.modifiers.save', props.product.id), {
    modifiers: modifiers.value.map((m, mi) => ({
      name: m.name,
      type: m.type,
      is_required: m.is_required,
      sort_order: mi,
      options: m.options.map((o, oi) => ({
        name: o.name,
        extra_cost: parseFloat(o.extra_cost) || 0,
        sort_order: oi,
      })),
    })),
  }, {
    onSuccess: () => { saveSuccess.value = 'Modificadores guardados.' },
    onError: () => { saveError.value = 'Error al guardar modificadores.' },
    onFinish: () => { savingModifiers.value = false },
  })
}
</script>
