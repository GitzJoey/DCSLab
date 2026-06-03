<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { separator } from "@midoneui/core/styles/separator.styles";

const {
  class: className,
  orientation = "horizontal",
  decorative = true,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
  orientation?: "vertical" | "horizontal" | "responsive";
  decorative?: boolean;
}>();
</script>

<template>
  <div
    data-part="separator"
    :data-decorative="decorative"
    :data-orientation="orientation"
    :class="cn(separator, className)"
    v-bind="{ ...props }"
  />
</template>
