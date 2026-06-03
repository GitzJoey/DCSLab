<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectItem } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import { SelectItemIndicator } from ".";
import type { Api, ItemProps } from "@zag-js/select";
import { provide, inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  ItemProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("selectApi");

provide("selectItem", props);
</script>

<template>
  <Slot
    :class="cn(selectItem, className)"
    v-bind="{ ...api?.getItemProps(props), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
      <SelectItemIndicator />
    </div>
  </Slot>
</template>
