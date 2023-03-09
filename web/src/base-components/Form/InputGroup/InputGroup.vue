<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs, provide } from "vue";

export type ProvideInputGroup = boolean;

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge(["flex", typeof attrs.class === "string" && attrs.class])
);

provide<ProvideInputGroup>("inputGroup", true);
</script>

<template>
  <div :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </div>
</template>
