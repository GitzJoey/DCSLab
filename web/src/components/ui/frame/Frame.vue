<script lang="ts" setup>
import { ref, watch, onMounted } from "vue";
import { twMerge } from "tailwind-merge";
import { type Paths, setupSvgRenderer } from "@/utils/frame";

const {
  class: className,
  paths,
  enableBackdropBlur,
  enableViewBox,
  ...props
} = defineProps<{
  class?: string;
  paths: Paths;
  enableBackdropBlur?: boolean;
  enableViewBox?: boolean;
}>();

const svgRef = ref<SVGSVGElement | null>(null);

const init = () => {
  if (svgRef.value && svgRef.value?.parentElement) {
    const instance = setupSvgRenderer({
      el: svgRef.value,
      paths,
      enableBackdropBlur,
      enableViewBox,
    });

    return () => instance.destroy();
  }
};

onMounted(() => {
  init();
});

watch(
  () => paths,
  () => {
    init();
  }
);
</script>

<template>
  <svg
    v-bind="{ ...props }"
    :class="twMerge(['absolute inset-0 size-full', className])"
    xmlns="http://www.w3.org/2000/svg"
    ref="svgRef"
  />
</template>
