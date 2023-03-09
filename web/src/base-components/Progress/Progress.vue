<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs } from "vue";

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "w-full h-2 bg-slate-200 rounded dark:bg-black/20",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <div :class="computedClass">
    <slot></slot>
  </div>
</template>
