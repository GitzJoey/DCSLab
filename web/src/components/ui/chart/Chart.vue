<script lang="ts" setup generic="TType extends ChartType">
import ChartJs from "chart.js/auto";
import { ref, onMounted } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { chart } from "@midoneui/core/styles/chart.styles";
import type { ChartType, ChartConfiguration } from "chart.js";

const {
  class: className,
  config,
  getRef,
  ...props
} = defineProps<{
  class?: string;
  config: ChartConfiguration<TType>;
  getRef?: (chart: ChartJs<TType>) => void;
}>();

const chartRef = ref<
  | (HTMLCanvasElement & {
      instance?: ChartJs<TType>;
    })
  | null
>(null);

onMounted(() => {
  if (chartRef.value && !chartRef.value.instance) {
    chartRef.value.instance = new ChartJs(chartRef.value, config);
    getRef?.(chartRef.value.instance);
  }
});
</script>

<template>
  <canvas :class="cn(chart, className)" ref="chartRef" v-bind="props" />
</template>
