<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { comboboxItemGroup } from "@midoneui/core/styles/combobox.styles";
import type { Api } from "@zag-js/combobox";
import { provide, inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("comboboxApi");
const itemGroupId = { id: crypto.randomUUID() };

provide("comboboxItemGroupId", itemGroupId);
</script>

<template>
  <Slot
    :class="cn(comboboxItemGroup, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getItemGroupProps(itemGroupId) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
