<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { fieldSet } from "@midoneui/core/styles/field.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <fieldset
    data-part="field-set"
    :class="cn(fieldSet, className)"
    v-bind="{ ...props }"
  >
    <slot />
  </fieldset>
</template>
