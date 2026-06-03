<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldGroup } from "@midoneui/core/styles/field.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <div
    data-part="field-group"
    :class="cn(fieldGroup, className)"
    v-bind="{ ...props }"
  >
    <slot />
  </div>
</template>
