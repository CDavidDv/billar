<template>
  <apexchart
    type="heatmap"
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
  data: { type: Array, default: () => [] },
  height: { type: Number, default: 260 },
})

const series = computed(() => props.data)

const chartOptions = computed(() => ({
  chart: {
    toolbar: { show: false },
    background: 'transparent',
  },
  theme: { mode: 'dark' },
  dataLabels: { enabled: false },
  colors: ['#15803d'],
  xaxis: {
    type: 'category',
    labels: { style: { colors: '#a3a3a3', fontSize: '10px' } },
  },
  yaxis: {
    labels: { style: { colors: '#a3a3a3', fontSize: '11px' } },
  },
  plotOptions: {
    heatmap: {
      shadeIntensity: 0.6,
      colorScale: {
        ranges: [
          { from: 0, to: 0, color: '#262626', name: 'Sin ventas' },
          { from: 1, to: 500, color: '#14532d', name: 'Bajo' },
          { from: 501, to: 2000, color: '#15803d', name: 'Medio' },
          { from: 2001, to: 99999, color: '#4ade80', name: 'Alto' },
        ],
      },
    },
  },
  grid: { padding: { right: 10 } },
  tooltip: {
    theme: 'dark',
    y: { formatter: (v) => '$' + v.toLocaleString('es-MX', { minimumFractionDigits: 2 }) },
  },
}))
</script>
