<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldSeparator } from "@midoneui/core/styles/field.styles";
import { Separator } from "@/components/ui/separator";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    data-part="field-separator"
    :data-content="!!$slots.default"
    :class="cn(fieldSeparator, className)"
    v-bind="{ ...props }"
  >
    <Separator class="absolute inset-0 top-1/2" />
    <span v-if="$slots.default" data-part="field-separator-content">
      <slot />
    </span>
  </div>
</template>
