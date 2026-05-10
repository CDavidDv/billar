<template>
  <AppLayout title="Dashboard">
    <div class="p-6 space-y-6">

      <!-- KPI row -->
      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Ventas hoy</p>
          <p class="text-3xl font-bold text-white mt-1">${{ formatMXN(summary.sales_today) }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Órdenes hoy</p>
          <p class="text-3xl font-bold text-white mt-1">{{ summary.orders_today }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Ticket promedio</p>
          <p class="text-3xl font-bold text-amber-400 mt-1">${{ formatMXN(summary.avg_ticket) }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Mesas activas</p>
          <p class="text-3xl font-bold text-white mt-1">{{ summary.active_tables }}</p>
        </div>
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 p-5">
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Caguamas abiertas</p>
          <p class="text-3xl font-bold text-white mt-1">{{ summary.active_caguamas }}</p>
        </div>
        <div
          class="bg-neutral-800 rounded-xl border p-5"
          :class="summary.low_stock_count > 0 ? 'border-amber-700/50' : 'border-neutral-700'"
        >
          <p class="text-neutral-400 text-xs font-medium uppercase tracking-wider">Stock bajo</p>
          <p class="text-3xl font-bold mt-1" :class="summary.low_stock_count > 0 ? 'text-amber-400' : 'text-white'">
            {{ summary.low_stock_count }}
          </p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Weekly sales -->
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
          <div class="px-5 py-4 border-b border-neutral-700">
            <h3 class="text-white font-semibold text-lg">Ventas — últimos 7 días</h3>
          </div>
          <div class="p-5">
            <SalesChart :days="weekly.days" :totals="weekly.totals" />
          </div>
        </div>

        <!-- Top products -->
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
          <div class="px-5 py-4 border-b border-neutral-700">
            <h3 class="text-white font-semibold text-lg">Productos más vendidos</h3>
          </div>
          <div class="p-5">
            <TopProductsChart :products="topProducts" />
          </div>
        </div>
      </div>

      <!-- Heatmap -->
      <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-700">
          <h3 class="text-white font-semibold text-lg">Actividad por día y hora</h3>
          <p class="text-neutral-400 text-sm mt-0.5">Ventas acumuladas por franja horaria</p>
        </div>
        <div class="p-5">
          <HeatmapChart :data="heatmap" :height="220" />
        </div>
      </div>

      <!-- Bottom row -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Table occupancy -->
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
          <div class="px-5 py-4 border-b border-neutral-700">
            <h3 class="text-white font-semibold text-lg">Ocupación por mesa</h3>
          </div>
          <div class="overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-neutral-900">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Mesa</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Sesiones</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Promedio</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-700">
                <tr v-for="row in tableOccupancy" :key="row.name" class="hover:bg-neutral-750 transition-colors">
                  <td class="px-4 py-3 text-neutral-200">{{ row.name }}</td>
                  <td class="px-4 py-3 text-right text-white font-medium">{{ row.sessions }}</td>
                  <td class="px-4 py-3 text-right text-neutral-400">{{ row.avg_minutes }} min</td>
                </tr>
                <tr v-if="!tableOccupancy.length">
                  <td colspan="3" class="px-4 py-6 text-center text-neutral-500">Sin datos aún</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Low stock alerts -->
        <div class="bg-neutral-800 rounded-xl border border-neutral-700 overflow-hidden">
          <div class="px-5 py-4 border-b border-neutral-700">
            <h3 class="text-white font-semibold text-lg">Alertas de stock bajo</h3>
          </div>
          <div class="overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-neutral-900">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400 uppercase tracking-wider">Producto</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Stock</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 uppercase tracking-wider">Mínimo</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-700">
                <tr v-for="item in lowStock" :key="item.product" class="hover:bg-neutral-750 transition-colors">
                  <td class="px-4 py-3">
                    <p class="text-neutral-200">{{ item.product }}</p>
                    <p class="text-neutral-500 text-xs">{{ item.category }}</p>
                  </td>
                  <td class="px-4 py-3 text-right text-amber-400 font-medium">{{ item.quantity }} {{ item.unit }}</td>
                  <td class="px-4 py-3 text-right text-neutral-500">{{ item.min_stock }} {{ item.unit }}</td>
                </tr>
                <tr v-if="!lowStock.length">
                  <td colspan="3" class="px-4 py-6 text-center text-neutral-500">
                    <span class="text-green-400">✓</span> Todo el inventario en orden
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SalesChart from '@/Components/Charts/SalesChart.vue'
import TopProductsChart from '@/Components/Charts/TopProductsChart.vue'
import HeatmapChart from '@/Components/Charts/HeatmapChart.vue'

defineProps({
  summary: { type: Object, default: () => ({}) },
  weekly: { type: Object, default: () => ({ days: [], totals: [], counts: [] }) },
  topProducts: { type: Array, default: () => [] },
  heatmap: { type: Array, default: () => [] },
  tableOccupancy: { type: Array, default: () => [] },
  lowStock: { type: Array, default: () => [] },
})

function formatMXN(value) {
  return Number(value ?? 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
