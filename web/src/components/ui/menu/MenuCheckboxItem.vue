<script lang="ts" setup>
import type { Api, OptionItemProps } from "@zag-js/menu";
import { cn } from "@midoneui/core/utils/cn";
import { Check } from "lucide-vue-next";
import { menuItem } from "@midoneui/core/styles/menu.styles";
import { inject } from "vue";

const {
  shortcut,
  class: className,
  type = "checkbox",
  ...props
} = defineProps<
  Omit<OptionItemProps, "type"> & {
    class?: string;
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
        <Check />
      </span>
      <slot />
    </div>
    <div>{{ shortcut }}</div>
  </div>
</template>
