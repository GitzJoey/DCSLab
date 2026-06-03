<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { comboboxItemIndicator } from "@midoneui/core/styles/combobox.styles";
import { Check } from "lucide-vue-next";
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
    :class="cn(comboboxItemIndicator, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getItemIndicatorProps(item!) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot v-if="$slots.default" />
      <Check v-else class="size-3.5" />
    </div>
  </Slot>
</template>
