<script lang="ts" setup>
import { Slot } from "@/components/ui/slot";
import { cn } from "@midoneui/core/utils/cn";
import { datePickerPrevTrigger } from "@midoneui/core/styles/datepicker.styles";
import { MoveLeft } from "lucide-vue-next";
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
  <Slot v-bind="{ ...props, ...$attrs, ...api?.getPrevTriggerProps() }">
    <button v-if="!asChild" :class="cn(datePickerPrevTrigger, className)">
      <MoveLeft v-if="!$slots.default" />
      <slot v-else />
    </button>
    <slot v-else />
  </Slot>
</template>
