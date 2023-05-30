<script setup lang="ts">
import { computed } from "vue";
import { ChartData, ChartOptions } from "chart.js/auto";
import { useColorSchemeStore } from "../../stores/color-scheme";
import Chart from "../../base-components/Chart";
import { getColor } from "../../utils/colors";
import { randomNumbers } from "../../utils/helper";

const props = defineProps<{
  width?: number;
  height?: number;
  lineColor?: string;
}>();

const colorScheme = computed(() => useColorSchemeStore().colorScheme);

const data = computed<ChartData>(() => {
  return {
    labels: [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    datasets: [
      {
        label: "# of Votes",
        data: randomNumbers(0, 5, 12),
        borderWidth: 2,
        borderColor:
          colorScheme.value && props.lineColor?.length
            ? props.lineColor
            : getColor("primary", 0.8),
        backgroundColor: "transparent",
        pointBorderColor: "transparent",
        tension: 0.4,
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
        ticks: {
          display: false,
        },
        grid: {
          display: false,
          drawBorder: false,
        },
      },
      y: {
        ticks: {
          display: false,
        },
        grid: {
          display: false,
          drawBorder: false,
        },
      },
    },
  };
});
</script>

<template>
  <Chart
    type="line"
    :width="props.width"
    :height="props.height"
    :data="data"
    :options="options"
  />
</template>
