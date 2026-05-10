<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <div class="min-h-screen bg-neutral-950 flex flex-col items-center justify-center px-4">
        <!-- Logo -->
        <div class="mb-8">
            <Link href="/" class="flex items-center gap-3">
                <div class="w-14 h-14 bg-green-700 rounded-xl flex items-center justify-center shadow-lg shadow-green-900/30">
                    <span class="text-white font-bold text-2xl">B</span>
                </div>
                <div>
                    <p class="text-white font-bold text-xl">Billar</p>
                    <p class="text-neutral-500 text-xs">Sistema de Control</p>
                </div>
            </Link>
        </div>

        <!-- Card -->
        <div class="w-full max-w-sm">
            <slot />
        </div>
    </div>
</template>