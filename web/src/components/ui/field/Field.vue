<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import {
  fieldVariants,
  type FieldVariants,
} from "@midoneui/core/styles/field.styles";

const {
  class: className,
  orientation = "vertical",
  ...props
} = defineProps<
  FieldVariants & {
    class?: string;
  }
>();
</script>

<template>
  <div
    role="group"
    data-part="field"
    :data-orientation="orientation"
    :class="cn(fieldVariants({ orientation }), className)"
    v-bind="{ ...props }"
  >
    <slot />
  </div>
</template>
