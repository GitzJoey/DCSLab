<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { selectItemText } from "@midoneui/core/styles/select.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemProps } from "@zag-js/select";
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
const item = inject<ItemProps>("selectItem");
</script>

<template>
  <Slot
    :class="cn(selectItemText, className)"
    v-bind="{ ...api?.getItemTextProps(item!), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
