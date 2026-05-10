<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    branches: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)

const form = useForm({
    name: '',
    address: '',
    phone: '',
    is_main: false,
    is_active: true,
})

function openCreate() {
    editing.value = null
    form.reset()
    showModal.value = true
}

function openEdit(branch) {
    editing.value = branch
    form.name = branch.name
    form.address = branch.address || ''
    form.phone = branch.phone || ''
    form.is_main = branch.is_main
    form.is_active = branch.is_active
    showModal.value = true
}

function submit() {
    if (editing.value) {
        form.put(route('admin.branches.update', editing.value.id), {
            onSuccess: () => { showModal.value = false },
        })
    } else {
        form.post(route('admin.branches.store'), {
            onSuccess: () => { showModal.value = false },
        })
    }
}

function destroy(branch) {
    if (!confirm(`¿Eliminar sucursal "${branch.name}"?`)) return
    router.delete(route('admin.branches.destroy', branch.id))
}
</script>

<template>
    <AppLayout title="Sucursales">
        <template #header-actions>
            <button @click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors">
                + Nueva sucursal
            </button>
        </template>

        <div class="p-6 space-y-4">
            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="p-3 bg-green-900/40 border border-green-700/50 rounded-lg text-green-400 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <!-- Branches list -->
            <div class="grid gap-4">
                <div v-for="branch in branches" :key="branch.id"
                    class="bg-neutral-800 rounded-xl border border-neutral-700 p-5 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-white font-semibold">{{ branch.name }}</h3>
                            <span v-if="branch.is_main"
                                class="px-2 py-0.5 bg-amber-900/50 text-amber-400 text-xs rounded">Principal</span>
                            <span v-if="!branch.is_active"
                                class="px-2 py-0.5 bg-neutral-700 text-neutral-400 text-xs rounded">Inactiva</span>
                        </div>
                        <p v-if="branch.address" class="text-neutral-500 text-sm mt-1">{{ branch.address }}</p>
                        <p v-if="branch.phone" class="text-neutral-500 text-sm">{{ branch.phone }}</p>
                        <p class="text-neutral-600 text-xs mt-2">{{ branch.tables_count }} mesas</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openEdit(branch)"
                            class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 text-sm rounded-lg">
                            Editar
                        </button>
                        <button v-if="!branch.is_main" @click="destroy(branch)"
                            class="px-3 py-1.5 bg-red-900/50 hover:bg-red-800/50 text-red-400 text-sm rounded-lg">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="branches.length === 0" class="text-center py-10 text-neutral-500">
                No hay sucursales aún
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
                    <h2 class="text-white font-semibold text-lg">{{ editing ? 'Editar' : 'Nueva' }} Sucursal</h2>
                    <button @click="showModal = false" class="text-neutral-400 hover:text-white">✕</button>
                </div>
                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1.5">Nombre</label>
                        <input v-model="form.name" type="text" required
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1.5">Dirección</label>
                        <input v-model="form.address" type="text"
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-300 mb-1.5">Teléfono</label>
                        <input v-model="form.phone" type="text"
                            class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-neutral-300">
                        <input v-model="form.is_main" type="checkbox" class="rounded bg-neutral-700 border-neutral-600 text-green-600" />
                        Sucursal principal
                    </label>
                    <label class="flex items-center gap-2 text-sm text-neutral-300">
                        <input v-model="form.is_active" type="checkbox" class="rounded bg-neutral-700 border-neutral-600 text-green-600" />
                        Activa
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                            class="flex-1 px-4 py-2 bg-neutral-700 text-neutral-200 text-sm rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 px-4 py-2 bg-green-700 disabled:opacity-50 text-white text-sm rounded-lg">
                            {{ editing ? 'Guardar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>