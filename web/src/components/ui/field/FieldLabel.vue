<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { Label } from "@/components/ui/label";
import { fieldLabel } from "@midoneui/core/styles/field.styles";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();
</script>

<template>
  <Label
    data-part="field-label"
    :class="cn(fieldLabel, className)"
    v-bind="{ ...props }"
  >
    <slot />
  </Label>
</template>
