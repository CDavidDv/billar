<template>
    <AppLayout title="Orden de barra">
        <template #header-actions>
            <button
                @click="printTicket"
                class="px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm font-semibold hover:border-neutral-500 transition-colors"
            >
                Imprimir ticket
            </button>
            <button
                @click="showCancelModal = true"
                class="px-4 py-2 rounded-lg border border-neutral-600 text-neutral-400 text-sm font-semibold hover:border-red-700 hover:text-red-400 transition-colors"
            >
                Cancelar orden
            </button>
            <button
                @click="showCloseModal = true"
                class="px-4 py-2 rounded-lg bg-red-700 text-white text-sm font-semibold hover:bg-red-600 transition-colors"
            >
                Cobrar
            </button>
        </template>

        <div class="flex gap-6 h-full">
            <!-- Left: totals -->
            <div class="w-72 flex-shrink-0 space-y-4">
                <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
                    <p class="text-neutral-400 text-sm mb-1">Orden de barra</p>
                    <p class="text-amber-400 font-mono text-4xl font-bold tracking-tight mb-3">
                        ${{ order.subtotal.toFixed(2) }}
                    </p>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Apertura</span>
                            <span class="text-white">{{ formatDate(order.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Atendió</span>
                            <span class="text-white">{{ order.created_by }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Subtotal</span>
                        <span class="text-white">${{ order.subtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="order.discount > 0" class="flex justify-between">
                        <span class="text-neutral-400">Descuento</span>
                        <span class="text-green-400">-${{ order.discount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-neutral-700 pt-2 font-semibold">
                        <span class="text-neutral-300">Total</span>
                        <span class="text-amber-400 text-base">
                            ${{ (order.subtotal - order.discount).toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: items -->
            <div class="flex-1 flex flex-col gap-4 min-w-0">
                <!-- Add item -->
                <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-4">
                    <p class="text-white font-semibold mb-3">Agregar producto</p>
                    <ProductSearch
                        :products="props.products"
                        v-model:productName="itemForm.product_name"
                        v-model:unitPrice="itemForm.unit_price"
                        v-model:quantity="itemForm.quantity"
                        :disabled="itemForm.processing || beerForm.processing"
                        @submit="submitItem"
                        @select="onProductSelect"
                    />
                    <div v-if="itemForm.hasErrors" class="mt-2 text-red-400 text-xs">
                        Llena nombre y precio correctamente.
                    </div>
                </div>

                <!-- Items list -->
                <div class="flex-1 bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-neutral-700">
                        <p class="text-white font-semibold">
                            Cuenta
                            <span class="text-neutral-500 font-normal text-sm ml-2">{{ order.items.length }} items</span>
                        </p>
                    </div>

                    <div v-if="order.items.length === 0" class="flex items-center justify-center h-40 text-neutral-500 text-sm">
                        Sin productos en la cuenta
                    </div>

                    <div v-else class="divide-y divide-neutral-700">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-3 px-4 py-3 hover:bg-neutral-750 group"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm font-medium truncate">{{ item.product_name }}</p>
                                <p v-if="item.notes" class="text-neutral-500 text-xs truncate">{{ item.notes }}</p>
                            </div>
                            <span class="text-neutral-400 text-sm">{{ item.quantity }}x</span>
                            <span class="text-neutral-300 text-sm w-16 text-right">${{ item.unit_price.toFixed(2) }}</span>
                            <span class="text-white font-semibold text-sm w-20 text-right">${{ item.subtotal.toFixed(2) }}</span>
                            <button
                                @click="removeItem(item.id)"
                                class="opacity-0 group-hover:opacity-100 text-neutral-500 hover:text-red-400 transition-all ml-1"
                            >
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print ticket -->
        <div id="print-ticket" class="hidden">
            <div style="font-family: monospace; font-size: 12px; width: 280px; margin: 0 auto; padding: 8px;">
                <div style="text-align: center; margin-bottom: 8px;">
                    <strong style="font-size: 16px;">BILLAR</strong><br>
                    <span>Orden de barra</span><br>
                    <span>{{ formatDate(order.created_at) }}</span>
                </div>
                <hr style="border-top: 1px dashed #000; margin: 6px 0;" />
                <div v-for="item in order.items" :key="item.id" style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>{{ item.quantity }}x {{ item.product_name }}</span>
                    <span>${{ item.subtotal.toFixed(2) }}</span>
                </div>
                <hr style="border-top: 1px dashed #000; margin: 6px 0;" />
                <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: bold;">
                    <span>TOTAL</span>
                    <span>${{ (order.subtotal - order.discount).toFixed(2) }}</span>
                </div>
                <div style="text-align: center; margin-top: 10px; font-size: 11px;">
                    Atendió: {{ order.created_by }}<br>
                    ¡Gracias por su visita!
                </div>
            </div>
        </div>

        <!-- Modal: cobrar -->
        <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-sm border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-1">Cobrar orden</h2>
                <p class="text-neutral-400 text-sm mb-5">Barra sin mesa</p>

                <div class="bg-neutral-750 rounded-xl p-4 space-y-2 text-sm mb-5 border border-neutral-700">
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Productos</span>
                        <span class="text-white">${{ order.subtotal.toFixed(2) }}</span>
                    </div>
                    <div v-if="order.discount > 0" class="flex justify-between">
                        <span class="text-neutral-400">Descuento</span>
                        <span class="text-green-400">-${{ order.discount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t border-neutral-600 pt-2">
                        <span class="text-neutral-300">Total</span>
                        <span class="text-amber-400 text-lg">${{ (order.subtotal - order.discount).toFixed(2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-300 mb-2">Método de pago</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="method in paymentMethods"
                            :key="method.value"
                            @click="closeForm.payment_method = method.value"
                            class="px-3 py-2 rounded-lg border text-xs font-medium transition-colors"
                            :class="closeForm.payment_method === method.value
                                ? 'bg-green-700 border-green-600 text-white'
                                : 'border-neutral-600 text-neutral-400 hover:border-neutral-500'"
                        >
                            {{ method.label }}
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        @click="showCloseModal = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="submitClose"
                        :disabled="closeForm.processing || !closeForm.payment_method"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-700 text-white text-sm font-semibold hover:bg-red-600 transition-colors disabled:opacity-50"
                    >
                        {{ closeForm.processing ? 'Cerrando...' : 'Cobrar' }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal: caguama beer item -->
        <CaguamaModal
            :show="showCaguamaModal"
            :product="selectedBeerProduct"
            :caguamas="props.caguamas"
            :recipes="props.recipes"
            :addIns="props.addIns"
            @confirm="submitBeerItem"
            @cancel="showCaguamaModal = false"
        />

        <!-- Modal: cancelar orden -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-sm border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-1">Cancelar orden</h2>
                <p class="text-neutral-400 text-sm mb-5">
                    Se cancelará la orden con {{ order.items.length }} producto{{ order.items.length !== 1 ? 's' : '' }}.
                    Esta acción no se puede deshacer.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="showCancelModal = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                    >
                        Volver
                    </button>
                    <button
                        @click="submitCancel"
                        :disabled="cancelForm.processing"
                        class="flex-1 px-4 py-2 rounded-lg bg-neutral-700 border border-red-700 text-red-400 text-sm font-semibold hover:bg-red-900/40 transition-colors disabled:opacity-50"
                    >
                        {{ cancelForm.processing ? 'Cancelando...' : 'Sí, cancelar' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'
import ProductSearch from '@/Components/UI/ProductSearch.vue'
import CaguamaModal from '@/Components/UI/CaguamaModal.vue'

const props = defineProps({
    order: Object,
    products: { type: Array, default: () => [] },
    caguamas: { type: Array, default: () => [] },
    recipes: { type: Array, default: () => [] },
    addIns: { type: Array, default: () => [] },
})

const showCloseModal = ref(false)
const showCancelModal = ref(false)
const showCaguamaModal = ref(false)
const selectedBeerProduct = ref(null)

const itemForm = useForm({
    product_name: '',
    unit_price: '',
    quantity: 1,
    notes: '',
})

const beerForm = useForm({
    product_id: null,
    caguama_id: null,
    michelada_recipe_id: null,
    add_in_ids: [],
    quantity: 1,
    notes: '',
})

const closeForm = useForm({
    payment_method: 'efectivo',
})
const cancelForm = useForm({})

const paymentMethods = [
    { value: 'efectivo', label: 'Efectivo' },
    { value: 'tarjeta', label: 'Tarjeta' },
    { value: 'transferencia', label: 'Transfer.' },
]

function onProductSelect(product) {
    if (product.is_beer_product) {
        selectedBeerProduct.value = product
        showCaguamaModal.value = true
        itemForm.reset()
        itemForm.quantity = 1
    }
}

function submitItem() {
    if (!itemForm.product_name || !itemForm.unit_price) return
    itemForm.post(route('bar-orders.items.store', props.order.id), {
        onSuccess: () => {
            itemForm.reset()
            itemForm.quantity = 1
        },
    })
}

function submitBeerItem(data) {
    beerForm.product_id = selectedBeerProduct.value.id
    beerForm.caguama_id = data.caguama_id
    beerForm.michelada_recipe_id = data.michelada_recipe_id
    beerForm.add_in_ids = data.add_in_ids
    beerForm.quantity = data.quantity
    beerForm.post(route('bar-orders.beer-items.store', props.order.id), {
        onSuccess: () => {
            showCaguamaModal.value = false
            selectedBeerProduct.value = null
        },
    })
}

function removeItem(itemId) {
    useForm({}).delete(route('bar-orders.items.destroy', [props.order.id, itemId]))
}

function submitClose() {
    closeForm.post(route('bar-orders.close', props.order.id))
}

function submitCancel() {
    cancelForm.post(route('bar-orders.cancel', props.order.id))
}

function formatDate(iso) {
    return new Date(iso).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' })
}

function printTicket() {
    window.print()
}
</script>
