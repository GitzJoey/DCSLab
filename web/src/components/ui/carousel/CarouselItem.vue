<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { Box } from "@/components/ui/box";
import { carouselItem } from "@midoneui/core/styles/carousel.styles";
import type { Api, ItemProps } from "@zag-js/carousel";
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
  } & ItemProps
>();

const api = inject<Api>("carouselApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getItemProps({ index }) }">
    <slot v-if="asChild" />
    <Box v-else :class="cn(carouselItem, className)"><slot /></Box>
  </Slot>
</template>
