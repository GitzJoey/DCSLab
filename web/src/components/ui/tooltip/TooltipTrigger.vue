<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tooltipTrigger } from "@midoneui/core/styles/tooltip.styles";
import { Slot } from "@/components/ui/slot";
import { Button } from "@/components/ui/button";
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
  <Slot v-bind="{ ...api?.getTriggerProps(), ...props, ...$attrs }">
    <Button
      variant="secondary"
      look="outline"
      v-if="!asChild"
      :class="cn(tooltipTrigger, className)"
    >
      <slot />
    </Button>
    <slot v-else />
  </Slot>
</template>
