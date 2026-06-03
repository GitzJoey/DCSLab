<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { carouselIndicator } from "@midoneui/core/styles/carousel.styles";
import type { Api, IndicatorProps } from "@zag-js/carousel";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  index,
  ...props
} = defineProps<
  {
    class?: string;
    asChild?: boolean;
  } & IndicatorProps
>();

const api = inject<Api>("carouselApi");
</script>

<template>
  <button
    :class="cn(carouselIndicator, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getIndicatorProps({ index }) }"
  />
</template>
