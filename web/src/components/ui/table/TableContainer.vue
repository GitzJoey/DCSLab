<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableContainer } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <div :class="cn(tableContainer, className)" v-bind="{ ...props }">
    <slot />
  </div>
</template>
