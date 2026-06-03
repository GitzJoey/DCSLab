<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldLegend } from "@midoneui/core/styles/field.styles";

const {
  class: className,
  variant = "legend",
  ...props
} = defineProps<{
  class?: string;
  variant?: "legend" | "label";
}>();
</script>

<template>
  <legend
    data-part="field-legend"
    :data-variant="variant"
    :class="cn(fieldLegend, className)"
    v-bind="{ ...props }"
  >
    <slot />
  </legend>
</template>
