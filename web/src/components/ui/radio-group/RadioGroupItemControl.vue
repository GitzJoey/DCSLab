<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { radioGroupItemControl } from "@midoneui/core/styles/radio-group.styles";
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
    :class="cn(radioGroupItemControl, className)"
    v-bind="{ ...api?.getItemControlProps(itemProps!), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
