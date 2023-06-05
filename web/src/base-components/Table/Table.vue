<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, TableHTMLAttributes, useAttrs, provide } from "vue";

export type ProvideTable = {
  dark: boolean;
  bordered: boolean;
  hover: boolean;
  striped: boolean;
  sm: boolean;
};

interface TableProps extends /* @vue-ignore */ TableHTMLAttributes {
  dark?: boolean;
  bordered?: boolean;
  hover?: boolean;
  striped?: boolean;
  sm?: boolean;
}

const { dark, bordered, hover, striped, sm } = withDefaults(
  defineProps<TableProps>(),
  {
    dark: false,
    bordered: false,
    hover: false,
    striped: false,
    sm: false,
  }
);

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "w-full text-left",
    dark && "bg-dark text-white dark:bg-black/30",
    typeof attrs.class === "string" && attrs.class,
  ])
);

provide<ProvideTable>("table", {
  dark: dark,
  bordered: bordered,
  hover: hover,
  striped: striped,
  sm: sm,
});
</script>

<template>
  <table :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </table>
</template>
