<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs } from "vue";

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    "cursor-pointer ml-2",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <label :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </label>
</template>
