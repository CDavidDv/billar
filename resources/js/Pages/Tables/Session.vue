<template>
    <AppLayout :title="table.name">
        <template #header-actions>
            <button
                @click="printTicket"
                class="px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm font-semibold hover:border-neutral-500 transition-colors"
            >
                Imprimir ticket
            </button>
            <button
                @click="showCloseModal = true"
                class="px-4 py-2 rounded-lg bg-red-700 text-white text-sm font-semibold hover:bg-red-600 transition-colors"
            >
                Cerrar sesión
            </button>
        </template>

        <div class="flex gap-6 h-full">
            <!-- Left: timer + info -->
            <div class="w-72 flex-shrink-0 space-y-4">
                <!-- Timer card -->
                <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
                    <p class="text-neutral-400 text-sm mb-1">{{ table.type_label }} · {{ table.name }}</p>
                    <p class="text-amber-400 font-mono text-5xl font-bold tracking-tight mb-3">
                        {{ elapsedDisplay }}
                    </p>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Cobro</span>
                            <span class="text-white">
                                {{ session.billing_type === 'por_hora'
                                    ? `$${session.hourly_rate}/hr`
                                    : `$${session.hourly_rate} fijo` }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Tiempo est.</span>
                            <span class="text-amber-400 font-semibold">${{ estimatedTimeCost.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Abrió</span>
                            <span class="text-white">{{ session.opened_by }}</span>
                        </div>
                    </div>
                    <p v-if="session.notes" class="mt-3 text-neutral-500 text-xs border-t border-neutral-700 pt-3">
                        {{ session.notes }}
                    </p>
                </div>

                <!-- Totals card -->
                <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Productos</span>
                        <span class="text-white">${{ order.subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Tiempo est.</span>
                        <span class="text-white">${{ estimatedTimeCost.toFixed(2) }}</span>
                    </div>
                    <div v-if="order.discount > 0" class="flex justify-between">
                        <span class="text-neutral-400">Descuento</span>
                        <span class="text-green-400">-${{ order.discount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-neutral-700 pt-2 font-semibold">
                        <span class="text-neutral-300">Total est.</span>
                        <span class="text-amber-400 text-base">
                            ${{ (order.subtotal + estimatedTimeCost - order.discount).toFixed(2) }}
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

        <!-- Ticket de impresión (oculto en pantalla) -->
        <div id="print-ticket" class="hidden">
            <div style="font-family: monospace; font-size: 12px; width: 280px; margin: 0 auto; padding: 8px;">
                <div style="text-align: center; margin-bottom: 8px;">
                    <strong style="font-size: 16px;">BILLAR</strong><br>
                    <span>{{ table.name }} · {{ table.type_label }}</span><br>
                    <span>{{ formatDate(session.opened_at) }}</span>
                </div>
                <hr style="border-top: 1px dashed #000; margin: 6px 0;" />
                <div v-for="item in order.items" :key="item.id" style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>{{ item.quantity }}x {{ item.product_name }}</span>
                    <span>${{ item.subtotal.toFixed(2) }}</span>
                </div>
                <hr style="border-top: 1px dashed #000; margin: 6px 0;" />
                <div style="display: flex; justify-content: space-between;">
                    <span>Productos</span><span>${{ order.subtotal.toFixed(2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Tiempo ({{ elapsedDisplay }})</span><span>${{ estimatedTimeCost.toFixed(2) }}</span>
                </div>
                <div v-if="order.discount > 0" style="display: flex; justify-content: space-between;">
                    <span>Descuento</span><span>-${{ order.discount.toFixed(2) }}</span>
                </div>
                <hr style="border-top: 1px solid #000; margin: 6px 0;" />
                <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: bold;">
                    <span>TOTAL</span>
                    <span>${{ (order.subtotal + estimatedTimeCost - order.discount).toFixed(2) }}</span>
                </div>
                <div style="text-align: center; margin-top: 10px; font-size: 11px;">
                    Abrió: {{ session.opened_by }}<br>
                    ¡Gracias por su visita!
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

        <!-- Modal: cerrar sesión -->
        <div v-if="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-sm border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-1">Cerrar sesión</h2>
                <p class="text-neutral-400 text-sm mb-5">{{ table.name }}</p>

                <div class="bg-neutral-750 rounded-xl p-4 space-y-2 text-sm mb-5 border border-neutral-700">
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Tiempo</span>
                        <span class="text-white">{{ elapsedDisplay }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Tiempo</span>
                        <span class="text-amber-400">${{ estimatedTimeCost.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-400">Productos</span>
                        <span class="text-white">${{ order.subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold border-t border-neutral-600 pt-2">
                        <span class="text-neutral-300">Total</span>
                        <span class="text-amber-400 text-lg">
                            ${{ (order.subtotal + estimatedTimeCost - order.discount).toFixed(2) }}
                        </span>
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
                        {{ closeForm.processing ? 'Cerrando...' : 'Cerrar y cobrar' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'
import ProductSearch from '@/Components/UI/ProductSearch.vue'
import CaguamaModal from '@/Components/UI/CaguamaModal.vue'

const props = defineProps({
    session: Object,
    table: Object,
    order: Object,
    billing: Object,
    products: { type: Array, default: () => [] },
    caguamas: { type: Array, default: () => [] },
    recipes: { type: Array, default: () => [] },
    addIns: { type: Array, default: () => [] },
})

const showCloseModal = ref(false)
const showCaguamaModal = ref(false)
const selectedBeerProduct = ref(null)
const now = ref(Date.now())
let ticker = null

const elapsedSeconds = computed(() => Math.floor((now.value - new Date(props.session.opened_at).getTime()) / 1000))

const elapsedDisplay = computed(() => {
    const s = elapsedSeconds.value
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    if (h > 0) return `${h}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
    return `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
})

const estimatedTimeCost = computed(() => {
    if (props.session.billing_type === 'precio_fijo') {
        return props.session.hourly_rate
    }
    const hours = elapsedSeconds.value / 3600
    return Math.round(hours * props.session.hourly_rate * 100) / 100
})

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
    itemForm.post(route('tables.sessions.items.store', props.session.id), {
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
    beerForm.post(route('tables.sessions.beer-items.store', props.session.id), {
        onSuccess: () => {
            showCaguamaModal.value = false
            selectedBeerProduct.value = null
        },
    })
}

function removeItem(itemId) {
    useForm({}).delete(route('tables.sessions.items.destroy', [props.session.id, itemId]))
}

function submitClose() {
    closeForm.post(route('tables.sessions.close', props.session.id))
}

function formatDate(iso) {
    return new Date(iso).toLocaleString('es-MX', { dateStyle: 'short', timeStyle: 'short' })
}

function printTicket() {
    window.print()
}

onMounted(() => {
    ticker = setInterval(() => { now.value = Date.now() }, 1000)
})
onUnmounted(() => clearInterval(ticker))
</script>
