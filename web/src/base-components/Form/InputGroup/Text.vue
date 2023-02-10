<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs, inject } from "vue";
import { ProvideInputGroup } from "../InputGroup/InputGroup.vue";

const attrs = useAttrs();

const inputGroup = inject<ProvideInputGroup>("inputGroup");

const computedClass = computed(() =>
  twMerge([
    "py-2 px-3 bg-slate-100 border shadow-sm border-slate-200 text-slate-600 dark:bg-darkmode-900/20 dark:border-darkmode-900/20 dark:text-slate-400",
    inputGroup &&
      "rounded-none [&:not(:first-child)]:border-l-transparent first:rounded-l last:rounded-r",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <div :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </div>
</template>
