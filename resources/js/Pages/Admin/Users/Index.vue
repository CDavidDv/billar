<template>
  <AppLayout title="Usuarios">
    <template #header-actions>
      <button
        @click="openCreate"
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors"
      >
        <PlusIcon class="w-4 h-4" />
        Nuevo usuario
      </button>
    </template>

    <div class="p-6 space-y-6">

      <!-- Flash / errors -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.errors?.user" class="flex items-start gap-3 p-4 bg-red-900/30 border border-red-700/50 rounded-lg">
        <span class="text-red-400">✕</span>
        <p class="text-red-300 text-sm">{{ $page.props.errors.user }}</p>
      </div>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-neutral-700">
        <table class="w-full text-sm">
          <thead class="bg-neutral-900">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Usuario</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Rol</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider hidden sm:table-cell">Creado</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-700 bg-neutral-800">
            <tr v-for="user in users" :key="user.id" class="hover:bg-neutral-750 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-neutral-700 rounded-full flex items-center justify-center shrink-0">
                    <span class="text-white font-semibold text-xs">{{ user.name[0].toUpperCase() }}</span>
                  </div>
                  <span class="text-neutral-200 font-medium">{{ user.name }}</span>
                  <span v-if="user.id === $page.props.auth.user.id" class="text-xs text-neutral-500">(tú)</span>
                </div>
              </td>
              <td class="px-4 py-3 text-neutral-400">{{ user.email }}</td>
              <td class="px-4 py-3">
                <span
                  v-for="role in user.roles"
                  :key="role"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="roleBadge(role)"
                >
                  {{ role }}
                </span>
              </td>
              <td class="px-4 py-3 text-neutral-500 text-xs hidden sm:table-cell">{{ user.created_at }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="openEdit(user)" class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 text-xs font-semibold rounded-lg border border-neutral-600 transition-colors">
                    Editar
                  </button>
                  <button
                    @click="destroy(user)"
                    :disabled="user.id === $page.props.auth.user.id"
                    class="px-3 py-1.5 bg-red-900/50 hover:bg-red-800/50 disabled:opacity-30 disabled:cursor-not-allowed text-red-400 text-xs font-semibold rounded-lg border border-red-700/40 transition-colors"
                  >
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td colspan="5" class="px-4 py-10 text-center text-neutral-500">Sin usuarios</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-neutral-800 rounded-2xl border border-neutral-700 w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-700">
          <h2 class="text-white font-semibold text-lg">{{ editing ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
          <button @click="showModal = false" class="text-neutral-400 hover:text-white">✕</button>
        </div>
        <form @submit.prevent="submitForm" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Nombre</label>
            <input v-model="form.name" type="text" required class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-400">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Email</label>
            <input v-model="form.email" type="email" required class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">Rol</label>
            <select v-model="form.role" required class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
              <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
            </select>
            <p v-if="form.errors.role" class="mt-1 text-xs text-red-400">{{ form.errors.role }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-300 mb-1.5">
              {{ editing ? 'Nueva contraseña (dejar en blanco para no cambiar)' : 'Contraseña' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!editing"
              autocomplete="new-password"
              class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
          </div>

          <div class="flex gap-3 pt-1">
            <button type="button" @click="showModal = false" class="flex-1 px-4 py-2 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 font-semibold text-sm rounded-lg border border-neutral-600 transition-colors">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 text-white font-semibold text-sm rounded-lg transition-colors">
              {{ editing ? 'Guardar' : 'Crear' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  users: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)

const form = useForm({
  name: '',
  email: '',
  role: 'cajero',
  password: '',
})

function roleBadge(role) {
  const map = {
    admin: 'bg-amber-900/50 text-amber-400',
    cajero: 'bg-blue-900/50 text-blue-400',
    bartender: 'bg-purple-900/50 text-purple-400',
  }
  return map[role] ?? 'bg-neutral-700 text-neutral-400'
}

function openCreate() {
  editing.value = null
  form.reset()
  form.role = props.roles[1] ?? props.roles[0] ?? 'cajero'
  showModal.value = true
}

function openEdit(user) {
  editing.value = user
  form.name = user.name
  form.email = user.email
  form.role = user.roles[0] ?? 'cajero'
  form.password = ''
  showModal.value = true
}

function submitForm() {
  if (editing.value) {
    form.put(route('admin.users.update', editing.value.id), {
      onSuccess: () => { showModal.value = false },
    })
  } else {
    form.post(route('admin.users.store'), {
      onSuccess: () => { showModal.value = false },
    })
  }
}

function destroy(user) {
  if (!confirm(`¿Eliminar a ${user.name}? Esta acción no se puede deshacer.`)) return
  router.delete(route('admin.users.destroy', user.id))
}
</script>
