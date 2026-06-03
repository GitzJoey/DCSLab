<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderValueText } from "@midoneui/core/styles/slider.styles";
import { Slot } from "@/components/ui/slot";
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
  <Slot
    :class="cn(sliderValueText, className)"
    v-bind="{ ...api?.getValueTextProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <output v-else>{{ api?.value?.[0] }}</output>
  </Slot>
</template>
