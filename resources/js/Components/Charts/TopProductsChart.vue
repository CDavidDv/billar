<template>
  <apexchart
    type="bar"
    :height="height"
    :options="chartOptions"
    :series="series"
  />
</template>

<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const apexchart = VueApexCharts

const props = defineProps({
  products: { type: Array, default: () => [] },
  height: { type: Number, default: 300 },
})

const series = computed(() => [
  { name: 'Unidades', data: props.products.map(p => p.qty) },
])

const chartOptions = computed(() => ({
  chart: {
    toolbar: { show: false },
    background: 'transparent',
  },
  theme: { mode: 'dark' },
  plotOptions: {
    bar: { horizontal: true, borderRadius: 4, barHeight: '65%' },
  },
  dataLabels: { enabled: false },
  colors: ['#15803d'],
  xaxis: {
    categories: props.products.map(p => p.name),
    labels: { style: { colors: '#a3a3a3', fontSize: '11px' } },
  },
  yaxis: {
    labels: { style: { colors: '#a3a3a3', fontSize: '11px' } },
  },
  grid: { borderColor: '#404040', strokeDashArray: 3 },
  tooltip: { theme: 'dark' },
}))
</script>
