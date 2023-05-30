<script setup lang="ts">
import { onMounted, computed, ref, provide } from "vue";
import { ChartData, ChartOptions } from "chart.js/auto";
import { useColorSchemeStore } from "../../stores/color-scheme";
import Chart, { ChartElement } from "../../base-components/Chart";
import { getColor } from "../../utils/colors";

const props = defineProps<{
  width?: number;
  height?: number;
}>();

const colorScheme = computed(() => useColorSchemeStore().colorScheme);
const reportBarChartRef = ref<ChartElement>();

provide("bind[reportBarChartRef]", (el: ChartElement) => {
  reportBarChartRef.value = el;
});

// Fake visitor data
const reportBarChartData = new Array(40).fill(0).map((data, key) => {
  if (key % 3 == 0 || key % 5 == 0) {
    return Math.ceil(Math.random() * (0 - 20) + 20);
  } else {
    return Math.ceil(Math.random() * (0 - 7) + 7);
  }
});

// Fake visitor bar color
const reportBarChartColor = computed(() => {
  return reportBarChartData.map((data) => {
    if (data >= 8 && data <= 14) {
      return colorScheme.value ? getColor("primary", 0.65) : "";
    } else if (data >= 15) {
      return colorScheme.value ? getColor("primary") : "";
    }

    return colorScheme.value ? getColor("primary", 0.35) : "";
  });
});

const data = computed<ChartData>(() => {
  return {
    labels: reportBarChartData,
    datasets: [
      {
        label: "Html Template",
        barPercentage: 0.5,
        barThickness: 6,
        maxBarThickness: 8,
        minBarLength: 2,
        data: reportBarChartData,
        backgroundColor: reportBarChartColor.value,
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

onMounted(() => {
  setInterval(() => {
    if (reportBarChartRef.value) {
      const chartInstance = reportBarChartRef.value.instance;
      const chartConfig = chartInstance.config;

      // Swap visitor data
      const newData = chartConfig.data.datasets[0].data[0];
      chartConfig.data.datasets[0].data.shift();
      chartConfig.data.datasets[0].data.push(newData);
      chartInstance.update();

      // Swap visitor bar color
      if (Array.isArray(chartConfig.data.datasets[0].backgroundColor)) {
        const newColor = chartConfig.data.datasets[0].backgroundColor[0];
        chartConfig.data.datasets[0].backgroundColor.shift();
        chartConfig.data.datasets[0].backgroundColor.push(newColor);
      }
      chartInstance.update();
    }
  }, 1000);
});
</script>

<template>
  <Chart
    type="bar"
    :width="props.width"
    :height="props.height"
    :data="data"
    :options="options"
    ref-key="reportBarChartRef"
  />
</template>
