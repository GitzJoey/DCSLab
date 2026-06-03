<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { menuRadioItemGroup } from "@midoneui/core/styles/menu.styles";
import { Slot } from "@/components/ui/slot";

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
    :class="cn(menuRadioItemGroup, className)"
    v-bind="{
      ...props,
      ...$attrs,
    }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
