<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { radioGroupLabel } from "@midoneui/core/styles/radio-group.styles";
import { label } from "@midoneui/core/styles/label.styles";
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
    :class="cn([label, radioGroupLabel, className])"
    v-bind="{ ...api?.getLabelProps(), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <span v-else><slot /></span>
  </Slot>
</template>
