<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { radioGroupIndicator } from "@midoneui/core/styles/radio-group.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/radio-group";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("radioGroupApi");
</script>

<template>
  <Slot
    :class="cn(radioGroupIndicator, className)"
    v-bind="{ ...api?.getIndicatorProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else><slot /></div>
  </Slot>
</template>
