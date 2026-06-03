<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerTrigger } from "@midoneui/core/styles/datepicker.styles";
import { Button } from "@/components/ui/button";
import { Calendar } from "lucide-vue-next";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getTriggerProps() }">
    <Button
      variant="ghost"
      v-if="!asChild"
      :class="cn(datePickerTrigger, className)"
    >
      <Calendar v-if="!$slots.default" />
      <slot v-else />
    </Button>
    <slot v-else />
  </Slot>
</template>
