<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { comboboxItemGroupLabel } from "@midoneui/core/styles/combobox.styles";
import type { Api, ItemGroupProps } from "@zag-js/combobox";
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
const itemGroupId = inject<ItemGroupProps>("comboboxItemGroupId");
</script>

<template>
  <Slot
    :class="cn(comboboxItemGroupLabel, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getItemGroupLabelProps({
        htmlFor: itemGroupId?.id!,
      }) }"
  >
    <slot v-if="asChild" />
    <label v-else>
      <slot />
    </label>
  </Slot>
</template>
