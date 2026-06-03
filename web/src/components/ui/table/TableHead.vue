<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { tableHead } from "@midoneui/core/styles/table.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <th :class="cn(tableHead, className)" v-bind="{ ...props }">
    <slot />
  </th>
</template>
