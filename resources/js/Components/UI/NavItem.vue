<template>
    <Link
        :href="href"
        class="flex items-center gap-3 rounded-lg font-medium transition-colors"
        :class="[
            size === 'sm' ? 'px-2 py-1.5 text-xs' : 'px-2 py-2 text-sm',
            active
                ? 'bg-green-900/50 text-green-400 border border-green-800/50'
                : 'text-neutral-400 hover:text-white hover:bg-neutral-800 border border-transparent',
        ]"
    >
        <span v-if="$slots.icon" class="flex-shrink-0">
            <slot name="icon" />
        </span>
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    href: { type: String, required: true },
    active: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
})
</script>
