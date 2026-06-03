<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { radioGroupItemText } from "@midoneui/core/styles/radio-group.styles";
import { Slot } from "@/components/ui/slot";
import type { Api, ItemProps } from "@zag-js/radio-group";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("radioGroupApi");
const itemProps = inject<ItemProps>("radioGroupItem");
</script>

<template>
  <Slot
    :class="cn(radioGroupItemText, className)"
    v-bind="{ ...api?.getItemTextProps(itemProps!), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <span v-else>
      <slot />
    </span>
  </Slot>
</template>
