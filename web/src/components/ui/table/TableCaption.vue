<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableCaption } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <caption :class="cn(tableCaption, className)" v-bind="{ ...props }">
    <slot />
  </caption>
</template>
