<script lang="ts" setup>
import { cn } from "@midoneui/core/utils/cn";
import { datePickerRangeText } from "@midoneui/core/styles/datepicker.styles";
import type { Api } from "@zag-js/date-picker";
import { inject } from "vue";

const { class: className, ...props } = defineProps<{
  class?: string;
}>();

const api = inject<Api>("datepickerApi");
</script>

<template>
  <div
    :class="cn(datePickerRangeText, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getRangeTextProps() }"
  >
    {{ api?.visibleRangeText.start }}
  </div>
</template>
