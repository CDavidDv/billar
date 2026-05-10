<template>
  <AppLayout title="Importar / Exportar">
    <div class="p-6 space-y-6">

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
        <span class="text-green-400">✓</span>
        <p class="text-green-300 text-sm">{{ $page.props.flash.success }}</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 bg-neutral-900 rounded-xl p-1 w-fit">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
          :class="activeTab === tab.id ? 'bg-neutral-700 text-white' : 'text-neutral-400 hover:text-white'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Import products tab -->
      <div v-if="activeTab === 'import'" class="space-y-5">
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-6 space-y-5">
          <div>
            <h3 class="text-white font-semibold text-lg">Importar catálogo de productos</h3>
            <p class="text-neutral-400 text-sm mt-1">Sube un archivo Excel (.xlsx) o CSV con las columnas: <code class="text-green-400">nombre</code>, <code class="text-green-400">precio</code>, <code class="text-green-400">categoria</code>, <code class="text-amber-400">descripcion</code> (opcional)</p>
          </div>

          <!-- Template download -->
          <div class="flex items-center gap-3 p-4 bg-neutral-900 rounded-lg border border-neutral-700">
            <DocumentArrowDownIcon class="w-8 h-8 text-green-400 shrink-0" />
            <div>
              <p class="text-white text-sm font-medium">Plantilla de importación</p>
              <p class="text-neutral-400 text-xs">Descarga la plantilla con las columnas correctas</p>
            </div>
            <button @click="downloadTemplate" class="ml-auto px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 text-neutral-200 text-xs font-semibold rounded-lg border border-neutral-600 transition-colors">
              Descargar
            </button>
          </div>

          <!-- File upload -->
          <form @submit.prevent="submitImport" class="space-y-4">
            <div
              class="relative border-2 border-dashed rounded-xl p-8 text-center transition-colors"
              :class="dragOver ? 'border-green-500 bg-green-900/10' : 'border-neutral-600 hover:border-neutral-500'"
              @dragover.prevent="dragOver = true"
              @dragleave="dragOver = false"
              @drop.prevent="onFileDrop"
            >
              <input
                ref="fileInput"
                type="file"
                accept=".xlsx,.csv"
                class="absolute inset-0 opacity-0 cursor-pointer"
                @change="onFileChange"
              />
              <ArrowUpTrayIcon class="w-10 h-10 text-neutral-500 mx-auto mb-3" />
              <p class="text-neutral-300 font-medium">Arrastra tu archivo aquí</p>
              <p class="text-neutral-500 text-sm mt-1">o haz clic para seleccionar</p>
              <p v-if="selectedFile" class="mt-3 text-green-400 text-sm font-medium">
                ✓ {{ selectedFile.name }}
              </p>
            </div>

            <div v-if="importErrors.length" class="space-y-2">
              <p class="text-red-400 text-sm font-medium">{{ importErrors.length }} errores encontrados:</p>
              <div class="overflow-hidden rounded-lg border border-red-700/50 max-h-48 overflow-y-auto">
                <table class="w-full text-xs">
                  <thead class="bg-neutral-900 sticky top-0">
                    <tr>
                      <th class="px-3 py-2 text-left text-neutral-400">Fila</th>
                      <th class="px-3 py-2 text-left text-neutral-400">Campo</th>
                      <th class="px-3 py-2 text-left text-neutral-400">Error</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-neutral-700">
                    <tr v-for="(err, i) in importErrors" :key="i" class="bg-red-900/10">
                      <td class="px-3 py-2 text-neutral-300">{{ err.row }}</td>
                      <td class="px-3 py-2 text-amber-400">{{ err.attribute }}</td>
                      <td class="px-3 py-2 text-red-300">{{ err.errors.join(', ') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <button
              type="submit"
              :disabled="!selectedFile || importForm.processing"
              class="px-6 py-2.5 bg-green-700 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-lg transition-colors"
            >
              {{ importForm.processing ? 'Importando...' : 'Importar productos' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Export sales tab -->
      <div v-if="activeTab === 'export-sales'" class="space-y-5">
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-6 space-y-5">
          <div>
            <h3 class="text-white font-semibold text-lg">Exportar ventas</h3>
            <p class="text-neutral-400 text-sm mt-1">Descarga un Excel con todas las órdenes cerradas en el período seleccionado.</p>
          </div>

          <form @submit.prevent="submitExportSales" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-300 mb-1.5">Desde</label>
                <input
                  v-model="salesForm.from"
                  type="date"
                  required
                  class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-neutral-300 mb-1.5">Hasta</label>
                <input
                  v-model="salesForm.to"
                  type="date"
                  required
                  class="w-full bg-neutral-700 border border-neutral-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
              </div>
            </div>

            <div v-if="salesForm.errors.from || salesForm.errors.to" class="text-xs text-red-400">
              {{ salesForm.errors.from || salesForm.errors.to }}
            </div>

            <button
              type="submit"
              :disabled="!salesForm.from || !salesForm.to"
              class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-700 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm rounded-lg transition-colors"
            >
              <ArrowDownTrayIcon class="w-4 h-4" />
              Descargar Excel
            </button>
          </form>
        </div>
      </div>

      <!-- Export inventory tab -->
      <div v-if="activeTab === 'export-inventory'" class="space-y-5">
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-6 space-y-5">
          <div>
            <h3 class="text-white font-semibold text-lg">Exportar inventario</h3>
            <p class="text-neutral-400 text-sm mt-1">Descarga el stock actual de todos los productos con su estado de alerta.</p>
          </div>
          <a
            :href="route('admin.excel.export-inventory')"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-700 hover:bg-green-600 text-white font-semibold text-sm rounded-lg transition-colors"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
            Descargar inventario actual
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { DocumentArrowDownIcon, ArrowUpTrayIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const tabs = [
  { id: 'import', label: 'Importar productos' },
  { id: 'export-sales', label: 'Exportar ventas' },
  { id: 'export-inventory', label: 'Exportar inventario' },
]

const activeTab = ref('import')
const dragOver = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)

const importErrors = computed(() => {
  return (window.__page?.props?.flash?.import_errors) ?? []
})

const importForm = useForm({ file: null })
const salesForm = useForm({ from: '', to: '' })

function onFileChange(e) {
  selectedFile.value = e.target.files[0] ?? null
}

function onFileDrop(e) {
  dragOver.value = false
  selectedFile.value = e.dataTransfer.files[0] ?? null
}

function submitImport() {
  if (!selectedFile.value) return
  importForm.file = selectedFile.value
  importForm.post(route('admin.excel.import-products'), {
    onSuccess: () => {
      selectedFile.value = null
      if (fileInput.value) fileInput.value.value = ''
    },
  })
}

function submitExportSales() {
  // Direct navigation to trigger file download
  const params = new URLSearchParams({ from: salesForm.from, to: salesForm.to })
  window.location.href = route('admin.excel.export-sales') + '?' + params.toString()
}

function downloadTemplate() {
  const rows = [
    ['nombre', 'precio', 'categoria', 'descripcion'],
    ['Coca-Cola 600ml', '35', 'Refrescos', ''],
    ['Michelada especial', '80', 'Bebidas preparadas', 'Con clamato y tamarindo'],
  ]
  const csv = rows.map(r => r.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'plantilla_productos.csv'
  a.click()
  URL.revokeObjectURL(url)
}
</script>
