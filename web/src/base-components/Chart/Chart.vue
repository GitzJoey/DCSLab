<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import ChartJs, { ChartConfiguration } from "chart.js/auto";
import { CanvasHTMLAttributes, ref, onMounted, inject, watch } from "vue";

export interface ChartElement extends HTMLCanvasElement {
  instance: ChartJs;
}

export type ProvideChart = (el: ChartElement) => void;

interface ChartProps
  extends /* @vue-ignore */ CanvasHTMLAttributes,
    /* @vue-ignore */ ChartConfiguration {
  width?: number;
  height?: number;
  type: ChartConfiguration["type"];
  data: ChartConfiguration["data"];
  options: ChartConfiguration["options"];
  refKey?: string;
}

const props = defineProps<ChartProps>();

const chartRef = ref<ChartElement>();

const init = (el: ChartElement, props: ChartProps) => {
  const canvas = el?.getContext("2d");
  if (canvas) {
    const chart = new ChartJs(canvas, {
      type: props.type,
      data: props.data,
      options: props.options,
    });

    // Attach ChartJs instance
    el.instance = chart;
  }
};

const bindInstance = (el: ChartElement) => {
  if (props.refKey) {
    const bind = inject<ProvideChart>(`bind[${props.refKey}]`);
    if (bind) {
      bind(el);
    }
  }
};

onMounted(() => {
  if (chartRef.value) {
    bindInstance(chartRef.value);
    init(chartRef.value, props);
  }

  watch(props, () => {
    if (chartRef.value) {
      chartRef.value.instance.data = props.data;
      if (props.options) {
        chartRef.value.instance.options = props.options;
      }
      chartRef.value.instance.update();
    }
  });
});
</script>

<template>
  <div
    :style="{
      width: `${width}px`,
      height: `${height}px`,
    }"
  >
    <canvas ref="chartRef" :class="$attrs.class"></canvas>
  </div>
</template>
