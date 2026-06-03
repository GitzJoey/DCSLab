<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableBody } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <tbody :class="cn(tableBody, className)" v-bind="{ ...props }">
    <slot />
  </tbody>
</template>
