<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerNextTrigger } from "@midoneui/core/styles/datepicker.styles";
import { MoveRight } from "lucide-vue-next";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getNextTriggerProps() }">
    <button v-if="!asChild" :class="cn(datePickerNextTrigger, className)">
      <MoveRight v-if="!$slots.default" />
      <slot v-else />
    </button>
    <slot v-else />
  </Slot>
</template>
