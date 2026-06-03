<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { nativeSelectOption } from "@midoneui/core/styles/native-select.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <option
    :class="cn(nativeSelectOption, className)"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </option>
</template>
