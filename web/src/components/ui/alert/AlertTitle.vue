<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { Slot } from "@/components/ui/slot";
import { alertTitle } from "@midoneui/core/styles/alert.styles";

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
  <Slot :class="cn([className, alertTitle])" v-bind="{ ...props, ...$attrs }">
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
