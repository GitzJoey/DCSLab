<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerMonthSelect } from "@midoneui/core/styles/datepicker.styles";
import {
  NativeSelect,
  NativeSelectOption,
} from "@/components/ui/native-select";
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
  <NativeSelect
    :class="cn(datePickerMonthSelect, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getMonthSelectProps() }"
  >
    <NativeSelectOption
      v-for="(month, i) in api?.getMonths()"
      :key="i"
      :value="month.value"
    >
      {{ month.label }}
    </NativeSelectOption>
  </NativeSelect>
</template>
