<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldTitle } from "@midoneui/core/styles/field.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    data-part="field-label"
    :class="cn(fieldTitle, className)"
    v-bind="{ ...props }"
  >
    <slot />
  </div>
</template>
