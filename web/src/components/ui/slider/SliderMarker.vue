<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderMarker } from "@midoneui/core/styles/slider.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, MarkerProps } from "@zag-js/slider";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  MarkerProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("sliderApi");
</script>

<template>
  <Slot
    :class="cn(sliderMarker, className)"
    v-bind="{ ...api?.getMarkerProps(props), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
