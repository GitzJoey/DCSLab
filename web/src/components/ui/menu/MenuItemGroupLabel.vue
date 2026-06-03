<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { menuItemGroupLabel } from "@midoneui/core/styles/menu.styles";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();
</script>

<template>
  <Slot
    :class="cn(menuItemGroupLabel, className)"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <label v-else>
      <slot />
    </label>
  </Slot>
</template>
