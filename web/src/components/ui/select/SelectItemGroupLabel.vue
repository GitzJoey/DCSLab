<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectItemGroupLabel } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemGroupProps } from "@zag-js/select";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("selectApi");
const itemGroupId = inject<ItemGroupProps>("selectItemGroup");
</script>

<template>
  <Slot
    :class="cn(selectItemGroupLabel, className)"
    v-bind="{ ...api?.getItemGroupLabelProps({
        htmlFor: itemGroupId?.id!,
      }), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <label v-else><slot /></label>
  </Slot>
</template>
