<template>
  <AppLayout title="Inventario">
    <div class="p-6 space-y-6">

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>

      <!-- Controls -->
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar producto..."
          class="w-full sm:w-72 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white placeholder-neutral-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
        />
        <label class="flex items-center gap-2 text-sm text-neutral-300 cursor-pointer select-none">
          <input v-model="showLowOnly" type="checkbox" class="rounded bg-neutral-700 border-neutral-600 text-green-600 focus:ring-green-500" />
          Solo stock bajo
        </label>
        <div class="sm:ml-auto text-neutral-500 text-sm self-center">
          {{ filteredProducts.length }} productos
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-neutral-700">
        <table class="w-full text-sm">
          <thead class="bg-neutral-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Producto</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider hidden sm:table-cell">Categoría</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Stock</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider hidden md:table-cell">Mínimo</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-400 uppercase tracking-wider">Estado</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-700 bg-neutral-800">
            <tr
              v-for="p in filteredProducts"
              :key="p.id"
              class="hover:bg-neutral-750 transition-colors"
              :class="p.is_low ? 'bg-amber-900/10' : ''"
            >
              <td class="px-4 py-3 text-neutral-200 font-medium">{{ p.name }}</td>
              <td class="px-4 py-3 text-neutral-400 hidden sm:table-cell">{{ p.category ?? '—' }}</td>
              <td class="px-4 py-3 text-right font-semibold" :class="p.is_low ? 'text-amber-400' : 'text-white'">
                {{ p.quantity }} <span class="text-neutral-500 font-normal text-xs">{{ p.unit }}</span>
              </td>
              <td class="px-4 py-3 text-right text-neutral-500 hidden md:table-cell">
                {{ p.min_stock }} <span class="text-xs">{{ p.unit }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span v-if="p.is_low" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-900/50 text-amber-400">
                  ⚠ Bajo
                </span>
                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-400">
                  OK
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="openAdjust(p)"
                  class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 text-xs font-semibold rounded-lg border border-neutral-600 transition-colors"
                >
                  Ajustar
                </button>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-neutral-500">Sin resultados</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Adjust modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">Ajustar — {{ adjusting?.name }}</h2>
          <button @click="showAdjustModal = false" class="text-neutral-400 hover:text-white transition-colors">✕</button>
        </div>
        <form @submit.prevent="submitAdjust" class="px-6 py-5 space-y-4">
          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Tipo de movimiento</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="t in movTypes"
                :key="t.value"
                type="button"
                @click="adjustForm.type = t.value"
                class="py-2 rounded-lg border text-sm font-medium transition-colors"
                :class="adjustForm.type === t.value
                  ? 'border-green-600 bg-green-900/40 text-green-400'
                  : 'border-neutral-600 bg-neutral-700 text-neutral-300 hover:bg-neutral-600'"
              >
                {{ t.label }}
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">
              {{ adjustForm.type === 'ajuste' ? 'Nuevo stock total' : 'Cantidad' }}
            </label>
            <div class="flex gap-2">
              <input
                v-model.number="adjustForm.quantity"
                type="number"
                min="0"
                step="0.001"
                required
                class="flex-1 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              />
              <select
                v-model="adjustForm.unit"
                class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
              </select>
            </div>
          </div>

          <!-- Min stock -->
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Stock mínimo (alerta)</label>
            <input
              v-model.number="adjustForm.min_stock"
              type="number"
              min="0"
              step="0.001"
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Notas</label>
            <input
              v-model="adjustForm.notes"
              type="text"
              placeholder="Motivo del ajuste..."
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white placeholder-neutral-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
          </div>

          <div v-if="adjustForm.errors?.quantity" class="text-xs text-red-400">{{ adjustForm.errors.quantity }}</div>

          <div class="flex gap-3 pt-1">
            <button type="button" @click="showAdjustModal = false" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
              Cancelar
            </button>
            <button type="submit" :disabled="adjustForm.processing" class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  products: { type: Array, default: () => [] },
})

const search = ref('')
const showLowOnly = ref(false)
const showAdjustModal = ref(false)
const adjusting = ref(null)

const units = ['piezas', 'ml', 'oz', 'g', 'kg', 'litros']
const movTypes = [
  { value: 'entrada', label: 'Entrada' },
  { value: 'ajuste', label: 'Ajuste' },
  { value: 'merma', label: 'Merma' },
]

const adjustForm = useForm({
  quantity: 0,
  unit: 'piezas',
  min_stock: 0,
  type: 'entrada',
  notes: '',
})

const filteredProducts = computed(() => {
  let list = props.products
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(p => p.name.toLowerCase().includes(q) || (p.category ?? '').toLowerCase().includes(q))
  }
  if (showLowOnly.value) {
    list = list.filter(p => p.is_low)
  }
  return list
})

function openAdjust(product) {
  adjusting.value = product
  adjustForm.reset()
  adjustForm.quantity = product.quantity
  adjustForm.unit = product.unit
  adjustForm.min_stock = product.min_stock
  adjustForm.type = 'ajuste'
  showAdjustModal.value = true
}

function submitAdjust() {
  adjustForm.post(route('admin.inventory.adjust', adjusting.value.id), {
    onSuccess: () => {
      showAdjustModal.value = false
    },
  })
}
</script>
