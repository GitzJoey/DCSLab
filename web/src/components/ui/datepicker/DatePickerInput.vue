<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerInput } from "@midoneui/core/styles/datepicker.styles";
import { Input } from "@/components/ui/input";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getInputProps() }">
    <slot v-if="asChild" />
    <Input v-else :class="cn(datePickerInput, className)" />
  </Slot>
</template>
