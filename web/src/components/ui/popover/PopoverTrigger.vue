<script lang="ts" setup>
import { type Api } from "@zag-js/popover";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Button } from "@/components/ui/button";
import { Slot } from "@/components/ui/slot";
import { popoverTrigger } from "@midoneui/core/styles/popover.styles";
import { PopoverIndicator } from ".";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("popoverApi");
</script>

<template>
  <Slot v-bind="{ ...api?.getTriggerProps(), ...props, ...$attrs }">
    <Button
      variant="ghost"
      v-if="!asChild"
      :class="cn(popoverTrigger, className)"
    >
      <slot />
      <PopoverIndicator />
    </Button>
    <slot v-else />
  </Slot>
</template>
