<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderLabel } from "@midoneui/core/styles/slider.styles";
import { Slot } from "@/components/ui/slot";
import { Label } from "@/components/ui/label";
import type { Api } from "@zag-js/slider";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("sliderApi");
</script>

<template>
  <Slot v-bind="{ ...api?.getLabelProps(), ...props, ...$attrs }">
    <slot v-if="asChild" />
    <Label v-else :class="cn(sliderLabel, className)">
      <slot />
    </Label>
  </Slot>
</template>
