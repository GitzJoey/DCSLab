<script lang="ts" setup>
import type { Api, OptionItemProps } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { Dot } from "lucide-vue-next";
import { menuItem } from "@midoneui/core/styles/menu.styles";
import { inject } from "vue";

const {
  shortcut,
  class: className,
  asChild = false,
  type = "radio",
  ...props
} = defineProps<
  Omit<OptionItemProps, "type"> & {
    class?: string;
    asChild?: boolean;
    shortcut?: string;
    type?: OptionItemProps["type"];
  }
>();

const api = inject<Api>("menuApi");
</script>

<template>
  <div
    :class="cn(menuItem, className)"
    v-bind="{
      ...props,
      ...$attrs,
      ...api?.getOptionItemProps({
        ...props,
        type,
      }),
    }"
  >
    <div>
      <span
        data-part="item-indicator"
        v-bind="{ ...api?.getItemIndicatorProps(props) }"
      >
        <Dot />
      </span>
      <slot />
    </div>
    <div>{{ shortcut }}</div>
  </div>
</template>
