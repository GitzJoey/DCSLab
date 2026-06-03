<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderThumb } from "@midoneui/core/styles/slider.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, ThumbProps } from "@zag-js/slider";
import { provide, inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  ThumbProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("sliderApi");

provide("sliderThumb", props);
</script>

<template>
  <Slot
    :class="cn(sliderThumb, className)"
    v-bind="{ ...api?.getThumbProps(props), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
