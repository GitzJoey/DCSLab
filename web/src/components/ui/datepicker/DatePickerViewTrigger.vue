<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerViewTrigger } from "@midoneui/core/styles/datepicker.styles";
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
    :class="cn(datePickerViewTrigger, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getViewTriggerProps(props) }"
  >
    <button v-if="!asChild" :class="cn(datePickerViewTrigger, className)">
      <slot />
    </button>
    <slot v-else />
  </Slot>
</template>
