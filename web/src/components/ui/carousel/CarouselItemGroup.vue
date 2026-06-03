<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { carouselItemGroup } from "@midoneui/core/styles/carousel.styles";
import type { Api } from "@zag-js/carousel";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("carouselApi");
</script>

<template>
  <Slot
    :class="cn(carouselItemGroup, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getItemGroupProps() }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
