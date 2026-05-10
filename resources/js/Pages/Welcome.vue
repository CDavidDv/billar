<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    canLogin: Boolean,
})
</script>

<template>
    <div class="min-h-screen bg-neutral-950 flex flex-col">
        <!-- Hero -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-16">
            <div class="w-full max-w-md text-center">
                <!-- Logo -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white">Billar</h1>
                    <p class="text-neutral-500 mt-1">Sistema de Control</p>
                </div>

                <!-- Login -->
                <div v-if="canLogin" class="space-y-3">
                    <Link
                        :href="route('login')"
                        class="block w-full py-3 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors"
                    >
                        Iniciar sesión
                    </Link>
                </div>

                <!-- Info -->
                <div class="mt-10 pt-8 border-t border-neutral-800">
                    <p class="text-neutral-600 text-sm">
                        Abiertos: Lun-Dom de 10:00 a 02:00
                    </p>
                    <p class="text-neutral-500 text-xs mt-1">
                        📍 Av. Principal 123, Centro
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="py-6 text-center text-neutral-600 text-xs">
            Sistema POS v1.0
        </footer>
    </div>
</template>