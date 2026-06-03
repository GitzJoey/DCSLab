<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableHeader } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <thead :class="cn(tableHeader, className)" v-bind="{ ...props }">
    <slot />
  </thead>
</template>
