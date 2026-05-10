<script setup>
import { ref } from 'vue'

const props = defineProps({
    categories: { type: Array, default: () => [] },
    business_name: { type: String, default: 'Billar' },
    logo_text: { type: String, default: 'B' },
})

const activeCategory = ref(0)
</script>

<template>
    <div class="min-h-screen bg-neutral-950">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-neutral-900/95 backdrop-blur border-b border-neutral-800">
            <div class="px-4 py-4 text-center">
                <div class="inline-flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">{{ logo_text }}</span>
                    </div>
                    <h1 class="text-white font-bold">{{ business_name }}</h1>
                </div>
                <p class="text-neutral-500 text-xs mt-1">Menú Digital</p>
            </div>

            <!-- Category tabs -->
            <div class="flex overflow-x-auto px-2 pb-3 gap-1 scrollbar-hide">
                <button
                    v-for="(cat, idx) in categories"
                    :key="cat.id"
                    @click="activeCategory = idx"
                    class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                    :class="activeCategory === idx ? 'bg-green-700 text-white' : 'bg-neutral-800 text-neutral-400 hover:text-neutral-200'"
                >
                    {{ cat.name }}
                </button>
            </div>
        </header>

        <!-- Products -->
        <main class="p-4 pb-20">
            <div v-if="categories[activeCategory]" class="space-y-3">
                <div
                    v-for="product in categories[activeCategory].products"
                    :key="product.id"
                    class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden"
                    :class="{ 'opacity-50': !product.is_available }"
                >
                    <div class="flex">
                        <!-- Image -->
                        <div v-if="product.image_url" class="w-24 h-24 sm:w-28 sm:h-28 shrink-0">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
                        </div>

                        <!-- Info -->
                        <div class="flex-1 p-3 min-w-0">
                            <div class="flex justify-between items-start gap-2">
                                <h3 class="text-white font-semibold">{{ product.name }}</h3>
                                <span class="text-green-400 font-bold whitespace-nowrap">${{ product.price.toFixed(2) }}</span>
                            </div>
                            <p v-if="product.description" class="text-neutral-500 text-sm mt-1 line-clamp-2">
                                {{ product.description }}
                            </p>
                            <p v-if="!product.is_available" class="text-red-400 text-xs mt-2">
                                Agotado
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty category -->
                <div v-if="categories[activeCategory].products.length === 0" class="text-center py-10">
                    <p class="text-neutral-500">No hay productos disponibles</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="fixed bottom-0 left-0 right-0 bg-neutral-900/95 backdrop-blur border-t border-neutral-800 py-3 text-center">
            <p class="text-neutral-600 text-xs">Escanea el QR en tu mesa para pedir</p>
        </footer>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>