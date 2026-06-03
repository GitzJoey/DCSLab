<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { label } from "@midoneui/core/styles/label.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <label :class="cn(label, className)" v-bind="{ ...props }">
    <slot />
  </label>
</template>
