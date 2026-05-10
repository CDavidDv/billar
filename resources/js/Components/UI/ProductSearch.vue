<template>
    <div class="flex gap-2">
        <!-- Product search -->
        <div class="flex-1 relative">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar producto..."
                class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
                @focus="open = true"
                @blur="onBlur"
                @keydown.down.prevent="moveDown"
                @keydown.up.prevent="moveUp"
                @keydown.enter.prevent="selectHighlighted"
                @keydown.escape="open = false"
            />
            <!-- Dropdown -->
            <div
                v-if="open && filtered.length > 0"
                class="absolute z-20 top-full mt-1 left-0 right-0 bg-neutral-800 border border-neutral-600 rounded-lg shadow-xl overflow-hidden max-h-56 overflow-y-auto"
            >
                <button
                    v-for="(product, i) in filtered"
                    :key="product.id"
                    type="button"
                    class="w-full flex items-center justify-between px-3 py-2 text-sm hover:bg-neutral-700 transition-colors text-left"
                    :class="i === highlighted ? 'bg-neutral-700' : ''"
                    @mousedown.prevent="selectProduct(product)"
                >
                    <span class="text-white truncate">{{ product.name }}</span>
                    <span class="text-amber-400 font-semibold ml-2 flex-shrink-0">${{ product.price.toFixed(2) }}</span>
                </button>
            </div>
        </div>

        <input
            :value="unitPrice"
            @input="$emit('update:unitPrice', parseFloat($event.target.value) || '')"
            type="number"
            min="0"
            step="0.50"
            placeholder="Precio"
            class="w-24 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
            @keyup.enter="$emit('submit')"
        />
        <input
            :value="quantity"
            @input="$emit('update:quantity', parseInt($event.target.value) || 1)"
            type="number"
            min="1"
            placeholder="Cant"
            class="w-16 bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm placeholder-neutral-500 focus:outline-none focus:border-green-600"
            @keyup.enter="$emit('submit')"
        />
        <button
            @click="$emit('submit')"
            :disabled="disabled"
            class="px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50"
        >
            +
        </button>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    products: { type: Array, default: () => [] },
    productName: { type: String, default: '' },
    unitPrice: { type: [Number, String], default: '' },
    quantity: { type: Number, default: 1 },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:productName', 'update:unitPrice', 'update:quantity', 'submit', 'select'])

const search = ref(props.productName)
const open = ref(false)
const highlighted = ref(-1)

watch(() => props.productName, (v) => { search.value = v })
watch(search, (v) => {
    emit('update:productName', v)
    highlighted.value = -1
})

const filtered = computed(() => {
    if (!search.value) return props.products.slice(0, 10)
    const q = search.value.toLowerCase()
    return props.products.filter(p => p.name.toLowerCase().includes(q)).slice(0, 10)
})

function selectProduct(product) {
    search.value = product.name
    emit('update:productName', product.name)
    emit('update:unitPrice', product.price)
    emit('select', product)
    open.value = false
}

function onBlur() {
    setTimeout(() => { open.value = false }, 150)
}

function moveDown() {
    if (!open.value) open.value = true
    highlighted.value = Math.min(highlighted.value + 1, filtered.value.length - 1)
}

function moveUp() {
    highlighted.value = Math.max(highlighted.value - 1, -1)
}

function selectHighlighted() {
    if (highlighted.value >= 0 && filtered.value[highlighted.value]) {
        selectProduct(filtered.value[highlighted.value])
    } else {
        emit('submit')
    }
}
</script>
