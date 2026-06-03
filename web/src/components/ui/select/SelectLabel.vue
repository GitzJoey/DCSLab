<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectLabel } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import { Label } from "@/components/ui/label";
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
  <Slot v-bind="{ ...api?.getLabelProps(), ...props, ...$attrs }">
    <slot v-if="asChild" />
    <Label v-else :class="cn(selectLabel, className)"><slot /></Label>
  </Slot>
</template>
