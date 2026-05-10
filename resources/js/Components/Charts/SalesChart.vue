<template>
  <apexchart
    type="area"
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
  days: { type: Array, default: () => [] },
  totals: { type: Array, default: () => [] },
  height: { type: Number, default: 280 },
})

const series = computed(() => [
  { name: 'Ventas ($)', data: props.totals },
])

const chartOptions = computed(() => ({
  chart: {
    toolbar: { show: false },
    background: 'transparent',
    animations: { enabled: false },
  },
  theme: { mode: 'dark' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] },
  },
  colors: ['#16a34a'],
  xaxis: {
    categories: props.days,
    labels: { style: { colors: '#a3a3a3', fontSize: '11px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#a3a3a3', fontSize: '11px' },
      formatter: (v) => '$' + v.toLocaleString('es-MX'),
    },
  },
  grid: { borderColor: '#404040', strokeDashArray: 3 },
  tooltip: {
    theme: 'dark',
    y: { formatter: (v) => '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 }) },
  },
}))
</script>
