<script lang="ts">
export default {
  inheritAttrs: false,
};
</script>

<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs, provide } from "vue";

export type ProvideFormInline = boolean;

const attrs = useAttrs();
const computedClass = computed(() =>
  twMerge([
    "block sm:flex items-center",
    typeof attrs.class === "string" && attrs.class,
  ])
);

provide<ProvideFormInline>("formInline", true);
</script>

<template>
  <div :class="computedClass" v-bind="_.omit(attrs, 'class')">
    <slot></slot>
  </div>
</template>
