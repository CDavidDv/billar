<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    branches: Array,
})

const form = useForm({
    email: '',
    password: '',
    branch_id: props.branches?.length === 1 ? props.branches[0].id : null,
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <form @submit.prevent="submit" class="space-y-5">
            <!-- Status -->
            <div v-if="status" class="p-3 bg-green-900/40 border border-green-700/50 rounded-lg text-green-400 text-sm text-center">
                {{ status }}
            </div>

            <!-- Branch selector (solo si hay más de 1 sucursal) -->
            <div v-if="branches && branches.length > 1">
                <label class="block text-sm font-medium text-neutral-300 mb-1.5">Sucursal</label>
                <select
                    v-model="form.branch_id"
                    required
                    class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    :class="{ 'border-red-600': form.errors.branch_id }"
                >
                    <option :value="null" disabled>Selecciona sucursal</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                        {{ branch.name }}
                    </option>
                </select>
                <p v-if="form.errors.branch_id" class="mt-1 text-xs text-red-400">{{ form.errors.branch_id }}</p>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-1.5">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@ejemplo.com"
                    class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-3 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    :class="{ 'border-red-600': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-1.5">Contraseña</label>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-3 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    :class="{ 'border-red-600': form.errors.password }"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me -->
            <label class="flex items-center gap-2 text-sm text-neutral-400 cursor-pointer">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="rounded bg-neutral-800 border-neutral-600 text-green-600 focus:ring-green-500"
                />
                Recordarme
            </label>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold rounded-lg transition-colors"
                >
                    {{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}
                </button>
            </div>

            <!-- Forgot password -->
            <div class="text-center pt-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-neutral-500 hover:text-neutral-300"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>