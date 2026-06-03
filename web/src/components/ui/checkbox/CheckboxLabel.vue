<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import type { Api } from "@zag-js/checkbox";
import { checkboxLabel } from "@midoneui/core/styles/checkbox.styles";
import { label } from "@midoneui/core/styles/label.styles";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  asChild?: boolean;
  class?: string;
}>();

const api = inject<Api>("checkboxApi");
</script>

<template>
  <Slot
    :class="cn([label, checkboxLabel, className])"
    v-bind="{ ...props, ...$attrs, ...api?.getLabelProps() }"
  >
    <slot v-if="asChild" />
    <span v-else>
      <slot />
    </span>
  </Slot>
</template>
