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

const table = inject<ProvideTable>("table", {
  dark: false,
  bordered: false,
  hover: false,
  striped: false,
  sm: false,
});

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    table?.hover &&
      "[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50",
    table?.striped &&
      "[&:nth-of-type(odd)_td]:bg-slate-100 [&:nth-of-type(odd)_td]:dark:bg-darkmode-300 [&:nth-of-type(odd)_td]:dark:bg-opacity-50",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <tr :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </tr>
</template>
