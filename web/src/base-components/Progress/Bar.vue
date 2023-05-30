<script setup lang="ts">
import _ from "lodash";
import { twMerge } from "tailwind-merge";
import { computed, useAttrs } from "vue";

const attrs = useAttrs();

const computedClass = computed(() =>
  twMerge([
    "bg-primary h-full rounded text-xs text-white flex justify-center items-center",
    typeof attrs.class === "string" && attrs.class,
  ])
);
</script>

<template>
  <div :class="computedClass">
    <slot></slot>
  </div>
</template>
