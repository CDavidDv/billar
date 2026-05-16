<template>
    <div class="flex flex-col h-full">
        <!-- Category tabs -->
        <div class="flex gap-1 overflow-x-auto pb-2 flex-shrink-0 scrollbar-none">
            <button
                v-for="cat in categories"
                :key="cat.name"
                @click="activeCategory = cat.name"
                class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap"
                :class="activeCategory === cat.name
                    ? 'bg-green-700 text-white'
                    : 'bg-neutral-700 text-neutral-300 hover:bg-neutral-600'"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Product grid -->
        <div class="flex-1 overflow-y-auto mt-2">
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-2">
                <button
                    v-for="product in filteredProducts"
                    :key="product.id"
                    @click="!disabled && $emit('select', product)"
                    :disabled="disabled"
                    class="flex flex-col items-center justify-center p-3 rounded-xl border text-center transition-colors min-h-[80px] active:scale-95"
                    :class="[
                        product.is_beer_product
                            ? 'border-green-700/50 hover:border-green-600 hover:bg-green-900/20'
                            : 'border-neutral-700 hover:border-neutral-500 hover:bg-neutral-700/50',
                        disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
                        'bg-neutral-800'
                    ]"
                >
                    <span class="text-white text-xs font-semibold leading-tight line-clamp-2">{{ product.name }}</span>
                    <span class="text-amber-400 text-sm font-bold mt-1">${{ product.price.toFixed(0) }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    products: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
})

defineEmits(['select'])

const categories = computed(() => {
    const map = new Map()
    for (const p of props.products) {
        if (!map.has(p.category_name)) {
            map.set(p.category_name, { name: p.category_name, sort: p.category_sort ?? 99 })
        }
    }
    return [...map.values()].sort((a, b) => a.sort - b.sort)
})

const activeCategory = ref(categories.value[0]?.name ?? '')

const filteredProducts = computed(() =>
    props.products.filter(p => p.category_name === activeCategory.value)
)
</script>
