<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerTableCellTrigger } from "@midoneui/core/styles/datepicker.styles";
import type {
  Api,
  ViewProps,
  DayTableCellProps,
  TableCellProps,
} from "@zag-js/date-picker";
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
const viewContext = inject<ViewProps>("datepickerView");
const cellContext = inject<DayTableCellProps | TableCellProps>(
  "datepickerCell"
);
</script>

<template>
  <Slot
    :class="cn(datePickerTableCellTrigger, className)"
    v-bind="{ ...props, ...$attrs, ...(viewContext?.view === 'day'
        ? api?.getDayTableCellTriggerProps(cellContext as DayTableCellProps)
        : viewContext?.view === 'month'
        ? api?.getMonthTableCellTriggerProps(cellContext as TableCellProps)
        : viewContext?.view === 'year'
        ? api?.getYearTableCellTriggerProps(cellContext as TableCellProps)
        : undefined) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
