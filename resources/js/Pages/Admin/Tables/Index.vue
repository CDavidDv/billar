<template>
    <AppLayout title="Gestión de Mesas">
        <template #header-actions>
            <button
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition-colors"
            >
                <PlusIcon class="w-4 h-4" />
                Nueva mesa
            </button>
        </template>

        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-700">
                        <th class="px-4 py-3 text-left text-neutral-400 font-medium">Nombre</th>
                        <th class="px-4 py-3 text-left text-neutral-400 font-medium">Tipo</th>
                        <th class="px-4 py-3 text-left text-neutral-400 font-medium">Cobro</th>
                        <th class="px-4 py-3 text-right text-neutral-400 font-medium">Precio</th>
                        <th class="px-4 py-3 text-center text-neutral-400 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right text-neutral-400 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    <tr v-for="table in tables" :key="table.id" class="hover:bg-neutral-750 transition-colors">
                        <td class="px-4 py-3 text-white font-medium">{{ table.name }}</td>
                        <td class="px-4 py-3 text-neutral-300">{{ typeLabels[table.type] }}</td>
                        <td class="px-4 py-3 text-neutral-300">
                            {{ table.billing_type === 'por_hora' ? 'Por hora' : 'Precio fijo' }}
                        </td>
                        <td class="px-4 py-3 text-right text-amber-400 font-semibold">
                            ${{ Number(table.hourly_rate).toFixed(2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="table.is_active
                                    ? 'bg-green-900 text-green-400'
                                    : 'bg-neutral-700 text-neutral-400'"
                            >
                                {{ table.is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click="openEdit(table)"
                                    class="p-1.5 text-neutral-400 hover:text-white hover:bg-neutral-700 rounded-lg transition-colors"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    @click="confirmDelete(table)"
                                    class="p-1.5 text-neutral-400 hover:text-red-400 hover:bg-neutral-700 rounded-lg transition-colors"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal crear/editar -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-md border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-5">
                    {{ editing ? 'Editar mesa' : 'Nueva mesa' }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1">Nombre</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej: Mesa 7"
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                            :class="{ 'border-red-600': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-red-400 text-xs">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-1">Tipo</label>
                            <select
                                v-model="form.type"
                                class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600"
                            >
                                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-1">Cobro</label>
                            <select
                                v-model="form.billing_type"
                                class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600"
                            >
                                <option value="por_hora">Por hora</option>
                                <option value="precio_fijo">Precio fijo</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1">
                            {{ form.billing_type === 'por_hora' ? 'Precio por hora ($)' : 'Precio fijo ($)' }}
                        </label>
                        <input
                            v-model="form.hourly_rate"
                            type="number"
                            min="0"
                            step="5"
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600"
                            :class="{ 'border-red-600': form.errors.hourly_rate }"
                        />
                        <p v-if="form.errors.hourly_rate" class="mt-1 text-red-400 text-xs">{{ form.errors.hourly_rate }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            @click="form.is_active = !form.is_active"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                            :class="form.is_active ? 'bg-green-700' : 'bg-neutral-600'"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
                            />
                        </button>
                        <span class="text-sm text-neutral-300">Mesa activa</span>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        @click="showForm = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="submitForm"
                        :disabled="form.processing"
                        class="flex-1 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-semibold hover:bg-green-600 transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Guardando...' : (editing ? 'Guardar cambios' : 'Crear mesa') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal eliminar -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-neutral-800 rounded-2xl p-6 w-full max-w-sm border border-neutral-700 shadow-xl">
                <h2 class="text-white font-bold text-xl mb-2">Eliminar mesa</h2>
                <p class="text-neutral-400 text-sm mb-6">
                    ¿Eliminar <span class="text-white font-medium">{{ deleteTarget.name }}</span>?
                    Esta acción no se puede deshacer.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="deleteTarget = null"
                        class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="submitDelete"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-700 text-white text-sm font-semibold hover:bg-red-600 transition-colors"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    tables: Array,
})

const showForm = ref(false)
const editing = ref(null)
const deleteTarget = ref(null)

const typeLabels = {
    billar_comun: 'Billar',
    billar_privado: 'Privado',
    futbolito: 'Futbolito',
    maquina: 'Máquina',
    otro: 'Otro',
}

const typeOptions = [
    { value: 'billar_comun', label: 'Billar común' },
    { value: 'billar_privado', label: 'Billar privado' },
    { value: 'futbolito', label: 'Futbolito' },
    { value: 'maquina', label: 'Máquina' },
    { value: 'otro', label: 'Otro' },
]

const form = useForm({
    name: '',
    type: 'billar_comun',
    billing_type: 'por_hora',
    hourly_rate: 50,
    is_active: true,
})

function openCreate() {
    editing.value = null
    form.reset()
    form.type = 'billar_comun'
    form.billing_type = 'por_hora'
    form.hourly_rate = 50
    form.is_active = true
    showForm.value = true
}

function openEdit(table) {
    editing.value = table
    form.name = table.name
    form.type = table.type
    form.billing_type = table.billing_type
    form.hourly_rate = Number(table.hourly_rate)
    form.is_active = table.is_active
    showForm.value = true
}

function submitForm() {
    if (editing.value) {
        form.put(route('admin.tables.update', editing.value.id), {
            onSuccess: () => { showForm.value = false },
        })
    } else {
        form.post(route('admin.tables.store'), {
            onSuccess: () => { showForm.value = false },
        })
    }
}

function confirmDelete(table) {
    deleteTarget.value = table
}

function submitDelete() {
    useForm({}).delete(route('admin.tables.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>
