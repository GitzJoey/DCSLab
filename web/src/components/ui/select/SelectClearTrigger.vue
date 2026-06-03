<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectClearTrigger } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/select";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("selectApi");
</script>

<template>
  <Slot v-bind="{ ...api?.getClearTriggerProps(), ...props, ...$attrs }">
    <slot v-if="asChild" />
    <span v-else :class="cn(selectClearTrigger, className)"><slot /></span>
  </Slot>
</template>
