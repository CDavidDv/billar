<template>
    <AppLayout title="Aditamentos de Caguama">
        <template #header-actions>
            <button
                @click="showCreateForm = !showCreateForm"
                class="px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-semibold hover:bg-green-600 transition-colors"
            >
                + Nuevo aditamento
            </button>
        </template>

        <!-- Create form -->
        <div v-if="showCreateForm" class="mb-6 bg-neutral-800 rounded-xl border border-neutral-700 p-5">
            <p class="text-white font-semibold mb-4">Nuevo aditamento</p>
            <div class="flex gap-3 flex-wrap items-end">
                <div>
                    <label class="block text-xs text-neutral-400 mb-1">Nombre</label>
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Ej: Clamato"
                        class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600 w-48"
                    />
                </div>
                <div>
                    <label class="block text-xs text-neutral-400 mb-1">Volumen (mL)</label>
                    <input
                        v-model.number="createForm.volume_ml"
                        type="number"
                        min="0"
                        max="999"
                        class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600 w-28"
                    />
                    <p class="text-neutral-500 text-xs mt-0.5">0 = no desplaza cerveza</p>
                </div>
                <div>
                    <label class="block text-xs text-neutral-400 mb-1">Orden</label>
                    <input
                        v-model.number="createForm.sort_order"
                        type="number"
                        min="0"
                        class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-green-600 w-20"
                    />
                </div>
                <div class="flex gap-2">
                    <button
                        @click="submitCreate"
                        :disabled="createForm.processing || !createForm.name"
                        class="px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-600 disabled:opacity-50 transition-colors"
                    >
                        Guardar
                    </button>
                    <button
                        @click="showCreateForm = false"
                        class="px-4 py-2 border border-neutral-600 text-neutral-300 text-sm rounded-lg hover:border-neutral-500 transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
            <p v-if="createForm.errors?.name" class="text-red-400 text-xs mt-2">{{ createForm.errors.name }}</p>
        </div>

        <!-- List -->
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-neutral-700 flex items-center justify-between">
                <p class="text-white font-semibold">Aditamentos <span class="text-neutral-500 font-normal text-sm ml-2">{{ addIns.length }}</span></p>
                <p class="text-neutral-500 text-xs">Los mL que un aditamento desplace se restan de la cerveza al servir</p>
            </div>

            <div v-if="!addIns.length" class="text-center py-12 text-neutral-500 text-sm">
                Sin aditamentos. Crea el primero.
            </div>

            <div v-else class="divide-y divide-neutral-700">
                <div
                    v-for="addIn in addIns"
                    :key="addIn.id"
                    class="flex items-center gap-4 px-5 py-3"
                    :class="!addIn.is_active ? 'opacity-50' : ''"
                >
                    <template v-if="editingId === addIn.id">
                        <input
                            v-model="editForm.name"
                            class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-green-600 w-40"
                        />
                        <input
                            v-model.number="editForm.volume_ml"
                            type="number" min="0" max="999"
                            class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-green-600 w-24"
                        />
                        <input
                            v-model.number="editForm.sort_order"
                            type="number" min="0"
                            class="bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-green-600 w-16"
                        />
                        <button @click="submitEdit(addIn)" class="px-3 py-1.5 bg-green-700 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition-colors">
                            Guardar
                        </button>
                        <button @click="editingId = null" class="px-3 py-1.5 border border-neutral-600 text-neutral-300 text-xs rounded-lg hover:border-neutral-500 transition-colors">
                            Cancelar
                        </button>
                    </template>
                    <template v-else>
                        <span class="text-white font-medium flex-1">{{ addIn.name }}</span>
                        <span class="text-amber-400 font-mono text-sm w-20">{{ addIn.volume_ml }} mL</span>
                        <span class="text-neutral-500 text-xs w-16">orden: {{ addIn.sort_order }}</span>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-medium"
                            :class="addIn.is_active ? 'bg-green-900/50 text-green-400' : 'bg-neutral-700 text-neutral-400'"
                        >
                            {{ addIn.is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                        <button @click="startEdit(addIn)" class="text-neutral-400 hover:text-white text-xs transition-colors">
                            Editar
                        </button>
                        <button @click="toggleActive(addIn)" class="text-neutral-400 hover:text-amber-400 text-xs transition-colors">
                            {{ addIn.is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button @click="deleteAddIn(addIn)" class="text-neutral-400 hover:text-red-400 text-xs transition-colors">
                            Eliminar
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    addIns: { type: Array, default: () => [] },
})

const showCreateForm = ref(false)
const editingId = ref(null)

const createForm = useForm({ name: '', volume_ml: 0, sort_order: 0 })
const editForm = ref({ name: '', volume_ml: 0, is_active: true, sort_order: 0 })

function submitCreate() {
    createForm.post(route('admin.add-ins.store'), {
        onSuccess: () => {
            showCreateForm.value = false
            createForm.reset()
        },
    })
}

function startEdit(addIn) {
    editingId.value = addIn.id
    editForm.value = { name: addIn.name, volume_ml: addIn.volume_ml, is_active: addIn.is_active, sort_order: addIn.sort_order }
}

function submitEdit(addIn) {
    useForm(editForm.value).put(route('admin.add-ins.update', addIn.id), {
        onSuccess: () => { editingId.value = null },
    })
}

function toggleActive(addIn) {
    useForm({ ...addIn, is_active: !addIn.is_active }).put(route('admin.add-ins.update', addIn.id))
}

function deleteAddIn(addIn) {
    if (!confirm(`¿Eliminar "${addIn.name}"?`)) return
    useForm({}).delete(route('admin.add-ins.destroy', addIn.id))
}
</script>
