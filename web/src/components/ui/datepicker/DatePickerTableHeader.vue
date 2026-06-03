<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerTableHeader } from "@midoneui/core/styles/datepicker.styles";
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
    :class="cn(datePickerTableHeader, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getTableHeaderProps() }"
  >
    <slot v-if="asChild" />
    <th v-else>
      <slot />
    </th>
  </Slot>
</template>
