<script lang="ts" setup>
import { type Api } from "@zag-js/popover";
import { cn } from "@midoneui/core/utils/cn";
import { inject } from "vue";
import { Slot } from "@/components/ui/slot";
import { popoverIndicator } from "@midoneui/core/styles/popover.styles";
import { ChevronDown } from "lucide-vue-next";

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
  <Slot
    :class="cn(popoverIndicator, className)"
    v-bind="{ ...api?.getIndicatorProps(), ...props, ...$attrs }"
  >
    <slot v-if="$slots.default" />
    <ChevronDown v-else />
  </Slot>
</template>
