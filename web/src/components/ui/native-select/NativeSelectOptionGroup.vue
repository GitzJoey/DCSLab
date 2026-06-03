<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { NativeSelectOptGroup } from "@midoneui/core/styles/native-select.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <optgroup
    :class="cn(NativeSelectOptGroup, className)"
    v-bind="{ ...props, ...$attrs }"
  >
    <slot />
  </optgroup>
</template>
