<template>
  <AppLayout title="Catálogo de Productos">
    <template #header-actions>
      <button @click="openCategoryModal()" class="btn-secondary text-sm">
        <PlusIcon class="w-4 h-4 mr-1" />
        Categoría
      </button>
      <button @click="openProductModal()" class="btn-primary text-sm">
        <PlusIcon class="w-4 h-4 mr-1" />
        Producto
      </button>
    </template>

    <div class="p-6 space-y-6">
      <!-- Stats row -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-neutral-800 rounded-lg p-4">
          <p class="text-neutral-400 text-xs uppercase tracking-wide">Total productos</p>
          <p class="text-2xl font-bold text-white mt-1">{{ products.length }}</p>
        </div>
        <div class="bg-neutral-800 rounded-lg p-4">
          <p class="text-neutral-400 text-xs uppercase tracking-wide">Categorías</p>
          <p class="text-2xl font-bold text-white mt-1">{{ categories.length }}</p>
        </div>
        <div class="bg-neutral-800 rounded-lg p-4">
          <p class="text-neutral-400 text-xs uppercase tracking-wide">Con receta</p>
          <p class="text-2xl font-bold text-green-400 mt-1">{{ products.filter(p => p.has_recipe).length }}</p>
        </div>
      </div>

      <!-- Filter tabs -->
      <div class="flex gap-2 flex-wrap">
        <button
          v-for="cat in [{ id: null, name: 'Todos', icon: '' }, ...categories]"
          :key="cat.id ?? 'all'"
          @click="activeCategory = cat.id"
          :class="[
            'px-3 py-1.5 rounded-full text-sm font-medium transition-colors',
            activeCategory === cat.id
              ? 'bg-green-700 text-white'
              : 'bg-neutral-800 text-neutral-300 hover:bg-neutral-700'
          ]"
        >
          <span v-if="cat.icon" class="mr-1">{{ cat.icon }}</span>
          {{ cat.name }}
          <span v-if="cat.id" class="ml-1 text-xs opacity-60">({{ cat.product_count }})</span>
        </button>
      </div>

      <!-- Product grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="bg-neutral-800 rounded-lg border border-neutral-700 hover:border-neutral-600 transition-colors"
        >
          <div class="p-4">
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-white font-medium truncate">{{ product.name }}</h3>
                  <span v-if="product.is_beer_product" class="text-xs bg-amber-900 text-amber-300 px-1.5 py-0.5 rounded">🍺 Caguama</span>
                  <span v-if="product.has_recipe" class="text-xs bg-green-900 text-green-400 px-1.5 py-0.5 rounded">Receta</span>
                  <span v-if="!product.is_active" class="text-xs bg-neutral-700 text-neutral-400 px-1.5 py-0.5 rounded">Inactivo</span>
                </div>
                <p class="text-neutral-400 text-xs mt-0.5">
                  <span v-if="product.category">{{ product.category.icon }} {{ product.category.name }}</span>
                </p>
              </div>
              <p class="text-amber-400 font-semibold text-lg ml-3 shrink-0">${{ formatPrice(product.price) }}</p>
            </div>

            <p v-if="product.description" class="text-neutral-400 text-sm mt-2 line-clamp-2">{{ product.description }}</p>

            <!-- Modifiers summary -->
            <div v-if="product.modifiers?.length" class="mt-2 flex flex-wrap gap-1">
              <span
                v-for="mod in product.modifiers"
                :key="mod.id"
                class="text-xs bg-neutral-700 text-neutral-300 px-1.5 py-0.5 rounded"
              >
                {{ mod.name }}
              </span>
            </div>
          </div>

          <div class="px-4 pb-3 flex items-center gap-2 border-t border-neutral-700 pt-3">
            <Link
              :href="route('admin.products.recipe.show', product.id)"
              class="text-xs text-green-400 hover:text-green-300 flex items-center gap-1"
            >
              <BeakerIcon class="w-3.5 h-3.5" />
              {{ product.has_recipe ? 'Ver receta' : 'Añadir receta' }}
            </Link>
            <span class="text-neutral-700">·</span>
            <button @click="openProductModal(product)" class="text-xs text-neutral-400 hover:text-white">
              Editar
            </button>
            <span class="text-neutral-700">·</span>
            <button @click="confirmDelete(product)" class="text-xs text-red-500 hover:text-red-400">
              Eliminar
            </button>
          </div>
        </div>

        <div
          v-if="filteredProducts.length === 0"
          class="col-span-full text-center py-12 text-neutral-500"
        >
          Sin productos en esta categoría.
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <Teleport to="body">
      <div v-if="showProductModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-neutral-800 rounded-xl w-full max-w-lg shadow-xl">
          <div class="flex items-center justify-between p-5 border-b border-neutral-700">
            <h2 class="text-white font-semibold text-lg">
              {{ editingProduct ? 'Editar producto' : 'Nuevo producto' }}
            </h2>
            <button @click="closeProductModal" class="text-neutral-400 hover:text-white">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitProduct" class="p-5 space-y-4">
            <div>
              <label class="label-base">Categoría</label>
              <select v-model="productForm.product_category_id" class="input-base" required>
                <option value="" disabled>Seleccionar...</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.icon }} {{ cat.name }}
                </option>
              </select>
              <p v-if="productErrors.product_category_id" class="text-red-400 text-xs mt-1">{{ productErrors.product_category_id }}</p>
            </div>

            <div>
              <label class="label-base">Nombre</label>
              <input v-model="productForm.name" type="text" class="input-base" required maxlength="150" />
              <p v-if="productErrors.name" class="text-red-400 text-xs mt-1">{{ productErrors.name }}</p>
            </div>

            <div>
              <label class="label-base">Descripción (opcional)</label>
              <textarea v-model="productForm.description" class="input-base resize-none" rows="2"></textarea>
            </div>

            <div>
              <label class="label-base">Precio (MXN)</label>
              <input v-model="productForm.price" type="number" step="0.01" min="0" class="input-base" required />
              <p v-if="productErrors.price" class="text-red-400 text-xs mt-1">{{ productErrors.price }}</p>
            </div>

            <div class="flex gap-6">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="productForm.is_active" type="checkbox" class="checkbox-base" />
                <span class="text-neutral-300 text-sm">Activo</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="productForm.is_beer_product" type="checkbox" class="checkbox-base" />
                <span class="text-neutral-300 text-sm">🍺 Producto de caguama</span>
              </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeProductModal" class="btn-secondary">Cancelar</button>
              <button type="submit" :disabled="productForm.processing" class="btn-primary">
                {{ editingProduct ? 'Guardar' : 'Crear' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Category Modal -->
      <div v-if="showCategoryModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-neutral-800 rounded-xl w-full max-w-md shadow-xl">
          <div class="flex items-center justify-between p-5 border-b border-neutral-700">
            <h2 class="text-white font-semibold text-lg">
              {{ editingCategory ? 'Editar categoría' : 'Nueva categoría' }}
            </h2>
            <button @click="closeCategoryModal" class="text-neutral-400 hover:text-white">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitCategory" class="p-5 space-y-4">
            <div>
              <label class="label-base">Nombre</label>
              <input v-model="categoryForm.name" type="text" class="input-base" required />
            </div>
            <div>
              <label class="label-base">Ícono (emoji)</label>
              <input v-model="categoryForm.icon" type="text" class="input-base" maxlength="10" placeholder="🍹" />
            </div>
            <div>
              <label class="label-base">Orden</label>
              <input v-model="categoryForm.sort_order" type="number" min="0" class="input-base" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="categoryForm.is_active" type="checkbox" class="checkbox-base" />
              <span class="text-neutral-300 text-sm">Activa</span>
            </label>

            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeCategoryModal" class="btn-secondary">Cancelar</button>
              <button type="submit" :disabled="categoryForm.processing" class="btn-primary">
                {{ editingCategory ? 'Guardar' : 'Crear' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon, XMarkIcon, BeakerIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: Array,
  products: Array,
})

const activeCategory = ref(null)

const filteredProducts = computed(() => {
  if (!activeCategory.value) return props.products
  return props.products.filter(p => p.product_category_id === activeCategory.value)
})

const formatPrice = (val) => Number(val).toFixed(2)

// ── Product modal ──────────────────────────────────────────
const showProductModal = ref(false)
const editingProduct = ref(null)
const productErrors = ref({})

const productForm = useForm({
  product_category_id: '',
  name: '',
  description: '',
  price: '',
  is_active: true,
  is_beer_product: false,
  has_recipe: false,
})

function openProductModal(product = null) {
  editingProduct.value = product
  productErrors.value = {}
  if (product) {
    productForm.product_category_id = product.product_category_id
    productForm.name = product.name
    productForm.description = product.description ?? ''
    productForm.price = product.price
    productForm.is_active = product.is_active
    productForm.is_beer_product = product.is_beer_product
    productForm.has_recipe = product.has_recipe
  } else {
    productForm.reset()
    productForm.is_active = true
  }
  showProductModal.value = true
}

function closeProductModal() {
  showProductModal.value = false
  editingProduct.value = null
}

function submitProduct() {
  if (editingProduct.value) {
    productForm.put(route('admin.products.update', editingProduct.value.id), {
      onSuccess: closeProductModal,
      onError: (e) => { productErrors.value = e },
    })
  } else {
    productForm.post(route('admin.products.store'), {
      onSuccess: closeProductModal,
      onError: (e) => { productErrors.value = e },
    })
  }
}

function confirmDelete(product) {
  if (confirm(`¿Eliminar "${product.name}"?`)) {
    router.delete(route('admin.products.destroy', product.id))
  }
}

// ── Category modal ─────────────────────────────────────────
const showCategoryModal = ref(false)
const editingCategory = ref(null)

const categoryForm = useForm({
  name: '',
  icon: '',
  sort_order: 0,
  is_active: true,
})

function openCategoryModal(category = null) {
  editingCategory.value = category
  if (category) {
    categoryForm.name = category.name
    categoryForm.icon = category.icon ?? ''
    categoryForm.sort_order = category.sort_order
    categoryForm.is_active = category.is_active
  } else {
    categoryForm.reset()
    categoryForm.is_active = true
  }
  showCategoryModal.value = true
}

function closeCategoryModal() {
  showCategoryModal.value = false
  editingCategory.value = null
}

function submitCategory() {
  if (editingCategory.value) {
    categoryForm.put(route('admin.categories.update', editingCategory.value.id), {
      onSuccess: closeCategoryModal,
    })
  } else {
    categoryForm.post(route('admin.categories.store'), {
      onSuccess: closeCategoryModal,
    })
  }
}
</script>
