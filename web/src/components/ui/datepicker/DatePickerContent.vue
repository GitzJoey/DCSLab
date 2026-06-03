<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerContent } from "@midoneui/core/styles/datepicker.styles";
import type { Api } from "@zag-js/date-picker";
import { Box } from "@/components/ui/box";
import { Slot } from "@/components/ui/slot";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<{
  class?: string;
  asChild?: boolean;
}>();

const api = inject<Api>("datepickerApi");
</script>

<template>
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getContentProps() }">
    <slot v-if="asChild" />
    <Box v-else raised="single" :class="cn(datePickerContent, className)">
      <div><slot /></div>
    </Box>
  </Slot>
</template>
