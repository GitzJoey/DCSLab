<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs, inject } from "vue";
import { ProvideTable } from "./Table.vue";
import { ProvideThead } from "./Thead.vue";

const table = inject<ProvideTable>("table", {
  dark: false,
  bordered: false,
  hover: false,
  striped: false,
  sm: false,
});
const thead = inject<ProvideThead>("thead", {
  variant: "default",
});

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "font-medium px-5 py-3 border-b-2 dark:border-darkmode-300",
    thead?.variant === "light" && "border-b-0 text-slate-700",
    thead?.variant === "dark" && "border-b-0",
    table?.dark && "border-slate-600 dark:border-darkmode-300",
    table?.bordered && "border-l border-r border-t",
    table?.sm && "px-4 py-2",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <th :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </th>
</template>
