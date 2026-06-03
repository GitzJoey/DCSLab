<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { type Api, type ItemProps } from "@zag-js/menu";
import { inject } from "vue";
import { cn } from "@midoneui/core/utils/cn";
import { menuItem } from "@midoneui/core/styles/menu.styles";

const {
  class: className,
  shortcut,
  asChild = false,
  ...props
} = defineProps<
  ItemProps & {
    class?: string;
    shortcut?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("menuApi");
</script>

<template>
  <Slot
    :class="cn(menuItem, className)"
    v-bind="{ ...api?.getItemProps(props), ...props, ...$attrs }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <div><slot /></div>
      <div>{{ shortcut }}</div>
    </div>
  </Slot>
</template>
