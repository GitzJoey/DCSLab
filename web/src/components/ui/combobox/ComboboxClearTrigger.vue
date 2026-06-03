<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { comboboxClearTrigger } from "@midoneui/core/styles/combobox.styles";
import type { Api } from "@zag-js/combobox";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("comboboxApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getClearTriggerProps() }">
    <slot v-if="asChild" />
    <span :class="cn(comboboxClearTrigger, className)" v-else>
      <slot />
    </span>
  </Slot>
</template>
