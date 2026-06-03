<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerLabel } from "@midoneui/core/styles/datepicker.styles";
import { Label } from "@/components/ui/label";
import { Slot } from "@/components/ui/slot";
import type { Api } from "@zag-js/date-picker";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getLabelProps() }">
    <slot v-if="asChild" />
    <Label v-else :class="cn(datePickerLabel, className)">
      <slot />
    </Label>
  </Slot>
</template>
