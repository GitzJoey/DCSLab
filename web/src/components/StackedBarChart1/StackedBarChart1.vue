<script setup lang="ts">
import { computed } from "vue";
import { ChartData, ChartOptions } from "chart.js/auto";
import { useDarkModeStore } from "../../stores/dark-mode";
import Chart from "../../base-components/Chart";
import { getColor } from "../../utils/colors";
import { randomNumbers } from "../../utils/helper";

const props = defineProps<{
  width?: number;
  height?: number;
  lineColor?: string;
}>();

const darkMode = computed(() => useDarkModeStore().darkMode);

const data = computed<ChartData>(() => {
  return {
    labels: [...Array(16).keys()],
    datasets: [
      {
        label: "Html Template",
        barPercentage: 0.5,
        barThickness: 6,
        maxBarThickness: 8,
        minBarLength: 2,
        backgroundColor: getColor("primary", 0.8),
        data: randomNumbers(-100, 100, 16),
      },
      {
        label: "VueJs Template",
        barPercentage: 0.5,
        barThickness: 6,
        maxBarThickness: 8,
        minBarLength: 2,
        backgroundColor: darkMode.value
          ? getColor("darkmode.200")
          : getColor("slate.300"),
        data: randomNumbers(-100, 100, 16),
      },
    ],
  };
});

const options = computed<ChartOptions>(() => {
  return {
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
    },
    scales: {
      x: {
        stacked: true,
        ticks: {
          font: {
            size: 12,
          },
          color: getColor("slate.500", 0.8),
        },
        grid: {
          display: false,
          drawBorder: false,
        },
      },
      y: {
        stacked: true,
        ticks: {
          font: {
            size: 12,
          },
          color: getColor("slate.500", 0.8),
          callback: function (value) {
            return "$" + value;
          },
        },
        grid: {
          color: darkMode.value
            ? getColor("slate.500", 0.3)
            : getColor("slate.300"),
          borderDash: [2, 2],
          drawBorder: false,
        },
      },
    },
  };
});
</script>

<template>
  <Chart
    type="bar"
    :width="props.width"
    :height="props.height"
    :data="data"
    :options="options"
  />
</template>
