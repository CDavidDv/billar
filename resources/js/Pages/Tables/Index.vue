<template>
    <AppLayout title="Mesas">
        <template #header-actions>
            <span class="text-neutral-400 text-sm">
                {{ activeTables }} / {{ tables.length }} ocupadas
            </span>
            <button
                @click="newBarOrder"
                :disabled="barOrderForm.processing"
                class="px-4 py-2 rounded-lg bg-neutral-700 border border-neutral-600 text-neutral-200 text-sm font-semibold hover:bg-neutral-600 transition-colors disabled:opacity-50"
            >
                + Orden barra
            </button>
        </template>

        <!-- Bar orders activas -->
        <div v-if="barOrders.length > 0" class="mb-6">
            <p class="text-neutral-400 text-xs font-semibold uppercase tracking-wider mb-3">
                Órdenes de barra abiertas ({{ barOrders.length }})
            </p>
            <div class="flex flex-wrap gap-3">
                <a
                    v-for="order in barOrders"
                    :key="order.id"
                    :href="route('bar-orders.show', order.id)"
                    class="flex items-center gap-3 bg-neutral-800 border border-amber-700/40 rounded-xl px-4 py-3 hover:border-amber-600/70 transition-all hover:scale-[1.02]"
                >
                    <div>
                        <p class="text-white text-sm font-semibold">Barra #{{ order.id }}</p>
                        <p class="text-neutral-500 text-xs">{{ order.items_count }} items · {{ order.created_by }}</p>
                    </div>
                    <span class="text-amber-400 font-semibold text-sm ml-2">${{ order.subtotal.toFixed(2) }}</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <button
                v-for="table in tables"
                :key="table.id"
                @click="handleTableClick(table)"
                class="relative flex flex-col rounded-xl border-2 p-4 text-left transition-all hover:scale-[1.02] focus:outline-none"
                :class="tableCardClass(table)"
            >
                <!-- Status badge -->
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wide"
                        :class="table.session ? 'text-red-400' : 'text-green-400'">
                        {{ table.session ? 'Ocupada' : 'Libre' }}
                    </span>
                    <span class="text-xs text-neutral-500">{{ table.type_label }}</span>
                </div>

                <!-- Name -->
                <p class="text-white font-bold text-lg leading-tight mb-2">{{ table.name }}</p>

                <!-- Session info or rate -->
                <div v-if="table.session" class="mt-auto">
                    <p class="text-amber-400 font-mono text-2xl font-bold">
                        {{ elapsedTime(table.session.opened_at) }}
                    </p>
                    <p class="text-neutral-400 text-xs mt-1">{{ table.session.opened_by }}</p>
                    <p v-if="table.session.items_count > 0" class="text-neutral-500 text-xs">
                        {{ table.session.items_count }} producto{{ table.session.items_count !== 1 ? 's' : '' }}
                    </p>
                </div>
                <div v-else class="mt-auto">
                    <p class="text-neutral-400 text-sm">
                        <span v-if="table.billing_type === 'por_hora'">
                            ${{ table.hourly_rate }}/hr
                        </span>
                        <span v-else>
                            ${{ table.hourly_rate }} fijo
                        </span>
                    </p>
                </div>
            </button>
        </div>

        <!-- Modal: abrir sesión -->
        <div v-if="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-sm border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-1">Abrir sesión</h2>
                <p class="text-neutral-400 text-sm mb-5">{{ selectedTable?.name }}</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-2">Tipo de cobro</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="form.billing_type = 'por_hora'"
                                class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                :class="form.billing_type === 'por_hora'
                                    ? 'bg-green-700 border-green-600 text-white'
                                    : 'border-neutral-600 text-neutral-400 hover:border-neutral-500'"
                            >
                                Por hora
                            </button>
                            <button
                                @click="form.billing_type = 'precio_fijo'"
                                class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                :class="form.billing_type === 'precio_fijo'
                                    ? 'bg-green-700 border-green-600 text-white'
                                    : 'border-neutral-600 text-neutral-400 hover:border-neutral-500'"
                            >
                                Precio fijo
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1">
                            {{ form.billing_type === 'por_hora' ? 'Precio/hora' : 'Precio fijo' }}:
                            <span class="text-amber-400">${{ selectedTable?.hourly_rate }}</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1">Notas (opcional)</label>
                        <input
                            v-model="form.notes"
                            type="text"
                            placeholder="Ej: cliente VIP, reservación..."
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                        />
                    </div>
                </div>

                <div v-if="form.errors?.table" class="mt-3 text-red-400 text-sm">
                    {{ form.errors.table }}
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        @click="closeOpenModal"
                        class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="submitOpenSession"
                        :disabled="form.processing"
                        class="flex-1 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-semibold hover:bg-green-600 transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Abriendo...' : 'Abrir sesión' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    tables: Array,
    barOrders: { type: Array, default: () => [] },
})

const openModal = ref(false)
const selectedTable = ref(null)
const form = useForm({
    billing_type: 'por_hora',
    notes: '',
})
const barOrderForm = useForm({})

function newBarOrder() {
    barOrderForm.post(route('bar-orders.create'))
}

const activeTables = computed(() => props.tables.filter(t => t.session).length)

function tableCardClass(table) {
    if (table.session) {
        return 'bg-neutral-800 border-red-700/50 hover:border-red-600/70'
    }
    return 'bg-neutral-800 border-green-700/40 hover:border-green-600/60'
}

function elapsedTime(openedAt) {
    const diff = Math.floor((Date.now() - new Date(openedAt).getTime()) / 1000)
    const h = Math.floor(diff / 3600)
    const m = Math.floor((diff % 3600) / 60)
    const s = diff % 60
    if (h > 0) return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

function handleTableClick(table) {
    if (table.session) {
        router.visit(route('tables.sessions.show', table.session.id))
    } else {
        selectedTable.value = table
        form.reset()
        form.billing_type = table.billing_type
        openModal.value = true
    }
}

function closeOpenModal() {
    openModal.value = false
    selectedTable.value = null
}

function submitOpenSession() {
    form.post(route('tables.open', selectedTable.value.id), {
        onSuccess: () => closeOpenModal(),
    })
}

// Polling cada 10s
let pollingInterval = null
onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['tables'] })
    }, 10000)
})
onUnmounted(() => clearInterval(pollingInterval))
</script>
