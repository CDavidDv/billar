<template>
    <div class="flex h-screen bg-neutral-950 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 bg-neutral-900 border-r border-neutral-800 flex flex-col">
            <!-- Logo -->
            <div class="px-5 py-5 border-b border-neutral-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-700 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">B</span>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm leading-tight">Billar</p>
                        <p class="text-neutral-500 text-xs">Sistema de Control</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <!-- Cajero / Bartender -->
                <div class="mb-4">
                    <p class="px-2 mb-1 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Operaciones</p>
                    <NavItem :href="route('tables.index')" :active="route().current('tables.*')">
                        <template #icon><TableCellsIcon class="w-5 h-5" /></template>
                        Mesas
                    </NavItem>
                    <NavItem :href="route('floor-plan.index')" :active="route().current('floor-plan.*')">
                        <template #icon><MapIcon class="w-5 h-5" /></template>
                        Mapa
                    </NavItem>
                    <NavItem :href="route('caguamas.index')" :active="route().current('caguamas.*')">
                        <template #icon><BeakerIcon class="w-5 h-5" /></template>
                        Caguamas
                    </NavItem>
                </div>

                <!-- Admin only -->
                <div v-if="$page.props.auth.user.roles?.includes('admin')" class="mb-4">
                    <p class="px-2 mb-1 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Administración</p>
                    <NavItem :href="route('dashboard')" :active="route().current('dashboard')">
                        <template #icon><ChartBarIcon class="w-5 h-5" /></template>
                        Dashboard
                    </NavItem>
                    <NavItem :href="route('admin.products.index')" :active="route().current('admin.products.*')">
                        <template #icon><CubeIcon class="w-5 h-5" /></template>
                        Productos
                    </NavItem>
                    <NavItem :href="route('admin.inventory.index')" :active="route().current('admin.inventory.*')">
                        <template #icon><ArchiveBoxIcon class="w-5 h-5" /></template>
                        Inventario
                    </NavItem>
                    <NavItem :href="route('admin.screens.index')" :active="route().current('admin.screens.*')">
                        <template #icon><TvIcon class="w-5 h-5" /></template>
                        Pantallas
                    </NavItem>

                    <!-- Submenu admin -->
                    <div class="mt-2">
                        <button @click="adminOpen = !adminOpen"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg text-sm font-medium text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors">
                            <div class="flex items-center gap-3">
                                <Cog6ToothIcon class="w-5 h-5" />
                                <span>Configuración</span>
                            </div>
                            <ChevronDownIcon class="w-4 h-4 transition-transform" :class="{ 'rotate-180': adminOpen }" />
                        </button>
                        <div v-if="adminOpen" class="ml-8 mt-1 space-y-1">
                            <NavItem :href="route('admin.tables.index')" :active="route().current('admin.tables.*')" size="sm">
                                Mesas
                            </NavItem>
                            <NavItem :href="route('admin.products.index')" :active="route().current('admin.products.*')" size="sm">
                                Catálogo
                            </NavItem>
                            <NavItem :href="route('admin.users.index')" :active="route().current('admin.users.*')" size="sm">
                                Usuarios
                            </NavItem>
                            <NavItem :href="route('admin.floor-plan.edit')" :active="route().current('admin.floor-plan.*')" size="sm">
                                Editor de Mapa
                            </NavItem>
                            <NavItem :href="route('admin.config.index')" :active="route().current('admin.config.*')" size="sm">
                                Precios y Config
                            </NavItem>
                            <NavItem :href="route('admin.branches.index')" :active="route().current('admin.branches.*')" size="sm">
                                Sucursales
                            </NavItem>
                            <NavItem :href="route('admin.add-ins.index')" :active="route().current('admin.add-ins.*')" size="sm">
                                Aditamentos
                            </NavItem>
                            <NavItem :href="route('admin.excel.index')" :active="route().current('admin.excel.*')" size="sm">
                                Importar / Exportar
                            </NavItem>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- User footer -->
            <div class="px-3 py-4 border-t border-neutral-800">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-8 h-8 bg-neutral-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-xs">{{ userInitials }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-neutral-500 text-xs capitalize">{{ $page.props.auth.user.roles?.[0] ?? 'usuario' }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button"
                        class="text-neutral-500 hover:text-red-400 transition-colors">
                        <ArrowLeftStartOnRectangleIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="h-14 bg-neutral-900 border-b border-neutral-800 flex items-center px-6 flex-shrink-0">
                <h1 class="text-white font-semibold text-lg">{{ title }}</h1>
                <div class="ml-auto flex items-center gap-4">
                    <!-- Low stock alert (admin only) -->
                    <Link
                        v-if="lowStockCount > 0 && $page.props.auth.user.roles?.includes('admin')"
                        :href="route('admin.inventory.index')"
                        class="relative flex items-center gap-1.5 px-2 py-1 rounded-lg bg-amber-900/40 border border-amber-700/60 text-amber-400 text-xs font-medium hover:bg-amber-900/60 transition-colors"
                        title="Productos con stock bajo"
                    >
                        <ExclamationTriangleIcon class="w-4 h-4" />
                        {{ lowStockCount }} stock bajo
                    </Link>

                    <!-- Branch selector -->
                    <div v-if="currentBranch" class="flex items-center gap-2">
                        <span v-if="branches.length <= 1" class="text-sm text-neutral-400">
                            {{ currentBranch.name }}
                        </span>
                        <select
                            v-else
                            v-model="currentBranchId"
                            @change="switchBranch"
                            class="bg-neutral-800 border border-neutral-700 rounded-lg px-2 py-1 text-sm text-white focus:outline-none focus:ring-1 focus:ring-green-500"
                        >
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>
                    <slot name="header-actions" />
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import NavItem from '@/Components/UI/NavItem.vue'
import {
    TableCellsIcon,
    MapIcon,
    BeakerIcon,
    ChartBarIcon,
    CubeIcon,
    ArchiveBoxIcon,
    TvIcon,
    Cog6ToothIcon,
    ChevronDownIcon,
    ArrowLeftStartOnRectangleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { usePage } from '@inertiajs/vue3'

defineProps({
    title: {
        type: String,
        default: '',
    },
})

const adminOpen = ref(false)
const page = usePage()

const branches = computed(() => page.props.auth?.branches || [])
const currentBranch = computed(() => page.props.auth?.currentBranch || null)
const currentBranchId = ref(page.props.auth?.currentBranch?.id || null)
const lowStockCount = computed(() => page.props.auth?.lowStockCount ?? 0)

const userInitials = computed(() => {
    const name = page.props.auth.user?.name ?? ''
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

function switchBranch() {
    if (!currentBranchId.value) return
    router.post(route('branch.switch'), { branch_id: currentBranchId.value })
}
</script>
