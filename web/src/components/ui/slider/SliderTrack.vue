<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { sliderTrack } from "@midoneui/core/styles/slider.styles";
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
    :class="cn(sliderTrack, className)"
    v-bind="{ ...api?.getTrackProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
