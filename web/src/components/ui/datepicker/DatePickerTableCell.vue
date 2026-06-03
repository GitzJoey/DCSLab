<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerTableCell } from "@midoneui/core/styles/datepicker.styles";
import type {
  Api,
  ViewProps,
  DayTableCellProps,
  TableCellProps,
} from "@zag-js/date-picker";
import { provide, inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  (DayTableCellProps | TableCellProps) & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("datepickerApi");
const viewContext = inject<ViewProps>("datepickerView");

provide("datepickerCell", props);
</script>

<template>
  <Slot
    :class="cn(datePickerTableCell, className)"
    v-bind="{ ...props, ...$attrs, ...(viewContext?.view === 'day'
          ? api?.getDayTableCellProps(props as DayTableCellProps)
          : viewContext?.view === 'month'
          ? api?.getMonthTableCellProps(props as TableCellProps)
          : viewContext?.view === 'year'
          ? api?.getYearTableCellProps(props as TableCellProps)
          : undefined) }"
  >
    <slot v-if="asChild" />
    <td v-else>
      <slot />
    </td>
  </Slot>
</template>
