<script lang="ts" setup>
import type { Api } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { menuSeparator } from "@midoneui/core/styles/menu.styles";
import { Slot } from "@/components/ui/slot";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Slot
    :class="cn(menuSeparator, className)"
    v-bind="{
      ...props,
      ...$attrs,
      ...api?.getSeparatorProps(),
    }"
  >
    <slot v-if="asChild" />
    <hr v-else>
      <slot />
    </hr>
  </Slot>
</template>
