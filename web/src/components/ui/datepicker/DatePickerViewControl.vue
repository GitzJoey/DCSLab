<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerViewControl } from "@midoneui/core/styles/datepicker.styles";
import type { Api, ViewProps } from "@zag-js/date-picker";
import { inject } from "vue";

const {
  class: className,
  asChild = false,
  ...props
} = defineProps<
  ViewProps & {
    class?: string;
    asChild?: boolean;
  }
>();

const api = inject<Api>("datepickerApi");
</script>

<template>
  <Slot
    :class="cn(datePickerViewControl, className)"
    v-bind="{ ...props, ...$attrs, ...api?.getViewControlProps(props) }"
  >
    <slot v-if="asChild" />
    <div v-else>
      <slot />
    </div>
  </Slot>
</template>
