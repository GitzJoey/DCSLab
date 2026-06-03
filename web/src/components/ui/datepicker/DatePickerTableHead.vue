<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerTableHead } from "@midoneui/core/styles/datepicker.styles";
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
  <Slot
    :class="cn(datePickerTableHead, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getTableHeadProps() }"
  >
    <slot v-if="asChild" />
    <thead v-else>
      <slot />
    </thead>
  </Slot>
</template>
