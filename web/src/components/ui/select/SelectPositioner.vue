<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectPositioner } from "@midoneui/core/styles/select.styles";
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
  <Slot
    :class="cn(selectPositioner, className)"
    v-bind="{ ...api?.getPositionerProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else><slot /></div>
  </Slot>
</template>
