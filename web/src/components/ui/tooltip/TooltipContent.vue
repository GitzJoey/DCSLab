<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tooltipContent } from "@midoneui/core/styles/tooltip.styles";
import { Slot } from "@/components/ui/slot";
import { TooltipArrow, TooltipArrowTip } from ".";
import type { Api } from "@zag-js/tooltip";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("tooltipApi");
</script>

<template>
  <Slot
    :class="cn(tooltipContent, className)"
    v-bind="{ ...api?.getContentProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
      <TooltipArrow>
        <TooltipArrowTip />
      </TooltipArrow>
    </div>
  </Slot>
</template>
