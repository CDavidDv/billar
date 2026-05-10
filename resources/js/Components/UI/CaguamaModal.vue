<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
        <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700 sticky top-0 bg-neutral-800 z-10">
                <div>
                    <h2 class="text-white font-semibold text-lg">Servir cerveza</h2>
                    <p class="text-neutral-400 text-sm">{{ product?.name }}</p>
                </div>
                <button @click="$emit('cancel')" class="text-neutral-400 hover:text-white transition-colors">✕</button>
            </div>

            <div class="px-6 py-5 space-y-4">

                <!-- Caguama selector -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-medium text-neutral-300">Caguama activa</label>
                        <button
                            @click="showOpenCaguama = !showOpenCaguama"
                            class="text-xs text-green-400 hover:text-green-300 transition-colors font-medium"
                        >
                            {{ showOpenCaguama ? '✕ Cancelar' : '+ Abrir nueva' }}
                        </button>
                    </div>

                    <!-- Open new caguama inline -->
                    <div v-if="showOpenCaguama" class="mb-2 flex gap-2 items-start">
                        <input
                            v-model="openCaguamaForm.notes"
                            type="text"
                            placeholder="Nota opcional (ej: Corona)"
                            class="flex-1 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                            @keyup.enter="submitOpenCaguama"
                        />
                        <button
                            @click="submitOpenCaguama"
                            :disabled="openCaguamaForm.processing"
                            class="px-3 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors whitespace-nowrap"
                        >
                            {{ openCaguamaForm.processing ? '...' : 'Abrir (1,200 mL)' }}
                        </button>
                    </div>

                    <select
                        v-model="form.caguama_id"
                        :disabled="!localCaguamas.length"
                        class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                    >
                        <option v-if="!localCaguamas.length" :value="null">Sin caguamas abiertas</option>
                        <option v-for="c in localCaguamas" :key="c.id" :value="c.id">{{ c.label }}</option>
                    </select>
                </div>

                <!-- Recipe selector -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-medium text-neutral-300">Receta / vaso</label>
                        <button
                            @click="showNewRecipe = !showNewRecipe"
                            class="text-xs text-green-400 hover:text-green-300 transition-colors font-medium"
                        >
                            {{ showNewRecipe ? '✕ Cancelar' : '+ Nueva receta' }}
                        </button>
                    </div>

                    <!-- Create recipe inline -->
                    <div v-if="showNewRecipe" class="mb-2 space-y-2">
                        <div class="flex gap-2">
                            <input
                                v-model="newRecipeForm.name"
                                type="text"
                                placeholder="Nombre (ej: Tarro)"
                                class="flex-1 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                            />
                            <div class="flex items-center gap-1">
                                <input
                                    v-model.number="newRecipeForm.container_volume_ml"
                                    type="number"
                                    min="50"
                                    max="1200"
                                    placeholder="mL"
                                    class="w-20 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                                />
                                <span class="text-neutral-500 text-xs">mL</span>
                            </div>
                            <button
                                @click="submitNewRecipe"
                                :disabled="newRecipeForm.processing || !newRecipeForm.name || !newRecipeForm.container_volume_ml"
                                class="px-3 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition-colors"
                            >
                                {{ newRecipeForm.processing ? '...' : 'Crear' }}
                            </button>
                        </div>
                        <p v-if="newRecipeForm.errors?.name" class="text-red-400 text-xs">{{ newRecipeForm.errors.name }}</p>
                    </div>

                    <select
                        v-model="form.recipe_id"
                        class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option v-for="r in localRecipes" :key="r.id" :value="r.id">
                            {{ r.name }} ({{ r.container_volume_ml }} mL)
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
                            :class="form.add_in_ids.includes(addIn.id) ? 'border border-amber-600/60' : 'border border-neutral-600'"
                        >
                            <input
                                type="checkbox"
                                :value="addIn.id"
                                v-model="form.add_in_ids"
                                class="accent-amber-500"
                            />
                            <span class="text-white text-sm flex-1">{{ addIn.name }}</span>
                            <span v-if="addIn.volume_ml > 0" class="text-neutral-400 text-xs">{{ addIn.volume_ml }}mL</span>
                        </label>
                    </div>
                </div>

                <!-- Volume preview -->
                <div class="bg-neutral-750 rounded-xl p-4 border border-neutral-600">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-400">Capacidad vaso</span>
                        <span class="text-white">{{ selectedRecipe?.container_volume_ml ?? 0 }} mL</span>
                    </div>
                    <div v-if="displacedVolume > 0" class="flex justify-between items-center text-sm mt-1">
                        <span class="text-neutral-400">Aditamentos</span>
                        <span class="text-neutral-300">− {{ displacedVolume }} mL</span>
                    </div>
                    <div class="flex justify-between items-center font-semibold border-t border-neutral-600 pt-2 mt-2">
                        <span class="text-neutral-300">Cerveza a descontar</span>
                        <span class="text-amber-400 text-base">{{ beerVolume }} mL</span>
                    </div>
                    <p v-if="selectedCaguama && beerVolume > selectedCaguama.remaining_volume_ml" class="text-red-400 text-xs mt-2">
                        Insuficiente — quedan {{ selectedCaguama.remaining_volume_ml }} mL
                    </p>
                    <p v-else-if="selectedCaguama" class="text-green-400 text-xs mt-2">
                        Quedarán {{ selectedCaguama.remaining_volume_ml - beerVolume }} mL después
                    </p>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-neutral-300 mb-1.5">Cantidad</label>
                    <input
                        v-model.number="form.quantity"
                        type="number"
                        min="1"
                        max="10"
                        class="w-24 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    />
                </div>
            </div>

            <div class="flex gap-3 px-6 pb-6">
                <button
                    @click="$emit('cancel')"
                    class="flex-1 px-4 py-2 rounded-lg border border-neutral-600 text-neutral-300 text-sm hover:border-neutral-500 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="confirm"
                    :disabled="!canConfirm"
                    class="flex-1 px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-semibold hover:bg-green-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Agregar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    show: Boolean,
    product: Object,
    caguamas: { type: Array, default: () => [] },
    recipes: { type: Array, default: () => [] },
    addIns: { type: Array, default: () => [] },
})

const emit = defineEmits(['confirm', 'cancel'])

// Local copies so we can add newly created items without full page reload
const localCaguamas = ref([...props.caguamas])
const localRecipes = ref([...props.recipes])

watch(() => props.caguamas, (v) => { localCaguamas.value = [...v] })
watch(() => props.recipes, (v) => { localRecipes.value = [...v] })

const showOpenCaguama = ref(false)
const showNewRecipe = ref(false)

const form = ref({
    caguama_id: null,
    recipe_id: null,
    add_in_ids: [],
    quantity: 1,
})

const openCaguamaForm = useForm({ notes: '' })
const newRecipeForm = useForm({ name: '', container_volume_ml: 800 })

watch(() => props.show, (v) => {
    if (v) {
        localCaguamas.value = [...props.caguamas]
        localRecipes.value = [...props.recipes]
        form.value = {
            caguama_id: props.caguamas[0]?.id ?? null,
            recipe_id: props.recipes[0]?.id ?? null,
            add_in_ids: [],
            quantity: 1,
        }
        showOpenCaguama.value = false
        showNewRecipe.value = false
    }
})

const selectedCaguama = computed(() =>
    localCaguamas.value.find(c => c.id === form.value.caguama_id)
)

const selectedRecipe = computed(() =>
    localRecipes.value.find(r => r.id === form.value.recipe_id)
)

const displacedVolume = computed(() =>
    props.addIns
        .filter(a => form.value.add_in_ids.includes(a.id))
        .reduce((sum, a) => sum + a.volume_ml, 0)
)

const beerVolume = computed(() => {
    if (!selectedRecipe.value) return 0
    return Math.max(0, selectedRecipe.value.container_volume_ml - displacedVolume.value)
})

const canConfirm = computed(() => {
    if (!form.value.caguama_id || !form.value.recipe_id) return false
    if (!selectedCaguama.value) return false
    return beerVolume.value <= selectedCaguama.value.remaining_volume_ml
})

function submitOpenCaguama() {
    openCaguamaForm.post(route('caguamas.open'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            // Reload only caguamas prop then pick up the new one
            router.reload({
                only: ['caguamas'],
                onSuccess: (p) => {
                    localCaguamas.value = p.props.caguamas
                    // Auto-select the newest (last opened)
                    const newest = [...p.props.caguamas].sort((a, b) => b.id - a.id)[0]
                    if (newest) form.value.caguama_id = newest.id
                    showOpenCaguama.value = false
                    openCaguamaForm.reset()
                },
            })
        },
    })
}

function submitNewRecipe() {
    newRecipeForm.post(route('michelada-recipes.quick'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.reload({
                only: ['recipes'],
                onSuccess: (p) => {
                    localRecipes.value = p.props.recipes
                    // Auto-select the newest
                    const newest = [...p.props.recipes].sort((a, b) => b.id - a.id)[0]
                    if (newest) form.value.recipe_id = newest.id
                    showNewRecipe.value = false
                    newRecipeForm.reset()
                    newRecipeForm.container_volume_ml = 800
                },
            })
        },
    })
}

function confirm() {
    emit('confirm', {
        caguama_id: form.value.caguama_id,
        michelada_recipe_id: form.value.recipe_id,
        add_in_ids: form.value.add_in_ids,
        quantity: form.value.quantity,
    })
}
</script>
