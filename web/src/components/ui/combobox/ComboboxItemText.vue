<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { comboboxItemText } from "@midoneui/core/styles/combobox.styles";
import type { Api, ItemProps } from "@zag-js/combobox";
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
const item = inject<ItemProps>("comboboxItem");
</script>

<template>
  <Slot
    :class="cn(comboboxItemText, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getItemTextProps(item!) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
