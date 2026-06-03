<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableFooter } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <tfoot :class="cn(tableFooter, className)" v-bind="{ ...props }">
    <slot />
  </tfoot>
</template>
