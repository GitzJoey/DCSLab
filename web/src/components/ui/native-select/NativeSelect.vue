<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { input } from "@midoneui/core/styles/input.styles";
import { nativeSelect } from "@midoneui/core/styles/native-select.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <select
    :class="cn(input, nativeSelect, className)"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </select>
</template>
